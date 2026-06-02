<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * BAN-61: verify the four ported Vehicle GET pages render the correct Inertia
 * component with the expected prop names when INERTIA_ENABLED=true.
 */
class InertiaVehicleTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected VehicleType $vehicleType;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');
        config(['app.inertia_enabled' => true]);

        foreach (['manage vehicle', 'create vehicle', 'edit vehicle', 'delete vehicle'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(['manage vehicle', 'create vehicle', 'edit vehicle', 'delete vehicle']);

        $this->vehicleType = VehicleType::factory()->create(['parent_id' => $this->owner->id]);
    }

    public function test_index_renders_vehicle_index_component_with_vehicles(): void
    {
        Vehicle::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('vehicle.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicle/Index')
                ->where('vehicles.current_page', 1)
                ->has('vehicles.data', 1)
                ->has('vehicles.data.0.daily_rate_formatted')
            );
    }

    public function test_create_renders_vehicle_create_component_with_select_options(): void
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

    public function test_show_renders_vehicle_show_component_with_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('vehicle.show', $vehicle->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicle/Show')
                ->where('vehicle.id', $vehicle->id)
                ->has('vehicle.daily_rate_formatted')
            );
    }

    public function test_edit_renders_vehicle_edit_component_with_vehicle_and_options(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('vehicle.edit', $vehicle->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicle/Edit')
                ->where('vehicle.id', $vehicle->id)
                ->has('types')
                ->has('gearbox')
                ->has('fuelType')
                ->has('option')
            );
    }
}
