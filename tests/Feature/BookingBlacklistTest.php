<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\DriverBlacklist;
use App\Models\Place;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Blacklist enforcement on booking creation (BAN-252, PR 2): a blacklisted
 * driver blocks the booking unless the owner acknowledges, and the override is
 * recorded.
 */
class BookingBlacklistTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected User $driver;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        Permission::firstOrCreate(['name' => 'create booking', 'guard_name' => 'web']);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo('create booking');

        $this->driver = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        $this->vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
    }

    private function blacklistDriver(string $reason = 'Damaged a vehicle'): DriverBlacklist
    {
        return DriverBlacklist::create([
            'driver_user_id' => $this->driver->id,
            'parent_id'      => $this->owner->id,
            'reason'         => $reason,
            'blacklisted_by' => $this->owner->id,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'vehicle'          => $this->vehicle->id,
            'start_date_time'  => '2026-06-01 09:00',
            'end_date_time'    => '2026-06-04 18:00',
            'driver'           => $this->driver->id,
            'pickup_address'   => (string) Place::factory()->create()->id,
            'drop_off_address' => (string) Place::factory()->create()->id,
            'status'           => 'yet_to_start',
            'amount'           => 300,
        ], $overrides);
    }

    public function test_booking_blocked_when_driver_blacklisted_without_acknowledgement(): void
    {
        $this->blacklistDriver();

        $this->actingAs($this->owner)
            ->post(route('booking.store'), $this->payload())
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('bookings', ['driver' => $this->driver->id]);
    }

    public function test_booking_proceeds_with_acknowledgement_and_records_override(): void
    {
        $blacklist = $this->blacklistDriver('Late returns');

        $this->actingAs($this->owner)
            ->post(route('booking.store'), $this->payload(['acknowledge_blacklist' => 1]))
            ->assertRedirect();

        $booking = Booking::where('driver', $this->driver->id)->first();
        $this->assertNotNull($booking);

        $blacklist->refresh();
        $this->assertCount(1, $blacklist->overrides);
        $entry = $blacklist->overrides[0];
        $this->assertSame('booking', $entry['context_type']);
        $this->assertSame($booking->id, $entry['context_id']);
        $this->assertSame($this->owner->id, $entry['by_user_id']);
        $this->assertSame($this->driver->id, $entry['driver_user_id']);
        $this->assertSame('Late returns', $entry['reason_snapshot']);
    }

    public function test_clean_driver_books_without_warning(): void
    {
        $this->actingAs($this->owner)
            ->post(route('booking.store'), $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('bookings', ['driver' => $this->driver->id]);
    }
}
