<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TrafficViolation;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\ViolationMatcher;
use App\Support\ExcelValue;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Traffic violations (BAN-260): record a notice and identify the renter who
 * held the vehicle when it was issued.
 *
 * Matching is delegated to App\Services\ViolationMatcher, which returns a
 * proposal plus a confidence label — this controller only persists it. The
 * owner confirms or reassigns; nothing here decides liability on its own.
 */
class TrafficViolationController extends Controller
{
    /** Most bookings offered in the manual-assignment picker. */
    private const ASSIGNABLE_LIMIT = 100;

    /**
     * Most data rows accepted from one uploaded file. Each row runs the matcher,
     * and nothing here is queued (QUEUE_CONNECTION=sync on the shared host), so
     * an unbounded file would simply time out. Rows beyond the cap are reported,
     * never silently dropped.
     */
    private const MAX_IMPORT_ROWS = 2000;

    /** Import/template column layout, in order. */
    private const TEMPLATE_COLUMNS = [
        'reference'   => 'REFERENCE',
        'plate'       => 'IMMATRICULATION',
        'date'        => 'DATE',
        'time'        => 'HEURE',
        'location'    => 'LIEU',
        'description' => 'INFRACTION',
        'amount'      => 'MONTANT',
    ];

    public function __construct(private ViolationMatcher $matcher)
    {
    }

