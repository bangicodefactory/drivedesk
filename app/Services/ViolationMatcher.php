<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\RentalAgreement;
use App\Models\TrafficViolation;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Answers "who had this vehicle at this instant?" for a traffic violation
 * notice (BAN-260).
 *
 * A violation carries a plate and a timestamp; the renter is inferred from the
 * booking whose rental window contains that timestamp. The result is always a
 * *proposal* carrying a confidence label, never a decision, because the data
 * cannot support certainty:
 *
 *  - `bookings` records only the planned end (`end_date` + `end_time`), edited
 *    in place. There is no actual-return timestamp and no extension concept, so
 *    a late return or a retroactively edited window is invisible here.
 *  - Same-day turnovers legitimately produce two candidate windows.
 *  - A violation timestamp can fall in a genuine gap (vehicle at the agency).
 *
 * Hence: exact (one window contains it), probable (several do, or one is within
 * the grace period), none (nothing close). The owner confirms or reassigns.
 *
 * This class is the single source of truth for matching — the manual form, the
 * importer, and the re-match action all call match().
 */
class ViolationMatcher
{
    /** Booking statuses that can never own a violation. */
    private const EXCLUDED_STATUSES = ['cancelled'];

    /**
     * Match a violation to the bookings that could have been running at $occurredAt.
     *
     * Candidates carry the renter's *id*, which is free — it is already on the
     * booking row. Call withPeople() on the result to load the User models when
     * you need names, so the importer does not pay for objects it never reads.
     *
     * @param  Collection<int,Vehicle>|null  $vehicleCache  Preloaded tenant vehicles,
     *         so a bulk import resolves plates without a query per row.
     * @return array{
     *     vehicle: Vehicle|null,
     *     confidence: string,
     *     candidates: array<int,array{booking:Booking,driver_id:int|null,distance_seconds:int,reason:string}>,
     *     best: array{booking:Booking,driver_id:int|null,distance_seconds:int,reason:string}|null
     * }
     */
    public function match(?string $plate, CarbonInterface $occurredAt, int $parentId, ?Collection $vehicleCache = null): array
    {
        $vehicle = $this->resolveVehicle($plate, $parentId, $vehicleCache);

        if ($vehicle === null) {
            return $this->emptyResult(null);
        }

        $graceHours = $this->graceHours();
        $at         = Carbon::parse($occurredAt->format('Y-m-d H:i:s'));
        $lowerBound = $at->copy()->subHours($graceHours);
        $upperBound = $at->copy()->addHours($graceHours);

        // Prefilter on the plain date columns — a superset of what the grace
        // window can reach. The precise instant comparison happens in PHP
        // below, because start/end are split across a date and a nullable time
        // column: the CONCAT(start_date,' ',start_time) idiom used elsewhere in
        // the codebase cannot use an index and yields NULL when the time is
        // NULL, which would silently drop the booking.
        $bookings = Booking::where('parent_id', $parentId)
            ->where('vehicle', $vehicle->id)
            ->where(function ($q) {
                // status is nullable, and `status != 'cancelled'` is NULL-unsafe.
                $q->whereNull('status')->orWhereNotIn('status', self::EXCLUDED_STATUSES);
            })
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            // Plain comparisons, not whereDate(): these are already `date`
            // columns, so wrapping them in DATE() would only make the predicate
            // non-sargable — the exact thing the comment above avoids.
            ->where('start_date', '<=', $upperBound->toDateString())
            ->where('end_date', '>=', $lowerBound->toDateString())
            ->get();

        if ($bookings->isEmpty()) {
            return $this->emptyResult($vehicle);
        }

        $graceSeconds = $graceHours * 3600;
        $containing   = [];
        $near         = [];

        foreach ($bookings as $booking) {
            $window = $this->windowFor($booking);
            if ($window === null) {
                continue;
            }
            [$start, $end] = $window;

            if ($at->betweenIncluded($start, $end)) {
                $containing[] = ['booking' => $booking, 'distance_seconds' => 0, 'reason' => 'within_window'];
                continue;
            }

            // abs(): Carbon 3's diffInSeconds is signed, so the raw value is
            // negative in both of these directions.
            $distance = (int) abs($at->lessThan($start)
                ? $start->diffInSeconds($at)
                : $at->diffInSeconds($end));

            if ($distance <= $graceSeconds) {
                $near[] = [
                    'booking'          => $booking,
                    'distance_seconds' => $distance,
                    'reason'           => $at->lessThan($start) ? 'before_start' : 'after_end',
                ];
            }
        }

        if ($containing !== []) {
            // Several overlapping windows is a data reality (same-day turnover,
            // an edited end date). Show the most recent rental first, but do not
            // pretend to be sure.
            usort($containing, fn ($a, $b) => $this->windowStart($b['booking']) <=> $this->windowStart($a['booking']));

            $candidates = $this->hydrate($containing);

            return [
                'vehicle'    => $vehicle,
                'confidence' => count($candidates) === 1
                    ? TrafficViolation::CONFIDENCE_EXACT
                    : TrafficViolation::CONFIDENCE_PROBABLE,
                'candidates' => $candidates,
                'best'       => $candidates[0],
            ];
        }

        if ($near !== []) {
            usort($near, fn ($a, $b) => $a['distance_seconds'] <=> $b['distance_seconds']);

            $candidates = $this->hydrate($near);

            return [
                'vehicle'    => $vehicle,
                'confidence' => TrafficViolation::CONFIDENCE_PROBABLE,
                'candidates' => $candidates,
                'best'       => $candidates[0],
            ];
        }

        return $this->emptyResult($vehicle);
    }

