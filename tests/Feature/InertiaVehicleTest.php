<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InertiaVehicleTest extends TestCase
{
    use RefreshDatabase;

    private function actor(array $permissions = []): User
    {
        return $this->makeUserWithPermissions($permissions, 'owner');
    }

    /** @test */
    public function index_renders_vehicle_index_component_with_vehicles(): void
    {
        $user = $this->actor(['manage vehicle']);
        $this->makeVehicleFor($user);

        $this->actingAs($user)->get(route('vehicle.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicle/Index')
                ->has('vehicles', 1)
                ->has('vehicles.0.daily_rate_formatted')
            );
    }

    /** @test */
    public function create_renders_vehicle_create_component_with_select_options(): void
    {
        $user = $this->actor(['create vehicle']);

        $this->actingAs($user)->get(route('vehicle.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicle/Create')
                ->has('types')
                ->has('gearbox')
                ->has('fuelType')
                ->has('option')
            );
    }

    /** @test */
    public function show_renders_vehicle_show_component_with_vehicle(): void
    {
        $user = $this->actor(['manage vehicle']);
        $vehicle = $this->makeVehicleFor($user);

        $this->actingAs($user)->get(route('vehicle.show', $vehicle->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicle/Show')
                ->where('vehicle.id', $vehicle->id)
                ->has('vehicle.daily_rate_formatted')
            );
    }

    /** @test */
    public function edit_renders_vehicle_edit_component_with_vehicle_and_options(): void
    {
        $user = $this->actor(['edit vehicle']);
        $vehicle = $this->makeVehicleFor($user);

        $this->actingAs($user)->get(route('vehicle.edit', $vehicle->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Vehicle/Edit')
                ->where('vehicle.id', $vehicle->id)
                ->has('types')
                ->has('gearbox')
                ->has('fuelType')
                ->has('option')
            );
    }

    private function makeVehicleFor(User $user): Vehicle
    {
        return Vehicle::create([
            'vehicle_id' => 1,
            'parent_id' => $user->parent_id ?? $user->id,
            'type' => 'sedan',
            'name' => 'Inertia Car',
            'model' => 'Model I',
            'engine_type' => 'V8',
            'engine_no' => 'EN777',
            'license_plate' => 'INE-777',
            'registration_expiry_date' => '2030-01-01',
            'daily_rate' => 99,
            'gearbox' => 'automatic',
            'fuel_type' => 'diesel',
            'number_of_seats' => 5,
            'kilometers' => 1000,
        ]);
    }
}
