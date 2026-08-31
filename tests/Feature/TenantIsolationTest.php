<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Tenant isolation for Booking (roadmap Tranche S.1, pilot).
 *
 * Multiple owners share one database, with `parent_id` as the boundary. Before
 * this, `update()` and `destroy()` took a route-model-bound Booking, checked
 * only the permission, and wrote to whichever row the id resolved to — so a
 * permission in one tenant reached another tenant's booking.
 *
 * The `BelongsToTenant` global scope now constrains every Booking query, which
 * makes the foreign row fail to resolve (404) before a controller body runs.
 * These tests pin the three cases that have to keep working: the tenant is
 * blocked, the super admin is not, and code with no authenticated user
 * (console, seeders, queue) is unscoped.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected User $otherOwner;
    protected Booking $foreignBooking;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        foreach (['manage booking', 'create booking', 'show booking', 'edit booking', 'delete booking'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(['manage booking', 'create booking', 'show booking', 'edit booking', 'delete booking']);

        $this->otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        // Built with no authenticated user, so the scope stays off and the row
        // is created against the *other* tenant on purpose.
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->otherOwner->id]);
        $driver  = User::factory()->driver()->create(['parent_id' => $this->otherOwner->id]);

        $this->foreignBooking = Booking::factory()->create([
            'vehicle'   => $vehicle->id,
            'driver'    => $driver->id,
            'parent_id' => $this->otherOwner->id,
        ]);
    }

    public function test_another_tenants_booking_does_not_resolve(): void
    {
        $this->actingAs($this->owner);

        $this->assertNull(Booking::find($this->foreignBooking->id));
        $this->assertCount(0, Booking::all());
    }

    public function test_update_cannot_reach_another_tenants_booking(): void
    {
        $originalAmount = $this->foreignBooking->amount;

        $this->actingAs($this->owner)
            ->put(route('booking.update', $this->foreignBooking->id), [
                'vehicle'          => 1,
                'start_date_time'  => '2026-07-01 09:00',
                'end_date_time'    => '2026-07-04 18:00',
                'driver'           => 1,
                'pickup_address'   => 'Airport',
                'drop_off_address' => 'Hotel',
                'status'           => 'yet_to_start',
                'amount'           => 999999,
                'daily_price'      => 150,
            ])
            ->assertStatus(404);

        // The row is untouched; re-read without the scope to see it at all.
        $fresh = Booking::acrossTenants()->find($this->foreignBooking->id);
        $this->assertEquals((string) $originalAmount, (string) $fresh->amount);
    }

    public function test_destroy_cannot_reach_another_tenants_booking(): void
    {
        $this->actingAs($this->owner)
            ->delete(route('booking.destroy', $this->foreignBooking->id))
            ->assertStatus(404);

        $this->assertNotNull(Booking::acrossTenants()->find($this->foreignBooking->id));
    }

    /**
     * `parentId()` returns a super admin's *own* id, which is never any
     * tenant's `parent_id` — so scoping on it would hide every row in the
     * system from them. The trait bypasses the scope for that role instead.
     */
    public function test_super_admin_still_sees_every_tenants_bookings(): void
    {
        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);

        $this->actingAs($superAdmin);

        $this->assertNotNull(Booking::find($this->foreignBooking->id));
    }

    /**
     * Console commands, seeders and queue jobs run with no authenticated user.
     * `parentId()` dereferences `Auth::user()` and would fatal there, so the
     * scope has to stay off — `demo:seed` and `reminders:*` depend on it.
     */
    public function test_scope_is_inert_without_an_authenticated_user(): void
    {
        $this->assertFalse(auth()->check());

        $this->assertNotNull(Booking::find($this->foreignBooking->id));
        $this->assertCount(1, Booking::all());
    }

    public function test_a_tenant_still_reaches_its_own_booking(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $driver  = User::factory()->driver()->create(['parent_id' => $this->owner->id]);

        $own = Booking::factory()->create([
            'vehicle'   => $vehicle->id,
            'driver'    => $driver->id,
            'parent_id' => $this->owner->id,
        ]);

        $this->actingAs($this->owner);

        $this->assertNotNull(Booking::find($own->id));
        $this->assertCount(1, Booking::all());
    }
}