    public function index(Request $request)
    {
        if (! \Auth::user()->can('manage traffic violation')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $search     = trim((string) $request->get('search', ''));
        $status     = trim((string) $request->get('status', ''));
        $confidence = trim((string) $request->get('confidence', ''));

        $violations = TrafficViolation::with(['vehicle', 'booking', 'driver'])
            ->where('parent_id', '=', parentId())
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('reference', 'like', "%{$search}%")
                        ->orWhere('license_plate', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('driver', fn ($d) => $d->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($confidence === 'unmatched', fn ($q) => $q->whereNull('booking_id'))
            ->when(
                $confidence !== '' && $confidence !== 'unmatched',
                fn ($q) => $q->where('match_confidence', $confidence)
            )
            ->orderByDesc('occurred_at')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('TrafficViolation/Index', [
            'violations' => $violations,
            'filters'    => [
                'search'     => $search,
                'status'     => $status,
                'confidence' => $confidence,
            ],
            'statuses'      => TrafficViolation::$statuses,
            'unmatchedCount' => TrafficViolation::where('parent_id', parentId())
                ->whereNull('booking_id')
                ->count(),
        ]);
    }

    public function create()
    {
        if (! \Auth::user()->can('create traffic violation')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        return Inertia::render('TrafficViolation/Create');
    }

    public function store(Request $request)
    {
        if (! \Auth::user()->can('create traffic violation')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $validator = \Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $violation = new TrafficViolation();
        $this->fill($violation, $request);
        $violation->parent_id  = parentId();
        $violation->created_by = \Auth::id();
        $violation->status     = 'new';

        $this->applyMatch($violation);

        try {
            $violation->save();
        } catch (QueryException $e) {
            // The (parent_id, reference) unique index is what keeps repeated
            // imports idempotent; surface it as a message, not a 500.
            return redirect()->back()->with('error', __('A violation with this reference already exists.'));
        }

        return redirect()->route('traffic-violation.show', $violation->id)
            ->with('success', __('Traffic violation successfully created.'));
    }

    public function show(TrafficViolation $trafficViolation)
    {
        if (! \Auth::user()->can('manage traffic violation')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if ((int) $trafficViolation->parent_id !== (int) parentId()) {
            return redirect()->route('traffic-violation.index')->with('error', __('Permission Denied.'));
        }

        $trafficViolation->load(['vehicle', 'booking', 'driver']);

        $at       = Carbon::parse($trafficViolation->occurred_at);
        $parentId = (int) $trafficViolation->parent_id;

        // Computed fresh, while the badge on the page is what was stored at
        // match time. Bookings get edited afterwards, so the two can legitimately
        // disagree — surface that instead of quietly showing a stale verdict.
        $result = $this->matcher->withPeople(
            $this->matcher->match($trafficViolation->license_plate, $at, $parentId),
            $at,
            $parentId
        );

        return Inertia::render('TrafficViolation/Show', [
            'violation'  => $trafficViolation,
            'candidates' => $this->candidatesFor($trafficViolation, $result),
            'statuses'      => TrafficViolation::$statuses,
            'liableParties' => TrafficViolation::$liableParties,
            'assignableBookings' => $this->assignableBookings($trafficViolation),
            'matchIsStale'       => $this->matchIsStale($trafficViolation, $result),
        ]);
    }

    /**
     * Does the stored match still agree with what the matcher says now?
     *
     * A manual assignment is never stale — a human overrode the matcher on
     * purpose. Otherwise a booking edited after the fact can silently invalidate
     * the stored verdict, and the owner deserves to know before acting on it.
     *
     * @param  array<string,mixed>  $result
     */
    private function matchIsStale(TrafficViolation $violation, array $result): bool
    {
        if ($violation->match_source === TrafficViolation::SOURCE_MANUAL) {
            return false;
        }

        $freshBookingId = $result['best']['booking']->id ?? null;

        return (int) $violation->booking_id !== (int) $freshBookingId
            || $violation->match_confidence !== $result['confidence'];
    }

    /**
     * Re-run the match. Bookings are edited after the fact — a corrected end
     * date can turn an unmatched violation into an obvious one.
     */
    public function rematch(TrafficViolation $trafficViolation)
    {
        if (! \Auth::user()->can('edit traffic violation')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if ((int) $trafficViolation->parent_id !== (int) parentId()) {
            return redirect()->route('traffic-violation.index')->with('error', __('Permission Denied.'));
        }

        $this->applyMatch($trafficViolation);
        $trafficViolation->save();

        return redirect()->back()->with('success', __('Match refreshed.'));
    }

    /**
     * Pin the violation to a booking by hand, or confirm the proposed one.
     *
     * A manual assignment outranks the matcher and survives later edits, so it
     * records who decided and when.
     */
    public function assign(Request $request, TrafficViolation $trafficViolation)
    {
        if (! \Auth::user()->can('edit traffic violation')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if ((int) $trafficViolation->parent_id !== (int) parentId()) {
            return redirect()->route('traffic-violation.index')->with('error', __('Permission Denied.'));
        }

        $validator = \Validator::make($request->all(), ['booking_id' => 'nullable|integer']);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $bookingId = $request->input('booking_id');

        // Detach: hand the violation back to the unmatched queue.
        if (empty($bookingId)) {
            $trafficViolation->booking_id       = null;
            $trafficViolation->driver_user_id   = null;
            $trafficViolation->match_confidence = TrafficViolation::CONFIDENCE_NONE;
            $trafficViolation->match_source     = TrafficViolation::SOURCE_MANUAL;
            $trafficViolation->confirmed_by     = \Auth::id();
            $trafficViolation->confirmed_at     = now();
            $trafficViolation->save();

            return redirect()->back()->with('success', __('Rental unlinked.'));
        }

        $booking = Booking::where('parent_id', parentId())->find($bookingId);

        if ($booking === null) {
            return redirect()->back()->with('error', __('That booking could not be found.'));
        }

        $trafficViolation->booking_id       = $booking->id;
        $trafficViolation->driver_user_id   = (int) ($booking->getAttributes()['driver'] ?? 0) ?: null;
        $trafficViolation->match_confidence = TrafficViolation::CONFIDENCE_EXACT;
        $trafficViolation->match_source     = TrafficViolation::SOURCE_MANUAL;
        $trafficViolation->matched_at       = now();
        $trafficViolation->confirmed_by     = \Auth::id();
        $trafficViolation->confirmed_at     = now();
        $trafficViolation->save();

        return redirect()->back()->with('success', __('Rental linked to this violation.'));
    }

    /** Move the violation along the recovery workflow. Marked by hand. */
    public function status(Request $request, TrafficViolation $trafficViolation)
    {
        if (! \Auth::user()->can('edit traffic violation')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if ((int) $trafficViolation->parent_id !== (int) parentId()) {
            return redirect()->route('traffic-violation.index')->with('error', __('Permission Denied.'));
        }

        $validator = \Validator::make($request->all(), [
            'status'           => 'required|in:'.implode(',', array_keys(TrafficViolation::$statuses)),
            'liable_party'     => 'required|in:'.implode(',', array_keys(TrafficViolation::$liableParties)),
            'amount_recovered' => 'nullable|numeric',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $trafficViolation->status           = $request->input('status');
        $trafficViolation->liable_party     = $request->input('liable_party');
        $trafficViolation->amount_recovered = $request->input('amount_recovered') ?: 0;
        $trafficViolation->save();

        return redirect()->back()->with('success', __('Traffic violation successfully updated.'));
    }

    public function edit(TrafficViolation $trafficViolation)
    {
        if (! \Auth::user()->can('edit traffic violation')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if ((int) $trafficViolation->parent_id !== (int) parentId()) {
            return redirect()->route('traffic-violation.index')->with('error', __('Permission Denied.'));
        }

        return Inertia::render('TrafficViolation/Edit', [
            'violation' => $trafficViolation,
        ]);
    }

    public function update(Request $request, TrafficViolation $trafficViolation)
    {
        if (! \Auth::user()->can('edit traffic violation')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        if ((int) $trafficViolation->parent_id !== (int) parentId()) {
            return redirect()->route('traffic-violation.index')->with('error', __('Permission Denied.'));
        }

        $validator = \Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->getMessageBag()->first());
        }

        $plateOrTimeChanged = $this->plateOrInstantChanged($trafficViolation, $request);
        $wasManual          = $trafficViolation->match_source === TrafficViolation::SOURCE_MANUAL;

        $this->fill($trafficViolation, $request);

        if ($plateOrTimeChanged) {
            if ($wasManual) {
                // A human pinned the rental, so keep it — but the vehicle is a
                // fact about the plate, not a decision. Leaving the old
                // vehicle_id here would make the page contradict itself: the
                // header would show the new plate beside the old car, and the
                // assignment picker would offer the old car's rentals.
                $trafficViolation->vehicle_id = $this->matcher->resolveVehicle(
                    $trafficViolation->license_plate,
                    (int) $trafficViolation->parent_id
                )?->id;
            } else {
                // The plate or the instant moved, so the previous match is about
                // a different question. Re-run it rather than leaving a stale
                // renter attached.
                $this->applyMatch($trafficViolation);
            }
        }

        try {
            $trafficViolation->save();
        } catch (QueryException $e) {
            return redirect()->back()->with('error', __('A violation with this reference already exists.'));
        }

        return redirect()->route('traffic-violation.show', $trafficViolation->id)
            ->with('success', __('Traffic violation successfully updated.'));
    }

    public function destroy(TrafficViolation $trafficViolation)
    {
        if (! \Auth::user()->can('delete traffic violation')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ((int) $trafficViolation->parent_id !== (int) parentId()) {
            return redirect()->route('traffic-violation.index')->with('error', __('Permission Denied.'));
        }

        $trafficViolation->delete();

        return redirect()->route('traffic-violation.index')
            ->with('success', __('Traffic violation successfully deleted.'));
    }

    /** The column layout the importer expects, as a downloadable xlsx. */
    public function downloadTemplate()
    {
        if (! \Auth::user()->can('create traffic violation')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        foreach (array_values(self::TEMPLATE_COLUMNS) as $i => $heading) {
            $sheet->setCellValue(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1).'1',
                $heading
            );
            $sheet->getColumnDimension(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1)
            )->setWidth(20);
        }

        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        // One example row: the date/time format is the whole point of the
        // template, so showing it beats documenting it elsewhere.
        $sheet->fromArray(
            [['PV-000123', '12345 A 6', '03/06/2026', '14:32', 'Avenue Hassan II', 'Excès de vitesse', '400']],
            null,
            'A2'
        );

        $path = storage_path('app/violation_tpl_'.uniqid().'.xlsx');
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, 'traffic_violations_template.xlsx')->deleteFileAfterSend(true);
    }

    /**
     * Bulk-import violation notices from a spreadsheet and auto-match each one.
     *
     * Mirrors BookingController::importExcel: header row skipped, per-row
     * reasons collected in `import_skipped` rather than aborting the batch, and
     * tenant lookups cached so a hundred rows do not mean a hundred queries.
     */
    public function importExcel(Request $request)
    {
        if (! \Auth::user()->can('create traffic violation')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        // Same set BookingController::importExcel accepts — no reason for the
        // two importers to disagree about what a spreadsheet is.
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv']);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Could not read the file: ').$e->getMessage());
        }

        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if (count($rows) < 2) {
            return redirect()->back()->with('error', __('The file has no data rows.'));
        }

        $pid = parentId();

        // Resolve plates against one preloaded fleet rather than per row.
        $vehicles = Vehicle::where('parent_id', $pid)->whereNotNull('license_plate')->get();

        // Existing references, so a re-imported file reports duplicates instead
        // of hitting the unique index row by row.
        $existingReferences = TrafficViolation::where('parent_id', $pid)
            ->whereNotNull('reference')
            ->pluck('reference')
            ->mapWithKeys(fn ($r) => [mb_strtolower(trim($r)) => true])
            ->all();

        $imported = 0;
        $skipped  = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // header
            }

            $lineNumber = $index + 1;

            if ($index > self::MAX_IMPORT_ROWS) {
                $skipped[] = __('Stopped at row :max — split the file and import the rest separately.', [
                    'max' => self::MAX_IMPORT_ROWS,
                ]);
                break;
            }

            [$reference, $plate, $date, $time, $location, $offence, $amount] = array_pad($row, 7, null);

            $reference = trim((string) $reference);
            $plate     = trim((string) $plate);

            if ($plate === '' && $reference === '' && trim((string) $date) === '') {
                continue; // blank filler row
            }

            if ($plate === '') {
                $skipped[] = __('Line :line: missing license plate.', ['line' => $lineNumber]);
                continue;
            }

            $parsedDate = ExcelValue::date($date);
            if ($parsedDate === null) {
                $skipped[] = __('Line :line: unreadable date.', ['line' => $lineNumber]);
                continue;
            }

            $parsedTime = ExcelValue::time($time);
            if ($parsedTime === null) {
                $skipped[] = __('Line :line: unreadable time.', ['line' => $lineNumber]);
                continue;
            }

            // Duplicate check first: on a row that is both a duplicate and
            // malformed, "already imported" is the more useful reason — the row
            // is already in the system, so its formatting is moot.
            if ($reference !== '' && isset($existingReferences[mb_strtolower($reference)])) {
                $skipped[] = __('Line :line: reference :reference already imported.', [
                    'line'      => $lineNumber,
                    'reference' => $reference,
                ]);
                continue;
            }

            // Report a malformed amount instead of silently importing 0 — this
            // is the figure recovery totals are built on, and "400 MAD" or
            // "1.200,50" would otherwise vanish without a trace. Blank is fine.
            $rawAmount = trim((string) $amount);
            if ($rawAmount !== '' && ! is_numeric($rawAmount)) {
                $skipped[] = __('Line :line: unreadable amount.', ['line' => $lineNumber]);
                continue;
            }

            $violation = new TrafficViolation();
            $violation->parent_id     = $pid;
            $violation->created_by    = \Auth::id();
            $violation->reference     = $reference !== '' ? $reference : null;
            $violation->license_plate = $plate;
            $violation->occurred_at   = $parsedDate.' '.$parsedTime;
            $violation->location      = $location ? trim((string) $location) : null;
            $violation->description   = $offence ? trim((string) $offence) : null;
            $violation->amount        = $rawAmount !== '' ? (float) $rawAmount : 0;
            $violation->status        = 'new';

            $result = $this->matcher->match(
                $plate,
                Carbon::parse($violation->occurred_at),
                $pid,
                $vehicles
            );

            // $result['best'] is null when nothing matched — guard the offset,
            // not just the property: `null['driver_id']` warns before ?-> runs.
            $best = $result['best'];

            $violation->vehicle_id       = $result['vehicle']?->id;
            $violation->match_confidence = $result['confidence'];
            $violation->match_source     = TrafficViolation::SOURCE_AUTO;
            $violation->matched_at       = now();
            $violation->booking_id       = $best === null ? null : $best['booking']->id;
            $violation->driver_user_id   = $best === null ? null : $best['driver_id'];

            try {
                $violation->save();
            } catch (QueryException $e) {
                $skipped[] = __('Line :line: reference :reference already imported.', [
                    'line'      => $lineNumber,
                    'reference' => $reference,
                ]);
                continue;
            }

            if ($reference !== '') {
                $existingReferences[mb_strtolower($reference)] = true;
            }

            $imported++;
        }

        return redirect()->route('traffic-violation.index')
            ->with('success', __(':count violation(s) imported.', ['count' => $imported]))
            ->with('import_skipped', $skipped ?: null);
    }

    // ── Internals ────────────────────────────────────────────────────────────

    /** @return array<string,string> */
    private function rules(): array
    {
        return [
            'license_plate' => 'required',
            'occurred_date' => 'required|date',
            // date_format, not just `required`: occurredAt() feeds this to
            // Carbon::parse, which throws on anything unparseable. The browser's
            // <input type="time"> constrains the form, but the endpoint does not
            // get to assume a browser sent the request.
            'occurred_time' => 'required|date_format:H:i,H:i:s',
            'amount'        => 'nullable|numeric',
            'notice_date'   => 'nullable|date',
            // The scan lands on the public disk and is served from our own
            // origin, so an .html/.svg upload would be stored XSS.
            'document'      => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ];
    }

    /**
     * Did the edit change what the match is actually about — the vehicle or the
     * moment? Anything else (amount, notes, the scan) leaves the match valid.
     */
    private function plateOrInstantChanged(TrafficViolation $violation, Request $request): bool
    {
        $plateChanged = Vehicle::plateKey($violation->license_plate)
            !== Vehicle::plateKey((string) $request->input('license_plate'));

        $instantChanged = Carbon::parse($violation->occurred_at)->format('Y-m-d H:i:s')
            !== $this->occurredAt($request);

        return $plateChanged || $instantChanged;
    }

    private function fill(TrafficViolation $violation, Request $request): void
    {
        // A blank reference must be NULL, never '': the (parent_id, reference)
        // unique index treats '' as a real value and would reject the second
        // hand-entered violation.
        $reference = trim((string) $request->input('reference', ''));

        $violation->reference     = $reference !== '' ? $reference : null;
        $violation->authority     = $request->input('authority');
        $violation->license_plate = trim((string) $request->input('license_plate'));
        $violation->occurred_at   = $this->occurredAt($request);
        $violation->notice_date   = $request->input('notice_date') ?: null;
        $violation->location      = $request->input('location');
        $violation->description   = $request->input('description');
        $violation->amount        = $request->input('amount') ?: 0;
        $violation->notes         = $request->input('notes');

        if ($request->hasFile('document')) {
            $file = $request->file('document');

            // The extension comes from the file's *content*, never from the
            // client name. `mimes:` validates content (guessExtension), so
            // trusting getClientOriginalExtension() here would let a PNG
            // carrying <script> in a comment chunk be stored as "x.html" and
            // served as text/html from our own origin — stored XSS that passes
            // validation. Laravel blocks .php itself; .html/.svg it does not.
            $extension = $file->extension() ?: 'bin';

            // Slug the basename too: it lands in a public URL, and a non-latin
            // name can slug to empty.
            $base     = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'notice';
            $fileName = $base.'_'.time().'.'.$extension;

            $dir = storage_path('upload/violation');
            if (! file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $request->file('document')->storeAs('upload/violation/', $fileName, 'public');
            $violation->document = $fileName;
        }
    }

    private function occurredAt(Request $request): string
    {
        $date = (string) $request->input('occurred_date');
        $time = (string) $request->input('occurred_time');

        if (strlen($time) === 5) {
            $time .= ':00';
        }

        return Carbon::parse($date.' '.$time)->format('Y-m-d H:i:s');
    }

    /** Run the matcher and write its proposal onto the model. */
    private function applyMatch(TrafficViolation $violation): void
    {
        $result = $this->matcher->match(
            $violation->license_plate,
            Carbon::parse($violation->occurred_at),
            (int) ($violation->parent_id ?: parentId())
        );

        $violation->vehicle_id       = $result['vehicle']?->id;
        $violation->match_confidence = $result['confidence'];
        $violation->match_source     = TrafficViolation::SOURCE_AUTO;
        $violation->matched_at       = now();
        $violation->confirmed_by     = null;
        $violation->confirmed_at     = null;

        if ($result['best'] !== null) {
            $violation->booking_id     = $result['best']['booking']->id;
            $violation->driver_user_id = $result['best']['driver_id'];
        } else {
            $violation->booking_id     = null;
            $violation->driver_user_id = null;
        }
    }

    /**
     * Bookings the owner can pin this violation to by hand.
     *
     * Scoped to the resolved vehicle: reassigning across vehicles would almost
     * always mean the plate is wrong, which is an edit, not an assignment. If
     * the plate resolved to nothing there is nothing sensible to offer.
     *
     * @return array<int,array{id:int,label:string}>
     */
    private function assignableBookings(TrafficViolation $violation): array
    {
        if (empty($violation->vehicle_id)) {
            return [];
        }

        $bookings = Booking::where('parent_id', $violation->parent_id)
            ->where('vehicle', $violation->vehicle_id)
            ->orderByDesc('start_date')
            ->limit(self::ASSIGNABLE_LIMIT)
            ->get();

        // One query for every renter rather than a find() per booking — with
        // the limit above this was up to 100 queries on a single page view.
        $drivers = User::whereIn(
            'id',
            $bookings->pluck('driver')->map(fn ($id) => (int) $id)->filter()->unique()->values()
        )->get()->keyBy('id');

        return $bookings->map(function (Booking $booking) use ($drivers) {
            $attributes = $booking->getAttributes();
            $driver     = $drivers->get((int) ($attributes['driver'] ?? 0));

            return [
                'id'    => $booking->id,
                'label' => '#'.$booking->booking_id
                    .' · '.($driver->name ?? __('Unknown renter'))
                    .' · '.$attributes['start_date'].' → '.$attributes['end_date'],
            ];
        })->all();
    }

    /**
     * Every booking the matcher considers plausible, flattened for the UI so it
     * can explain the guess instead of just asserting it.
     *
     * @param  array<string,mixed>  $result  An already-matched, people-enriched result
     * @return array<int,array<string,mixed>>
     */
    private function candidatesFor(TrafficViolation $violation, array $result): array
    {
        return array_map(function (array $candidate) use ($violation) {
            $booking = $candidate['booking'];

            return [
                'booking_id'       => $booking->id,
                'booking_number'   => $booking->booking_id,
                'start'            => trim($booking->getAttributes()['start_date'].' '.$booking->getAttributes()['start_time']),
                'end'              => trim($booking->getAttributes()['end_date'].' '.$booking->getAttributes()['end_time']),
                'status'           => $booking->status,
                'driver_id'        => $candidate['driver']?->id,
                'driver_name'      => $candidate['driver']?->name,
                'driver_email'     => $candidate['driver']?->email,
                'driver_phone'     => $candidate['driver']?->phone_number,
                'second_driver'    => $candidate['second_driver']?->name,
                'distance_seconds' => $candidate['distance_seconds'],
                'reason'           => $candidate['reason'],
                'is_current'       => (int) $booking->id === (int) $violation->booking_id,
            ];
        }, $result['candidates']);
    }
}
