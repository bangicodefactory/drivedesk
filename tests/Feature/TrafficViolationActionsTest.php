<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\TrafficViolation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * The triage actions on a traffic violation (BAN-260): re-run the match,
 * confirm or reassign the rental by hand, and move it through recovery.
 */
class TrafficViolationActionsTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    private const PERMISSIONS = [
        'manage traffic violation',
        'create traffic violation',
        'edit traffic violation',
        'delete traffic violation',
    ];

    protected User $owner;

    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(self::PERMISSIONS);

        $this->vehicle = Vehicle::factory()->create([
            'parent_id'     => $this->owner->id,
            'license_plate' => '12345 A 6',
        ]);
    }

    private function booking(string $startDate = '2026-06-01', string $endDate = '2026-06-05'): Booking
    {
        return Booking::factory()->create([
            'parent_id'  => $this->owner->id,
            'vehicle'    => $this->vehicle->id,
            'driver'     => User::factory()->create(['parent_id' => $this->owner->id, 'type' => 'driver'])->id,
            'start_date' => $startDate,
            'start_time' => '09:00:00',
            'end_date'   => $endDate,
            'end_time'   => '18:00:00',
            'status'     => 'completed',
        ]);
    }

    private function violation(array $overrides = []): TrafficViolation
    {
        return TrafficViolation::factory()->create(array_merge([
            'parent_id'     => $this->owner->id,
            'license_plate' => '12345 A 6',
            'occurred_at'   => '2026-06-03 14:32:00',
            'vehicle_id'    => $this->vehicle->id,
        ], $overrides));
    }

    // ── Re-match ─────────────────────────────────────────────────────────────

    public function test_rematch_picks_up_a_booking_added_after_the_violation(): void
    {
        $violation = $this->violation();
        $this->assertNull($violation->booking_id);

        // The booking is corrected/created only now — the original match had
        // nothing to find.
        $booking = $this->booking();

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.rematch', $violation->id))
            ->assertSessionHas('success');

        $fresh = $violation->fresh();
        $this->assertSame($booking->id, (int) $fresh->booking_id);
        $this->assertSame('exact', $fresh->match_confidence);
        $this->assertSame('auto', $fresh->match_source);
    }

    public function test_rematch_clears_a_match_that_no_longer_holds(): void
    {
        $booking   = $this->booking();
        $violation = $this->violation(['booking_id' => $booking->id, 'occurred_at' => '2026-09-01 10:00:00']);

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.rematch', $violation->id));

        $this->assertNull($violation->fresh()->booking_id);
    }

    public function test_rematch_denied_without_permission(): void
    {
        $employee  = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $violation = $this->violation();

        $this->actingAs($employee)
            ->post(route('traffic-violation.rematch', $violation->id))
            ->assertSessionHas('error');
    }

    public function test_rematch_refuses_another_tenants_violation(): void
    {
        $violation = $this->violation(['parent_id' => 9999]);

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.rematch', $violation->id))
            ->assertRedirect(route('traffic-violation.index'));
    }

    // ── Assign ───────────────────────────────────────────────────────────────

    public function test_assign_pins_a_booking_by_hand(): void
    {
        $booking   = $this->booking('2026-01-01', '2026-01-05'); // nowhere near the instant
        $violation = $this->violation();

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.assign', $violation->id), ['booking_id' => $booking->id])
            ->assertSessionHas('success');

        $fresh = $violation->fresh();
        $this->assertSame($booking->id, (int) $fresh->booking_id);
        $this->assertSame((int) $booking->getAttributes()['driver'], (int) $fresh->driver_user_id);
        $this->assertSame('manual', $fresh->match_source);
        $this->assertSame('exact', $fresh->match_confidence);
        $this->assertNotNull($fresh->confirmed_at);
        $this->assertSame($this->owner->id, (int) $fresh->confirmed_by);
    }

    public function test_assign_confirms_the_proposed_booking(): void
    {
        $booking   = $this->booking();
        $violation = $this->violation(['booking_id' => $booking->id, 'match_source' => 'auto']);

        $this->assertNull($violation->confirmed_at);

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.assign', $violation->id), ['booking_id' => $booking->id]);

        $this->assertNotNull($violation->fresh()->confirmed_at);
    }

    public function test_assign_with_no_booking_returns_it_to_the_unmatched_queue(): void
    {
        $booking   = $this->booking();
        $violation = $this->violation(['booking_id' => $booking->id, 'driver_user_id' => $booking->getAttributes()['driver']]);

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.assign', $violation->id), ['booking_id' => null])
            ->assertSessionHas('success');

        $fresh = $violation->fresh();
        $this->assertNull($fresh->booking_id);
        $this->assertNull($fresh->driver_user_id);
        $this->assertSame('none', $fresh->match_confidence);
    }

    public function test_assign_refuses_another_tenants_booking(): void
    {
        $otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $foreign    = Booking::factory()->create([
            'parent_id' => $otherOwner->id,
            'vehicle'   => $this->vehicle->id,
            'driver'    => User::factory()->create(['parent_id' => $otherOwner->id, 'type' => 'driver'])->id,
        ]);
        $violation = $this->violation();

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.assign', $violation->id), ['booking_id' => $foreign->id])
            ->assertSessionHas('error');

        $this->assertNull($violation->fresh()->booking_id);
    }

    public function test_assign_denied_without_permission(): void
    {
        $employee  = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $booking   = $this->booking();
        $violation = $this->violation();

        $this->actingAs($employee)
            ->post(route('traffic-violation.assign', $violation->id), ['booking_id' => $booking->id])
            ->assertSessionHas('error');

        $this->assertNull($violation->fresh()->booking_id);
    }

    // ── Status ───────────────────────────────────────────────────────────────

    public function test_status_records_the_recovery_outcome(): void
    {
        $violation = $this->violation();

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.status', $violation->id), [
                'status'           => 'paid',
                'liable_party'     => 'renter',
                'amount_recovered' => '400.50',
            ])
            ->assertSessionHas('success');

        $fresh = $violation->fresh();
        $this->assertSame('paid', $fresh->status);
        $this->assertSame('renter', $fresh->liable_party);
        $this->assertSame('400.50', (string) $fresh->amount_recovered);
    }

    public function test_status_rejects_an_unknown_value(): void
    {
        $violation = $this->violation(['status' => 'new']);

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.status', $violation->id), [
                'status'       => 'invented',
                'liable_party' => 'renter',
            ])
            ->assertSessionHas('error');

        $this->assertSame('new', $violation->fresh()->status);
    }

    public function test_status_rejects_an_unknown_liable_party(): void
    {
        $violation = $this->violation();

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.status', $violation->id), [
                'status'       => 'paid',
                'liable_party' => 'the_dog',
            ])
            ->assertSessionHas('error');

        $this->assertSame('new', $violation->fresh()->status);
    }

    public function test_status_refuses_another_tenants_violation(): void
    {
        $violation = $this->violation(['parent_id' => 9999, 'status' => 'new']);

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.status', $violation->id), [
                'status'       => 'paid',
                'liable_party' => 'renter',
            ])
            ->assertRedirect(route('traffic-violation.index'));

        $this->assertSame('new', $violation->fresh()->status);
    }

    // ── Assignable list ──────────────────────────────────────────────────────

    public function test_show_offers_the_vehicles_bookings_for_manual_assignment(): void
    {
        $this->booking();
        $violation = $this->violation();

        $this->actingAs($this->owner)
            ->get(route('traffic-violation.show', $violation->id))
            ->assertInertia(fn (Assert $page) => $page->has('assignableBookings', 1));
    }

    public function test_show_offers_nothing_when_the_plate_resolved_to_no_vehicle(): void
    {
        $this->booking();
        $violation = $this->violation(['vehicle_id' => null]);

        $this->actingAs($this->owner)
            ->get(route('traffic-violation.show', $violation->id))
            ->assertInertia(fn (Assert $page) => $page->has('assignableBookings', 0));
    }
}