    /**
     * Resolve a plate to one of the tenant's vehicles.
     *
     * Plates are unique per tenant only (`vehicles_parent_plate_unique`), so
     * this is always scoped by parent_id. Comparison goes through
     * Vehicle::plateKey(), which collapses the non-breaking spaces that Excel
     * and web pastes inject (IST-229).
     *
     * @param  Collection<int,Vehicle>|null  $vehicleCache
     */
    public function resolveVehicle(?string $plate, int $parentId, ?Collection $vehicleCache = null): ?Vehicle
    {
        $key = Vehicle::plateKey($plate ?? '');

        if ($key === '') {
            return null;
        }

        $vehicles = $vehicleCache ?? Vehicle::where('parent_id', $parentId)
            ->whereNotNull('license_plate')
            ->get();

        return $vehicles->first(
            fn (Vehicle $vehicle) => (int) $vehicle->parent_id === $parentId
                && Vehicle::plateKey($vehicle->license_plate) === $key
        );
    }

    /** Grace period around a rental window, in hours. Client-configurable. */
    public function graceHours(): int
    {
        return (int) config('client.violation_match_grace_hours', 12);
    }

    /**
     * The booking's rental window as real instants.
     *
     * A missing time is widened, not dropped: a null start_time means the day
     * started at 00:00:00 and a null end_time means it ran to 23:59:59.
     *
     * @return array{0:Carbon,1:Carbon}|null
     */
    private function windowFor(Booking $booking): ?array
    {
        $attributes = $booking->getAttributes();
        $startDate  = $attributes['start_date'] ?? null;
        $endDate    = $attributes['end_date'] ?? null;

        if (empty($startDate) || empty($endDate)) {
            return null;
        }

        try {
            $start = Carbon::parse(substr((string) $startDate, 0, 10).' '.($attributes['start_time'] ?: '00:00:00'));
            $end   = Carbon::parse(substr((string) $endDate, 0, 10).' '.($attributes['end_time'] ?: '23:59:59'));
        } catch (\Exception $e) {
            return null;
        }

        // Defensive: inverted windows exist in imported data.
        return $end->lessThan($start) ? [$end, $start] : [$start, $end];
    }

    private function windowStart(Booking $booking): string
    {
        $window = $this->windowFor($booking);

        return $window ? $window[0]->format('Y-m-d H:i:s') : '';
    }

