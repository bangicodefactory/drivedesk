<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\RentalAgreement;
use App\Models\TrafficViolation;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\ViolationMatcher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Matching a traffic violation back to the rental that was running at the time
 * (BAN-260). Lives in Feature/ rather than Unit/ because the matcher queries
 * bookings and vehicles — the Unit suite runs on bare PHPUnit with no app.
 *
 * The contract under test: never guess silently. Exactly one containing window
 * is `exact`; anything ambiguous or merely nearby is `probable` and returns
 * every candidate; nothing close is `none`.
 */
class ViolationMatcherTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    private ViolationMatcher $matcher;

    private User $owner;

    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');

        $this->matcher = new ViolationMatcher();
        $this->owner   = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->vehicle = Vehicle::factory()->create([
            'parent_id'     => $this->owner->id,
            'license_plate' => '12345 A 6',
        ]);
    }

    private function booking(string $start, string $end, array $overrides = []): Booking
    {
        [$startDate, $startTime] = explode(' ', $start);
        [$endDate, $endTime]     = explode(' ', $end);

        return Booking::factory()->create(array_merge([
            'parent_id'  => $this->owner->id,
            'vehicle'    => $this->vehicle->id,
            'driver'     => User::factory()->create(['parent_id' => $this->owner->id, 'type' => 'driver'])->id,
            'start_date' => $startDate,
            'start_time' => $startTime,
            'end_date'   => $endDate,
            'end_time'   => $endTime,
            'status'     => 'completed',
        ], $overrides));
    }

    private function matchAt(string $when, ?string $plate = null): array
    {
        return $this->matcher->match(
            $plate ?? '12345 A 6',
            Carbon::parse($when),
            $this->owner->id
        );
    }

    /**
     * match() carries only the renter's id — free, since it is on the booking
     * row. withPeople() is the opt-in that loads the User models, so the
     * importer never pays for objects it does not read.
     */
    private function matchWithPeopleAt(string $when, ?string $plate = null): array
    {
        $at = Carbon::parse($when);

        return $this->matcher->withPeople(
            $this->matcher->match($plate ?? '12345 A 6', $at, $this->owner->id),
            $at,
            $this->owner->id
        );
    }

    // ── Exact ────────────────────────────────────────────────────────────────

    public function test_instant_inside_a_single_window_is_an_exact_match(): void
    {
        $booking = $this->booking('2026-06-01 09:00:00', '2026-06-05 18:00:00');

        $result = $this->matchAt('2026-06-03 14:32:00');

        $this->assertSame(TrafficViolation::CONFIDENCE_EXACT, $result['confidence']);
        $this->assertCount(1, $result['candidates']);
        $this->assertSame($booking->id, $result['best']['booking']->id);
        $this->assertSame(0, $result['best']['distance_seconds']);
        $this->assertSame('within_window', $result['best']['reason']);
    }

    public function test_a_completed_booking_still_matches(): void
    {
        // Violations always arrive after the fact, so completed rentals must
        // remain matchable — unlike the availability check elsewhere.
        $this->booking('2026-06-01 09:00:00', '2026-06-05 18:00:00', ['status' => 'completed']);

        $this->assertSame(TrafficViolation::CONFIDENCE_EXACT, $this->matchAt('2026-06-03 14:32:00')['confidence']);
    }

    public function test_boundary_instants_are_inside_the_window(): void
    {
        $this->booking('2026-06-01 09:00:00', '2026-06-05 18:00:00');

        $this->assertSame(TrafficViolation::CONFIDENCE_EXACT, $this->matchAt('2026-06-01 09:00:00')['confidence']);
        $this->assertSame(TrafficViolation::CONFIDENCE_EXACT, $this->matchAt('2026-06-05 18:00:00')['confidence']);
    }

    public function test_null_times_widen_the_window_rather_than_dropping_the_booking(): void
    {
        $this->booking('2026-06-01 09:00:00', '2026-06-05 18:00:00', [
            'start_time' => null,
            'end_time'   => null,
        ]);

        // 00:00 on the start day and 23:59 on the end day are both covered.
        $this->assertSame(TrafficViolation::CONFIDENCE_EXACT, $this->matchAt('2026-06-01 00:30:00')['confidence']);
        $this->assertSame(TrafficViolation::CONFIDENCE_EXACT, $this->matchAt('2026-06-05 23:45:00')['confidence']);
    }

    // ── Probable ─────────────────────────────────────────────────────────────

    public function test_same_day_turnover_returns_both_candidates_as_probable(): void
    {
        // Two rentals whose windows overlap at 10:00 — the outgoing renter's
        // window has not formally ended when the next one starts.
        $morning = $this->booking('2026-06-01 09:00:00', '2026-06-03 12:00:00');
        $next    = $this->booking('2026-06-03 10:00:00', '2026-06-07 18:00:00');

        $result = $this->matchAt('2026-06-03 11:00:00');

        $this->assertSame(TrafficViolation::CONFIDENCE_PROBABLE, $result['confidence']);
        $this->assertCount(2, $result['candidates']);

        $ids = array_map(fn ($c) => $c['booking']->id, $result['candidates']);
        $this->assertEqualsCanonicalizing([$morning->id, $next->id], $ids);

        // Most recent rental start is proposed first.
        $this->assertSame($next->id, $result['best']['booking']->id);
    }

    public function test_instant_in_a_gap_within_grace_is_probable_and_nearest_first(): void
    {
        $earlier = $this->booking('2026-06-01 09:00:00', '2026-06-03 08:00:00');
        $later   = $this->booking('2026-06-03 20:00:00', '2026-06-07 18:00:00');

        // 11:00 — three hours after `earlier` ended, nine before `later` began.
        $result = $this->matchAt('2026-06-03 11:00:00');

        $this->assertSame(TrafficViolation::CONFIDENCE_PROBABLE, $result['confidence']);
        $this->assertSame($earlier->id, $result['best']['booking']->id);
        $this->assertSame(3 * 3600, $result['best']['distance_seconds']);
        $this->assertSame('after_end', $result['best']['reason']);

        $this->assertSame($later->id, $result['candidates'][1]['booking']->id);
        $this->assertSame('before_start', $result['candidates'][1]['reason']);
    }

    // ── None ─────────────────────────────────────────────────────────────────

    public function test_instant_outside_the_grace_period_matches_nothing(): void
    {
        $this->booking('2026-06-01 09:00:00', '2026-06-03 08:00:00');

        // Two days later — far beyond the 12h default grace.
        $result = $this->matchAt('2026-06-05 11:00:00');

        $this->assertSame(TrafficViolation::CONFIDENCE_NONE, $result['confidence']);
        $this->assertSame([], $result['candidates']);
        $this->assertNull($result['best']);
        // The vehicle still resolved — only the rental is unknown.
        $this->assertSame($this->vehicle->id, $result['vehicle']->id);
    }

    public function test_cancelled_bookings_are_never_matched(): void
    {
        $this->booking('2026-06-01 09:00:00', '2026-06-05 18:00:00', ['status' => 'cancelled']);

        $result = $this->matchAt('2026-06-03 14:32:00');

        $this->assertSame(TrafficViolation::CONFIDENCE_NONE, $result['confidence']);
    }

    public function test_unknown_plate_yields_no_vehicle_and_no_match(): void
    {
        $this->booking('2026-06-01 09:00:00', '2026-06-05 18:00:00');

        $result = $this->matchAt('2026-06-03 14:32:00', '99999 Z 9');

        $this->assertNull($result['vehicle']);
        $this->assertSame(TrafficViolation::CONFIDENCE_NONE, $result['confidence']);
    }

    public function test_blank_plate_yields_no_vehicle(): void
    {
        $this->assertNull($this->matcher->resolveVehicle('   ', $this->owner->id));
        $this->assertNull($this->matcher->resolveVehicle(null, $this->owner->id));
    }

    // ── Tenancy ──────────────────────────────────────────────────────────────

    public function test_another_tenants_booking_is_never_matched(): void
    {
        $otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        Booking::factory()->create([
            'parent_id'  => $otherOwner->id,
            'vehicle'    => $this->vehicle->id,
            'start_date' => '2026-06-01',
            'start_time' => '09:00:00',
            'end_date'   => '2026-06-05',
            'end_time'   => '18:00:00',
            'status'     => 'completed',
        ]);

        $result = $this->matchAt('2026-06-03 14:32:00');

        $this->assertSame(TrafficViolation::CONFIDENCE_NONE, $result['confidence']);
    }

    public function test_a_plate_belonging_to_another_tenant_does_not_resolve(): void
    {
        $otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        Vehicle::factory()->create([
            'parent_id'     => $otherOwner->id,
            'license_plate' => '77777 B 1',
        ]);

        $this->assertNull($this->matcher->resolveVehicle('77777 B 1', $this->owner->id));
    }

    // ── Plate normalization ──────────────────────────────────────────────────

    public function test_plate_with_a_non_breaking_space_still_resolves(): void
    {
        $this->booking('2026-06-01 09:00:00', '2026-06-05 18:00:00');

        // U+00A0 between the groups, as pasted out of Excel (IST-229).
        $result = $this->matchAt('2026-06-03 14:32:00', "12345\u{00A0}A 6");

        $this->assertSame($this->vehicle->id, $result['vehicle']->id);
        $this->assertSame(TrafficViolation::CONFIDENCE_EXACT, $result['confidence']);
    }

    public function test_plate_matching_is_case_insensitive(): void
    {
        $this->assertNotNull($this->matcher->resolveVehicle('12345 a 6', $this->owner->id));
    }

    // ── Second driver ────────────────────────────────────────────────────────

    public function test_second_driver_on_a_covering_agreement_is_surfaced(): void
    {
        $booking      = $this->booking('2026-06-01 09:00:00', '2026-06-05 18:00:00');
        $secondDriver = User::factory()->create(['parent_id' => $this->owner->id, 'type' => 'driver']);

        RentalAgreement::factory()->create([
            'parent_id'         => $this->owner->id,
            'vehicle'           => $this->vehicle->id,
            'driver'            => $booking->getAttributes()['driver'],
            'driver2'           => $secondDriver->id,
            'rental_start_date' => '2026-06-01 09:00:00',
            'rental_end_date'   => '2026-06-05 18:00:00',
            'status'            => 'completed',
        ]);

        $result = $this->matchWithPeopleAt('2026-06-03 14:32:00');

        $this->assertNotNull($result['best']['second_driver']);
        $this->assertSame($secondDriver->id, $result['best']['second_driver']->id);
    }

    public function test_no_second_driver_when_no_agreement_covers_the_instant(): void
    {
        $this->booking('2026-06-01 09:00:00', '2026-06-05 18:00:00');

        $this->assertNull($this->matchWithPeopleAt('2026-06-03 14:32:00')['best']['second_driver']);
    }

    public function test_a_second_driver_from_another_vehicles_agreement_is_not_borrowed(): void
    {
        // Same renter, two cars out at the same instant. Keying the second-driver
        // lookup by driver alone would hand the other car's extra driver to this
        // violation.
        $booking = $this->booking('2026-06-01 09:00:00', '2026-06-05 18:00:00');
        $driverId = $booking->getAttributes()['driver'];

        $otherVehicle = Vehicle::factory()->create([
            'parent_id'     => $this->owner->id,
            'license_plate' => '55555 B 5',
        ]);
        $otherSecondDriver = User::factory()->create(['parent_id' => $this->owner->id, 'type' => 'driver']);

        // The agreement carrying a driver2 belongs to the *other* vehicle.
        RentalAgreement::factory()->create([
            'parent_id'         => $this->owner->id,
            'vehicle'           => $otherVehicle->id,
            'driver'            => $driverId,
            'driver2'           => $otherSecondDriver->id,
            'rental_start_date' => '2026-06-01 09:00:00',
            'rental_end_date'   => '2026-06-05 18:00:00',
            'status'            => 'completed',
        ]);

        $result = $this->matchWithPeopleAt('2026-06-03 14:32:00');

        $this->assertSame($this->vehicle->id, $result['vehicle']->id);
        $this->assertNull($result['best']['second_driver']);
    }

    public function test_match_alone_issues_no_query_per_candidate(): void
    {
        // The importer runs match() once per row; loading a User and an
        // agreement per candidate here is what made a 1000-row file 4000 queries.
        $this->booking('2026-06-01 09:00:00', '2026-06-05 18:00:00');
        $this->booking('2026-06-03 10:00:00', '2026-06-07 18:00:00');

        \DB::enableQueryLog();
        $result = $this->matchAt('2026-06-03 14:32:00');
        $queries = count(\DB::getQueryLog());
        \DB::disableQueryLog();

        $this->assertCount(2, $result['candidates']);
        // One vehicle lookup, one booking lookup. Nothing per candidate.
        $this->assertSame(2, $queries);
    }

    public function test_with_people_costs_two_queries_regardless_of_candidate_count(): void
    {
        $this->booking('2026-06-01 09:00:00', '2026-06-05 18:00:00');
        $this->booking('2026-06-03 10:00:00', '2026-06-07 18:00:00');
        $this->booking('2026-06-02 08:00:00', '2026-06-06 20:00:00');

        $at     = Carbon::parse('2026-06-03 14:32:00');
        $result = $this->matcher->match('12345 A 6', $at, $this->owner->id);

        \DB::enableQueryLog();
        $enriched = $this->matcher->withPeople($result, $at, $this->owner->id);
        $queries  = count(\DB::getQueryLog());
        \DB::disableQueryLog();

        $this->assertCount(3, $enriched['candidates']);
        $this->assertSame(2, $queries); // agreements + users, batched
    }

    // ── Driver resolution ────────────────────────────────────────────────────

    public function test_the_matched_candidate_carries_the_renter(): void
    {
        $booking = $this->booking('2026-06-01 09:00:00', '2026-06-05 18:00:00');

        $result = $this->matchAt('2026-06-03 14:32:00');

        // The id comes free with the match — it is what the write path stores.
        $this->assertSame((int) $booking->getAttributes()['driver'], $result['best']['driver_id']);

        $enriched = $this->matchWithPeopleAt('2026-06-03 14:32:00');
        $this->assertNotNull($enriched['best']['driver']);
        $this->assertSame((int) $booking->getAttributes()['driver'], $enriched['best']['driver']->id);
    }
}
