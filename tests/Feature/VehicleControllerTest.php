<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class VehicleControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected VehicleType $vehicleType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        $perms = ['manage vehicle', 'create vehicle', 'edit vehicle', 'delete vehicle'];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo($perms);

        $this->vehicleType = VehicleType::factory()->create(['parent_id' => $this->owner->id]);
    }

    private function supportLogin(): User
    {
        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $superAdmin->givePermissionTo(['manage vehicle', 'create vehicle']);

        return $superAdmin;
    }

    // ── BAN-315: a support login writes into the customer's tenant ────────────
    //
    // parentId() returns a super admin their own id, which is no tenant's key,
    // so anything created during a support session was invisible to the
    // customer who owns the deployment -- and invisible in a way they could not
    // detect, because support bypasses the tenant scope on reads and sees it
    // fine. A vehicle nobody can book; a booking that never blocks the calendar.

    public function test_a_vehicle_created_during_support_belongs_to_the_customer(): void
    {
        $superAdmin = $this->supportLogin();

        $this->actingAs($superAdmin)
            ->post(route('vehicle.store'), $this->validPayload(['name' => 'Support Car']))
            ->assertRedirect(route('vehicle.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', [
            'name'      => 'Support Car',
            'parent_id' => $this->owner->id,
        ]);
        $this->assertDatabaseMissing('vehicles', [
            'name'      => 'Support Car',
            'parent_id' => $superAdmin->id,
        ]);
    }

    /** The point of the fix: the customer can actually see it. */
    public function test_the_customer_sees_a_vehicle_created_during_support(): void
    {
        $this->actingAs($this->supportLogin())
            ->post(route('vehicle.store'), $this->validPayload(['name' => 'Support Car']));

        $this->actingAs($this->owner)
            ->get(route('vehicle.index'))
            ->assertOk()
            ->assertSee('Support Car');
    }

    public function test_an_owners_own_write_is_unchanged(): void
    {
        $this->actingAs($this->owner)
            ->post(route('vehicle.store'), $this->validPayload(['name' => 'Owner Car']));

        $this->assertDatabaseHas('vehicles', [
            'name'      => 'Owner Car',
            'parent_id' => $this->owner->id,
        ]);
    }

    /**
     * Two owners means the deployment key is not knowable, so support writes
     * land where they always did rather than in an arbitrary tenant.
     */
    public function test_support_writes_fall_back_when_the_owner_is_ambiguous(): void
    {
        User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $superAdmin = $this->supportLogin();

        $this->actingAs($superAdmin)
            ->post(route('vehicle.store'), $this->validPayload(['name' => 'Ambiguous Car']));

        $this->assertDatabaseHas('vehicles', [
            'name'      => 'Ambiguous Car',
            'parent_id' => $superAdmin->id,
        ]);
    }

    // ── BAN-316: both sides resolve the same tenant key ──────────
    //
    // BAN-315 redirected the write stamps alone. That was worse than the bug:
    // the number generators and uniqueness guards still resolved through
    // parentId(), an empty bucket for a super admin, so a support-created row
    // landed in the customer's tenant carrying a number from nobody's.

    public function test_a_support_created_vehicle_does_not_reuse_a_customer_number(): void
    {
        Vehicle::factory()->create(['parent_id' => $this->owner->id, 'vehicle_id' => 1]);
        Vehicle::factory()->create(['parent_id' => $this->owner->id, 'vehicle_id' => 2]);

        $this->actingAs($this->supportLogin())
            ->post(route('vehicle.store'), $this->validPayload(['name' => 'Support Car']));

        $created = Vehicle::withoutGlobalScope('tenant')->where('name', 'Support Car')->first();

        $this->assertNotNull($created);
        $this->assertSame($this->owner->id, (int) $created->parent_id);
        $this->assertSame(3, (int) $created->vehicle_id);
    }

    /**
     * licensePlateExists() filtered by parentId(), so for a support login it
     * matched nothing and always returned false -- re-opening the duplicate
     * plate the guard exists to prevent, on the customer's own fleet.
     */
    public function test_support_cannot_add_a_plate_the_customer_already_has(): void
    {
        Vehicle::factory()->create([
            'parent_id'     => $this->owner->id,
            'license_plate' => '1234-A-56',
        ]);

        $this->actingAs($this->supportLogin())
            ->post(route('vehicle.store'), $this->validPayload([
                'name'          => 'Duplicate Plate',
                'license_plate' => '1234-A-56',
            ]))
            // The guard reports through the validator, not an `error` flash.
            ->assertSessionHasErrors();

        $this->assertDatabaseMissing('vehicles', ['name' => 'Duplicate Plate']);
    }

    /**
     * index() filters by parentId() explicitly rather than through the global
     * scope, so redirecting only the write left support unable to see what it
     * had just created -- trading one invisibility for another.
     */
    public function test_support_can_see_the_vehicle_it_just_created(): void
    {
        $superAdmin = $this->supportLogin();

        $this->actingAs($superAdmin)
            ->post(route('vehicle.store'), $this->validPayload(['name' => 'Support Car']));

        $this->actingAs($superAdmin)
            ->get(route('vehicle.index'))
            ->assertOk()
            ->assertSee('Support Car');
    }

    // ── unauthenticated ───────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('vehicle.index'))->assertRedirect(route('login'));
    }

    public function test_store_requires_auth(): void
    {
        $this->post(route('vehicle.store'))->assertRedirect(route('login'));
    }

    public function test_update_requires_auth(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $this->put(route('vehicle.update', $vehicle))->assertRedirect(route('login'));
    }

    public function test_destroy_requires_auth(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $this->delete(route('vehicle.destroy', $vehicle))->assertRedirect(route('login'));
    }

    // ── permission denied ─────────────────────────────────────────────────────

    public function test_index_denied_without_manage_vehicle(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->get(route('vehicle.index'))->assertSessionHas('error');
    }

    public function test_store_denied_without_create_vehicle(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->post(route('vehicle.store'), $this->validPayload())->assertSessionHas('error');
    }

    public function test_destroy_denied_without_delete_vehicle(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->delete(route('vehicle.destroy', $vehicle))->assertSessionHas('error');
    }

    // ── VehicleController::index ──────────────────────────────────────────────

    public function test_index_returns_200_for_authorized_user(): void
    {
        $this->actingAs($this->owner)->get(route('vehicle.index'))->assertOk();
    }

    public function test_index_renders_paginated_inertia_component(): void
    {
        Vehicle::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('vehicle.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicle/Index')
                ->where('vehicles.current_page', 1)
                ->has('vehicles.data')
                ->has('vehicles.last_page')
                ->has('vehicles.total')
            );
    }

    // ── VehicleController::store ──────────────────────────────────────────────

    public function test_store_creates_vehicle_and_redirects(): void
    {
        $this->actingAs($this->owner)
            ->post(route('vehicle.store'), $this->validPayload(['name' => 'Test Car']))
            ->assertRedirect(route('vehicle.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', ['name' => 'Test Car', 'parent_id' => $this->owner->id]);
    }

    public function test_store_flashes_error_on_missing_name(): void
    {
        $this->actingAs($this->owner)
            ->post(route('vehicle.store'), $this->validPayload(['name' => '']))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_store_defaults_available_for_rent_to_true_when_omitted(): void
    {
        $this->actingAs($this->owner)
            ->post(route('vehicle.store'), $this->validPayload(['name' => 'Default Availability']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', ['name' => 'Default Availability', 'available_for_rent' => 1]);
    }

    public function test_store_persists_available_for_rent_false(): void
    {
        $this->actingAs($this->owner)
            ->post(route('vehicle.store'), $this->validPayload(['name' => 'Unavailable Car', 'available_for_rent' => '0']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', ['name' => 'Unavailable Car', 'available_for_rent' => 0]);
    }

    // ── VehicleController::update ─────────────────────────────────────────────

    public function test_update_persists_changes(): void
    {
        $vehicle = Vehicle::factory()->create(['name' => 'Old Car', 'parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->put(route('vehicle.update', $vehicle), $this->validPayload(['name' => 'New Car']))
            ->assertRedirect(route('vehicle.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'name' => 'New Car']);
    }

    public function test_update_persists_available_for_rent_false(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id, 'available_for_rent' => true]);

        $this->actingAs($this->owner)
            ->put(route('vehicle.update', $vehicle), $this->validPayload(['available_for_rent' => '0']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'available_for_rent' => 0]);
    }

    public function test_update_leaves_availability_alone_when_the_field_is_absent(): void
    {
        // A vehicle is taken off the storefront deliberately -- sold, in the
        // workshop, reserved for the owner. An update that says nothing about
        // availability must not put it back on sale. The edit form always sends
        // the field, so this governs every other caller.
        $vehicle = Vehicle::factory()->create([
            'parent_id' => $this->owner->id,
            'available_for_rent' => false,
        ]);

        // validPayload() carries no availability field at all -- that absence
        // is the case under test.
        $this->actingAs($this->owner)
            ->put(route('vehicle.update', $vehicle), $this->validPayload())
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'available_for_rent' => 0]);
    }

    public function test_update_flashes_error_on_missing_name(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($this->owner)
            ->put(route('vehicle.update', $vehicle), [])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── IST-229: duplicate license-plate guard ───────────────────────────────

    public function test_store_rejects_duplicate_license_plate(): void
    {
        Vehicle::factory()->create(['parent_id' => $this->owner->id, 'license_plate' => 'AB-1234-CD']);

        // Same plate, different case + surrounding whitespace must still collide.
        $this->actingAs($this->owner)
            ->post(route('vehicle.store'), $this->validPayload(['license_plate' => '  ab-1234-cd  ']))
            ->assertRedirect()
            ->assertSessionHasErrors('license_plate');

        $this->assertSame(1, Vehicle::where('parent_id', $this->owner->id)->count());
    }

    public function test_store_allows_same_plate_for_a_different_tenant(): void
    {
        $other = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        Vehicle::factory()->create(['parent_id' => $other->id, 'license_plate' => 'AB-1234-CD']);

        // The plate is taken by another tenant — uniqueness is per-tenant, so allowed.
        $this->actingAs($this->owner)
            ->post(route('vehicle.store'), $this->validPayload(['license_plate' => 'AB-1234-CD']))
            ->assertRedirect(route('vehicle.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', ['parent_id' => $this->owner->id, 'license_plate' => 'AB-1234-CD']);
    }

    public function test_update_rejects_another_vehicles_plate_but_allows_keeping_own(): void
    {
        $a = Vehicle::factory()->create(['parent_id' => $this->owner->id, 'license_plate' => 'AA-111-AA']);
        $b = Vehicle::factory()->create(['parent_id' => $this->owner->id, 'license_plate' => 'BB-222-BB']);

        // Taking vehicle A's plate must fail and leave B unchanged.
        $this->actingAs($this->owner)
            ->put(route('vehicle.update', $b), $this->validPayload(['license_plate' => 'AA-111-AA']))
            ->assertRedirect()
            ->assertSessionHasErrors('license_plate');
        $this->assertDatabaseHas('vehicles', ['id' => $b->id, 'license_plate' => 'BB-222-BB']);

        // Keeping its own plate (with stray whitespace) must pass and be trimmed.
        $this->actingAs($this->owner)
            ->put(route('vehicle.update', $b), $this->validPayload(['license_plate' => '  BB-222-BB  ']))
            ->assertRedirect(route('vehicle.index'))
            ->assertSessionHas('success');
        $this->assertDatabaseHas('vehicles', ['id' => $b->id, 'license_plate' => 'BB-222-BB']);
    }

    public function test_store_rejects_plate_differing_only_by_non_breaking_space(): void
    {
        Vehicle::factory()->create(['parent_id' => $this->owner->id, 'license_plate' => 'AB-1234-CD']);

        // Same plate but with a leading non-breaking space (as pasted from Excel):
        // trim() wouldn't catch it; the normalized guard must.
        $this->actingAs($this->owner)
            ->post(route('vehicle.store'), $this->validPayload(['license_plate' => "\u{00a0}AB-1234-CD"]))
            ->assertRedirect()
            ->assertSessionHasErrors('license_plate');

        $this->assertSame(1, Vehicle::where('parent_id', $this->owner->id)->count());
    }

    public function test_store_normalizes_plate_stripping_unicode_whitespace(): void
    {
        $this->actingAs($this->owner)
            ->post(route('vehicle.store'), $this->validPayload(['license_plate' => "\u{00a0}XY-9999-ZZ "]))
            ->assertRedirect(route('vehicle.index'))
            ->assertSessionHas('success');

        // Stored value has the NBSP/whitespace stripped.
        $this->assertDatabaseHas('vehicles', ['parent_id' => $this->owner->id, 'license_plate' => 'XY-9999-ZZ']);
    }

    // ── VehicleController::destroy ────────────────────────────────────────────

    public function test_destroy_deletes_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->delete(route('vehicle.destroy', $vehicle))
            ->assertRedirect(route('vehicle.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('vehicles', ['id' => $vehicle->id]);
    }

    // ── VehicleController::create ─────────────────────────────────────────────

    public function test_create_requires_auth(): void
    {
        $this->get(route('vehicle.create'))->assertRedirect(route('login'));
    }

    public function test_create_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('vehicle.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicle/Create')
                ->has('types')
                ->has('gearbox')
                ->has('fuelType')
                ->has('option')
            );
    }

    // ── VehicleController::show ───────────────────────────────────────────────

    public function test_show_requires_auth(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $this->get(route('vehicle.show', $vehicle))->assertRedirect(route('login'));
    }

    public function test_show_renders_inertia_component(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('vehicle.show', $vehicle))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicle/Show')
                ->has('vehicle')
            );
    }

    // ── VehicleController::edit ───────────────────────────────────────────────

    public function test_edit_requires_auth(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $this->get(route('vehicle.edit', $vehicle))->assertRedirect(route('login'));
    }

    public function test_edit_renders_inertia_component(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('vehicle.edit', $vehicle))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicle/Edit')
                ->has('vehicle')
                ->has('types')
                ->has('gearbox')
                ->has('fuelType')
                ->has('option')
            );
    }

    // ── VehicleController::update — permission denied ─────────────────────────

    public function test_update_denied_without_edit_vehicle(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)
            ->put(route('vehicle.update', $vehicle), $this->validPayload())
            ->assertSessionHas('error');
    }

    // ── VehicleController::store — additional validations ────────────────────

    public function test_store_flashes_error_on_missing_daily_rate(): void
    {
        $this->actingAs($this->owner)
            ->post(route('vehicle.store'), $this->validPayload(['daily_rate' => '']))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_store_flashes_error_on_missing_license_plate(): void
    {
        $this->actingAs($this->owner)
            ->post(route('vehicle.store'), $this->validPayload(['license_plate' => '']))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── VehicleController::update — additional validations ───────────────────

    public function test_update_flashes_error_on_missing_daily_rate(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($this->owner)
            ->put(route('vehicle.update', $vehicle), $this->validPayload(['daily_rate' => '']))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_update_flashes_error_on_missing_license_plate(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($this->owner)
            ->put(route('vehicle.update', $vehicle), $this->validPayload(['license_plate' => '']))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── VehicleController::getVehicleRateCalculation ──────────────────────────

    public function test_vehicle_rate_calculation_requires_auth(): void
    {
        $this->get(route('vehicle.rate.calculation'))->assertRedirect(route('login'));
    }

    public function test_vehicle_rate_calculation_returns_json(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id, 'daily_rate' => 80]);

        $this->actingAs($this->owner)
            ->getJson(route('vehicle.rate.calculation', [
                'vahicle_id'      => $vehicle->id,
                'start_date_time' => now()->addDay()->format('Y/m/d') . ' 09:00',
                'end_date_time'   => now()->addDays(4)->format('Y/m/d') . ' 09:00',
                'daychange'       => 0,
            ]))
            ->assertOk();
    }

    public function test_vehicle_rate_calculation_with_daychange_returns_json(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id, 'daily_rate' => 80]);

        $this->actingAs($this->owner)
            ->getJson(route('vehicle.rate.calculation', [
                'vahicle_id'      => $vehicle->id,
                'start_date_time' => now()->addDay()->format('Y/m/d') . ' 09:00',
                'end_date_time'   => now()->addDays(4)->format('Y/m/d') . ' 09:00',
                'daychange'       => 1,
                'daily_price'     => 100,
            ]))
            ->assertOk();
    }

    // ── VehicleController::getAvailableVehicle ────────────────────────────────

    public function test_available_vehicle_requires_auth(): void
    {
        $this->get(route('available.vehicle'))->assertRedirect(route('login'));
    }

    public function test_available_vehicle_returns_json(): void
    {
        Vehicle::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->getJson(route('available.vehicle', [
                'start_date_time' => now()->addDays(10)->format('Y/m/d') . ' 09:00',
                'end_date_time'   => now()->addDays(13)->format('Y/m/d') . ' 09:00',
            ]))
            ->assertOk();
    }

    public function test_available_vehicle_with_malformed_dates_does_not_500(): void
    {
        // Regression for JAVASCRIPT-4: a malformed date string reaching
        // Carbon::createFromFormat('Y/m/d H:i', ...) used to throw an
        // unhandled InvalidFormatException and crash the request with a 500.
        Vehicle::factory()->create(['parent_id' => $this->owner->id]);

        $response = $this->actingAs($this->owner)
            ->getJson(route('available.vehicle', [
                'start_date_time' => '2026-06-20 09:00', // wrong separator + no padding match
                'end_date_time'   => 'not a date at all',
            ]));

        $response->assertOk();
        // Documented contract: degrade to an empty result rather than 500.
        $this->assertSame([], json_decode($response->getContent(), true));
    }

    public function test_available_vehicle_excludes_booked_vehicles(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);

        // Create an overlapping active booking for that vehicle.
        Booking::factory()->create([
            'vehicle'    => $vehicle->id,
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_date'   => now()->addDays(13)->format('Y-m-d'),
            'end_time'   => '18:00',
            'status'     => 'yet_to_start',
            'parent_id'  => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)
            ->getJson(route('available.vehicle', [
                'start_date_time' => now()->addDays(10)->format('Y/m/d') . ' 09:00',
                'end_date_time'   => now()->addDays(13)->format('Y/m/d') . ' 18:00',
            ]));

        $response->assertOk();
        $decoded = json_decode($response->getContent(), true);
        $this->assertArrayNotHasKey($vehicle->id, $decoded);
    }

    // ── VehicleController::store — with picture upload ────────────────────────

    public function test_store_with_picture_upload_saves_successfully(): void
    {
        \Illuminate\Support\Facades\Storage::fake();

        $picture = \Illuminate\Http\UploadedFile::fake()->image('car.jpg');

        $this->actingAs($this->owner)
            ->post(route('vehicle.store'), $this->validPayload(['picture' => $picture]))
            ->assertRedirect(route('vehicle.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', [
            'name'      => 'Renault Clio',
            'parent_id' => $this->owner->id,
        ]);
    }

    // ── VehicleController::update — with document upload ─────────────────────

    public function test_update_with_document_upload_saves_successfully(): void
    {
        \Illuminate\Support\Facades\Storage::fake();

        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $document = \Illuminate\Http\UploadedFile::fake()->create('reg.pdf', 100, 'application/pdf');

        $this->actingAs($this->owner)
            ->put(route('vehicle.update', $vehicle), $this->validPayload(['document' => $document]))
            ->assertRedirect(route('vehicle.index'))
            ->assertSessionHas('success');
    }

    // ── VehicleController::getAvailableVehicle — with booking_id exclusion ───

    public function test_available_vehicle_with_booking_id_exclusion_returns_json(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);

        $booking = Booking::factory()->create([
            'vehicle'    => $vehicle->id,
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_date'   => now()->addDays(13)->format('Y-m-d'),
            'end_time'   => '18:00',
            'status'     => 'yet_to_start',
            'parent_id'  => $this->owner->id,
        ]);

        // When booking_id is passed, that booking is excluded from the block check
        $response = $this->actingAs($this->owner)
            ->getJson(route('available.vehicle', [
                'start_date_time' => now()->addDays(10)->format('Y/m/d') . ' 09:00',
                'end_date_time'   => now()->addDays(13)->format('Y/m/d') . ' 18:00',
                'booking_id'      => $booking->id,
            ]));

        $response->assertOk();
        // Vehicle should appear because the conflicting booking is excluded
        $decoded = json_decode($response->getContent(), true);
        $this->assertArrayHasKey($vehicle->id, $decoded);
    }

    // ── VehicleController::getVehicleRateCalculation — with addons ───────────

    public function test_vehicle_rate_calculation_with_addons_returns_json(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id, 'daily_rate' => 80]);
        $addon   = \App\Models\Addon::factory()->create(['parent_id' => $this->owner->id, 'price' => 10]);

        $this->actingAs($this->owner)
            ->getJson(route('vehicle.rate.calculation', [
                'vahicle_id'      => $vehicle->id,
                'start_date_time' => now()->addDay()->format('Y/m/d') . ' 09:00',
                'end_date_time'   => now()->addDays(4)->format('Y/m/d') . ' 09:00',
                'daychange'       => 0,
                'addons'          => [$addon->id],
            ]))
            ->assertOk();
    }

    public function test_vehicle_rate_calculation_with_malformed_dates_does_not_500(): void
    {
        // Sibling of JAVASCRIPT-4: vehicleRateCalculation() does new DateTime()
        // on the dates, which throws on unparseable input. Must degrade, not 500.
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id, 'daily_rate' => 80]);

        $response = $this->actingAs($this->owner)
            ->getJson(route('vehicle.rate.calculation', [
                'vahicle_id'      => $vehicle->id,
                'start_date_time' => 'not a date at all',
                'end_date_time'   => 'also not a date',
                'daychange'       => 0,
            ]));

        $response->assertOk();
        $this->assertSame([], json_decode($response->getContent(), true));
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'type'                          => $this->vehicleType->id,
            'name'                          => 'Renault Clio',
            'model'                         => '2022 Clio',
            'engine_type'                   => 'V4',
            'engine_no'                     => 'ENG-1234AB',
            'license_plate'                 => 'AB-1234-CD',
            'registration_expiry_date'      => now()->addYear()->format('Y-m-d'),
            'daily_rate'                    => 50,
            'year_of_ﬁrst_immatriculation' => 2022,
            'gearbox'                       => 'manual',
            'fuel_type'                     => 'diesel',
            'number_of_seats'               => 5,
            'kilometers'                    => 10000,
        ], $overrides);
    }
}