    /**
     * Finish a candidate row without touching the database.
     *
     * The renter's id is already on the booking, and that is all the write path
     * needs — `driver_user_id` is an id, not a name. Loading User models here
     * would cost a query per candidate, per imported row, to build objects the
     * importer never reads. Call withPeople() when you actually need names.
     *
     * @param  array<int,array{booking:Booking,distance_seconds:int,reason:string}>  $rows
     * @return array<int,array{booking:Booking,driver_id:int|null,distance_seconds:int,reason:string}>
     */
    private function hydrate(array $rows): array
    {
        return array_map(function (array $row) {
            $driverId = (int) ($row['booking']->getAttributes()['driver'] ?? 0);

            return [
                'booking'          => $row['booking'],
                'driver_id'        => $driverId > 0 ? $driverId : null,
                'distance_seconds' => $row['distance_seconds'],
                'reason'           => $row['reason'],
            ];
        }, $rows);
    }

    /**
     * Attach the renter and any second driver to a match result's candidates.
     *
     * Two queries regardless of candidate count, and only paid for by callers
     * that render people (the detail page). Returns the result unchanged when
     * there is nothing to enrich.
     *
     * @param  array<string,mixed>  $result  A value returned by match()
     * @return array<string,mixed>
     */
    public function withPeople(array $result, CarbonInterface $at, int $parentId): array
    {
        if ($result['candidates'] === []) {
            return $result;
        }

        $driverIds  = [];
        $vehicleIds = [];

        foreach ($result['candidates'] as $candidate) {
            $attributes = $candidate['booking']->getAttributes();
            $vehicleId  = (int) ($attributes['vehicle'] ?? 0);

            if ($candidate['driver_id']) {
                $driverIds[$candidate['driver_id']] = $candidate['driver_id'];
            }
            if ($vehicleId > 0) {
                $vehicleIds[$vehicleId] = $vehicleId;
            }
        }

        // driver id => second driver's user id, for agreements covering $at.
        $secondDriverIds = $this->secondDriverIds($driverIds, $vehicleIds, $at, $parentId);

        // array_merge, not `+`: both arrays are keyed by the *driver* id, so a
        // union would drop every second driver on the key collision.
        $users = User::whereIn(
            'id',
            array_unique(array_merge(array_values($driverIds), array_values($secondDriverIds)))
        )->get()->keyBy('id');

        $enrich = function (array $candidate) use ($users, $secondDriverIds) {
            $secondId = $secondDriverIds[$candidate['driver_id']] ?? null;

            return $candidate + [
                'driver'        => $candidate['driver_id'] ? $users->get($candidate['driver_id']) : null,
                'second_driver' => $secondId ? $users->get($secondId) : null,
            ];
        };

        $result['candidates'] = array_map($enrich, $result['candidates']);
        $result['best']       = $result['candidates'][0] ?? null;

        return $result;
    }

    /**
     * Additional drivers named on rental agreements covering this instant,
     * keyed by the agreement's primary driver.
     *
     * `bookings` has one driver, but `rental_agreements.driver2` exists and its
     * dates are real datetimes. Surfaced so the owner can see who else was
     * authorised to drive — it is never auto-assigned, because the agreement
     * does not say who was actually behind the wheel.
     *
     * @param  array<int,int>  $driverIds
     * @param  array<int,int>  $vehicleIds
     * @return array<int,int>
     */
    private function secondDriverIds(array $driverIds, array $vehicleIds, CarbonInterface $at, int $parentId): array
    {
        if ($driverIds === [] || $vehicleIds === []) {
            return [];
        }

        $instant = $at->format('Y-m-d H:i:s');

        return RentalAgreement::where('parent_id', $parentId)
            ->whereIn('vehicle', array_values($vehicleIds))
            ->whereIn('driver', array_values($driverIds))
            ->whereNotNull('driver2')
            ->where(function ($q) {
                $q->whereNull('status')->orWhereNotIn('status', self::EXCLUDED_STATUSES);
            })
            ->where('rental_start_date', '<=', $instant)
            ->where('rental_end_date', '>=', $instant)
            ->get()
            ->mapWithKeys(fn (RentalAgreement $a) => [(int) $a->driver => (int) $a->driver2])
            ->all();
    }

    /** @return array{vehicle:Vehicle|null,confidence:string,candidates:array<int,mixed>,best:null} */
    private function emptyResult(?Vehicle $vehicle): array
    {
        return [
            'vehicle'    => $vehicle,
            'confidence' => TrafficViolation::CONFIDENCE_NONE,
            'candidates' => [],
            'best'       => null,
        ];
    }
}
