<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
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

        // The two payment permissions are created but deliberately not granted
        // below: one test asserts paymentCreate() refuses without them.
        foreach ([
            'manage booking', 'create booking', 'show booking', 'edit booking', 'delete booking',
            'create booking payment', 'delete booking payment',
        ] as $p) {
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

    // ── BAN-289: paths the scope turned into 500s ────────────────────────────
    // Making a foreign row unresolvable is only half the job — several actions
    // dereferenced the result of Booking::find() with no null check, so the
    // scope converted a silent cross-tenant leak into a 500. These pin the 404.

    public function test_edit_answers_404_for_another_tenants_booking(): void
    {
        $this->actingAs($this->owner)
            ->get(route('booking.edit', Crypt::encrypt($this->foreignBooking->id)))
            ->assertStatus(404);
    }

    public function test_payment_create_answers_404_for_another_tenants_booking(): void
    {
        $this->owner->givePermissionTo('create booking payment');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->actingAs($this->owner)
            ->get(route('booking.payment.create', $this->foreignBooking->id))
            ->assertStatus(404);
    }

    /** paymentCreate() previously had no permission check of any kind. */
    public function test_payment_create_requires_the_payment_permission(): void
    {
        $own = $this->ownBooking();

        $this->actingAs($this->owner)   // holds booking perms, not the payment one
            ->get(route('booking.payment.create', $own->id))
            ->assertRedirect();
    }

    /**
     * The regression that mattered most: paymentDestroy() deleted the payment
     * and its TVA rows *before* looking the booking up, so under the scope a
     * cross-tenant call destroyed rows and then 500'd — a half-completed write
     * against another tenant. The payment must survive intact.
     */
    public function test_payment_destroy_does_not_delete_another_tenants_payment(): void
    {
        $this->owner->givePermissionTo('delete booking payment');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $payment = BookingPayment::create([
            'booking_id'     => $this->foreignBooking->id,
            'amount'         => 100,
            'date'           => '2026-07-01',
            'payment_method' => 'espece',
            'parent_id'      => $this->otherOwner->id,
        ]);

        $this->actingAs($this->owner)
            ->delete(route('booking.payment.destroy', [
                'id'  => $this->foreignBooking->id,
                'pid' => $payment->id,
            ]))
            ->assertStatus(404);

        $this->assertDatabaseHas('booking_payments', ['id' => $payment->id]);
    }

    // -- Vehicle (BAN-290) ---------------------------------------------------

    public function test_another_tenants_vehicle_does_not_resolve(): void
    {
        $foreignVehicle = Vehicle::acrossTenants()
            ->where('parent_id', $this->otherOwner->id)
            ->firstOrFail();

        $this->actingAs($this->owner);

        $this->assertNull(Vehicle::find($foreignVehicle->id));
    }

    public function test_super_admin_still_sees_every_tenants_vehicles(): void
    {
        $foreignVehicle = Vehicle::acrossTenants()
            ->where('parent_id', $this->otherOwner->id)
            ->firstOrFail();

        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $this->actingAs($superAdmin);

        $this->assertNotNull(Vehicle::find($foreignVehicle->id));
    }

    /**
     * A bare `exists:vehicles,id` rule queries the table directly and ignores
     * the model's global scope, so another tenant's vehicle id passed
     * validation and then resolved to null inside the action. The rule is now
     * scoped, so the failure is a field error rather than a 500.
     */
    public function test_booking_store_rejects_another_tenants_vehicle_in_validation(): void
    {
        $foreignVehicle = Vehicle::acrossTenants()
            ->where('parent_id', $this->otherOwner->id)
            ->firstOrFail();

        $ownDriver = User::factory()->driver()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->post(route('booking.store'), [
                'vehicle'          => $foreignVehicle->id,
                'start_date_time'  => '2026-06-01 09:00',
                'end_date_time'    => '2026-06-04 18:00',
                'driver'           => $ownDriver->id,
                'pickup_address'   => 'Airport',
                'drop_off_address' => 'Hotel',
                'status'           => 'yet_to_start',
                'amount'           => 300,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['vehicle']);
    }

    /**
     * BAN-294: the scoped exists rule needs the same super-admin exemption the
     * model scope has. A bare where('parent_id', parentId()) rejected every
     * vehicle for a super admin — parentId() returns their own id, which is
     * never a vehicle's parent_id — so they could not save a booking at all,
     * not even re-saving one with its vehicle unchanged.
     */
    public function test_super_admin_can_save_a_booking_against_any_vehicle(): void
    {
        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $superAdmin->givePermissionTo('create booking');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $foreignVehicle = Vehicle::acrossTenants()
            ->where('parent_id', $this->otherOwner->id)->firstOrFail();
        $foreignDriver = User::where('parent_id', $this->otherOwner->id)
            ->where('type', 'driver')->firstOrFail();

        $this->actingAs($superAdmin)
            ->post(route('booking.store'), [
                'vehicle'          => $foreignVehicle->id,
                'start_date_time'  => '2026-06-01 09:00',
                'end_date_time'    => '2026-06-04 18:00',
                'driver'           => $foreignDriver->id,
                'pickup_address'   => 'Airport',
                'drop_off_address' => 'Hotel',
                'status'           => 'yet_to_start',
                'amount'           => 300,
            ])
            ->assertSessionHasNoErrors();
    }

    /**
     * BAN-294: RentalAgreementController validated `vehicle` as `required` only,
     * with no exists of any kind, and then dereferenced Vehicle::find() when
     * creating the companion booking. Under the tenant scope a foreign id
     * resolved null and fatalled there — after the agreement row was already
     * saved, outside any transaction, leaving an agreement with no booking.
     */
    public function test_rental_agreement_rejects_another_tenants_vehicle(): void
    {
        $foreignVehicle = Vehicle::acrossTenants()
            ->where('parent_id', $this->otherOwner->id)->firstOrFail();
        $ownDriver = User::factory()->driver()->create(['parent_id' => $this->owner->id]);

        $this->owner->givePermissionTo('create rental agreement');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->actingAs($this->owner)
            ->post(route('rental-agreement.store'), [
                'vehicle'            => $foreignVehicle->id,
                'driver'             => $ownDriver->id,
                'rental_start_date'  => '2026-07-01',
                'rental_start_time'  => '09:00',
                'rental_end_date'    => '2026-07-04',
                'rental_end_time'    => '18:00',
                'rental_duration'    => 3,
                'status'             => 'active',
                'create_booking'     => 1,
            ])
            ->assertSessionHasErrors(['vehicle']);

        $this->assertDatabaseCount('rental_agreements', 0);
    }

    private function ownBooking(): Booking
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $driver  = User::factory()->driver()->create(['parent_id' => $this->owner->id]);

        return Booking::factory()->create([
            'vehicle'   => $vehicle->id,
            'driver'    => $driver->id,
            'parent_id' => $this->owner->id,
        ]);
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
