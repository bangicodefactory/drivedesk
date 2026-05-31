<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * BAN-61: verify the three ported VehicleType GET pages render the correct
 * Inertia component with the expected prop names when INERTIA_ENABLED=true.
 */
class InertiaVehicleTypeTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');
        config(['app.inertia_enabled' => true]);

        foreach (['manage vehicle type', 'create vehicle type', 'edit vehicle type', 'delete vehicle type'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(['manage vehicle type', 'create vehicle type', 'edit vehicle type', 'delete vehicle type']);
    }

    public function test_index_renders_vehicle_type_index_component_with_types(): void
    {
        VehicleType::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('vehicle-type.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('VehicleType/Index')
                ->has('types', 1)
                ->has('types.0.type')
            );
    }

    public function test_create_renders_vehicle_type_create_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('vehicle-type.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('VehicleType/Create')
            );
    }

    public function test_edit_renders_vehicle_type_edit_component_with_vehicle_type(): void
    {
        $vehicleType = VehicleType::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('vehicle-type.edit', $vehicleType->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('VehicleType/Edit')
                ->where('vehicleType.id', $vehicleType->id)
                ->has('vehicleType.type')
            );
    }
}
