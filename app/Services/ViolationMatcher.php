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
     * @param  Collection<int,Vehicle>|null  $vehicleCache  Preloaded tenant vehicles,
     *         so a bulk import resolves plates without a query per row.
     * @return array{
     *     vehicle: Vehicle|null,
     *     confidence: string,
     *     candidates: array<int,array{booking:Booking,driver:User|null,second_driver:User|null,distance_seconds:int,reason:string}>,
     *     best: array{booking:Booking,driver:User|null,second_driver:User|null,distance_seconds:int,reason:string}|null
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
            ->whereDate('start_date', '<=', $upperBound->toDateString())
            ->whereDate('end_date', '>=', $lowerBound->toDateString())
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
                $containing[] = $this->candidate($booking, 0, 'within_window', $at);
                continue;
            }

            // abs(): Carbon 3's diffInSeconds is signed, so the raw value is
            // negative in both of these directions.
            $distance = (int) abs($at->lessThan($start)
                ? $start->diffInSeconds($at)
                : $at->diffInSeconds($end));

            if ($distance <= $graceSeconds) {
                $near[] = $this->candidate(
                    $booking,
                    $distance,
                    $at->lessThan($start) ? 'before_start' : 'after_end',
                    $at
                );
            }
        }

        if ($containing !== []) {
            // Several overlapping windows is a data reality (same-day turnover,
            // an edited end date). Show the most recent rental first, but do not
            // pretend to be sure.
            usort($containing, fn ($a, $b) => $this->windowStart($b['booking']) <=> $this->windowStart($a['booking']));

            return [
                'vehicle'    => $vehicle,
                'confidence' => count($containing) === 1
                    ? TrafficViolation::CONFIDENCE_EXACT
                    : TrafficViolation::CONFIDENCE_PROBABLE,
                'candidates' => $containing,
                'best'       => $containing[0],
            ];
        }

        if ($near !== []) {
            usort($near, fn ($a, $b) => $a['distance_seconds'] <=> $b['distance_seconds']);

            return [
                'vehicle'    => $vehicle,
                'confidence' => TrafficViolation::CONFIDENCE_PROBABLE,
                'candidates' => $near,
                'best'       => $near[0],
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
     * @return array{booking:Booking,driver:User|null,second_driver:User|null,distance_seconds:int,reason:string}
     */
    private function candidate(Booking $booking, int $distanceSeconds, string $reason, Carbon $at): array
    {
        $driverId = (int) ($booking->getAttributes()['driver'] ?? 0);

        return [
            'booking'          => $booking,
            'driver'           => $driverId > 0 ? User::find($driverId) : null,
            'second_driver'    => $this->secondDriverFor($booking, $at),
            'distance_seconds' => $distanceSeconds,
            'reason'           => $reason,
        ];
    }

    /**
     * The additional driver named on a rental agreement covering this instant.
     *
     * `bookings` has one driver, but `rental_agreements.driver2` exists and its
     * dates are real datetimes. Surfaced so the owner can see who else was
     * authorised to drive — it is never auto-assigned, because the agreement
     * does not say who was actually behind the wheel.
     */
    public function secondDriverFor(Booking $booking, CarbonInterface $at): ?User
    {
        $attributes = $booking->getAttributes();
        $driverId   = (int) ($attributes['driver'] ?? 0);
        $vehicleId  = (int) ($attributes['vehicle'] ?? 0);

        if ($driverId <= 0 || $vehicleId <= 0) {
            return null;
        }

        $agreement = RentalAgreement::where('parent_id', $booking->parent_id)
            ->where('vehicle', $vehicleId)
            ->where('driver', $driverId)
            ->whereNotNull('driver2')
            ->where(function ($q) {
                $q->whereNull('status')->orWhereNotIn('status', self::EXCLUDED_STATUSES);
            })
            ->where('rental_start_date', '<=', $at->format('Y-m-d H:i:s'))
            ->where('rental_end_date', '>=', $at->format('Y-m-d H:i:s'))
            ->first();

        if ($agreement === null || empty($agreement->driver2)) {
            return null;
        }

        return User::find((int) $agreement->driver2);
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
