<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TrafficViolation;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\ViolationMatcher;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        return Inertia::render('TrafficViolation/Show', [
            'violation'  => $trafficViolation,
            'candidates' => $this->candidatesFor($trafficViolation),
            'statuses'      => TrafficViolation::$statuses,
            'liableParties' => TrafficViolation::$liableParties,
            'assignableBookings' => $this->assignableBookings($trafficViolation),
        ]);
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

        $this->fill($trafficViolation, $request);

        // The plate or the instant moved, so the previous match is about a
        // different question. Re-run it rather than leaving a stale renter
        // attached — unless the owner had pinned it by hand.
        if ($plateOrTimeChanged && $trafficViolation->match_source !== TrafficViolation::SOURCE_MANUAL) {
            $this->applyMatch($trafficViolation);
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

    // ── Internals ────────────────────────────────────────────────────────────

    /** @return array<string,string> */
    private function rules(): array
    {
        return [
            'license_plate' => 'required',
            'occurred_date' => 'required|date',
            'occurred_time' => 'required',
            'amount'        => 'nullable|numeric',
            'notice_date'   => 'nullable|date',
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
            $originalName = $request->file('document')->getClientOriginalName();
            $fileName     = pathinfo($originalName, PATHINFO_FILENAME)
                .'_'.time().'.'.$request->file('document')->getClientOriginalExtension();

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
            $violation->driver_user_id = $result['best']['driver']?->id;
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

        return Booking::where('parent_id', $violation->parent_id)
            ->where('vehicle', $violation->vehicle_id)
            ->orderByDesc('start_date')
            ->limit(100)
            ->get()
            ->map(function (Booking $booking) {
                $attributes = $booking->getAttributes();
                $driver     = User::find((int) ($attributes['driver'] ?? 0));

                return [
                    'id'    => $booking->id,
                    'label' => '#'.$booking->booking_id
                        .' · '.($driver->name ?? __('Unknown renter'))
                        .' · '.$attributes['start_date'].' → '.$attributes['end_date'],
                ];
            })
            ->all();
    }

    /**
     * Every booking the matcher considers plausible, flattened for the UI so it
     * can explain the guess instead of just asserting it.
     *
     * @return array<int,array<string,mixed>>
     */
    private function candidatesFor(TrafficViolation $violation): array
    {
        $result = $this->matcher->match(
            $violation->license_plate,
            Carbon::parse($violation->occurred_at),
            (int) $violation->parent_id
        );

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
