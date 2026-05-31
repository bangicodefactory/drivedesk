<?php

namespace Tests\Feature;

use App\Models\Inspection;
use App\Models\InspectionType;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * BAN-63: verify the ported Inspection GET pages render the correct Inertia
 * component with the expected prop names when INERTIA_ENABLED=true.
 */
class InertiaInspectionTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');
        config(['app.inertia_enabled' => true]);

        foreach (['manage inspection', 'create inspection', 'edit inspection', 'show inspection', 'delete inspection'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(['manage inspection', 'create inspection', 'edit inspection', 'show inspection', 'delete inspection']);

        $this->vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
    }

    private function makeInspection(array $overrides = []): Inspection
    {
        return Inspection::create(array_merge([
            'vehicle'                => $this->vehicle->id,
            'inspector'              => 'Jane',
            'inspection_date'        => now()->format('Y-m-d'),
            'meter_reading_incoming' => 0,
            'incoming_date'          => now()->format('Y-m-d'),
            'status'                 => 'pending',
            'repair_status'          => 'pending',
            'amount'                 => 0,
            'parent_id'              => $this->owner->id,
        ], $overrides));
    }

    public function test_index_renders_inertia_component_with_inspections_prop(): void
    {
        $this->makeInspection();

        $this->actingAs($this->owner)
            ->get(route('inspection.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inspection/Index')
                ->has('inspections', 1)
                ->has('inspections.0.id_encrypted')
            );
    }

    public function test_create_renders_inertia_component_with_props(): void
    {
        InspectionType::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('inspection.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inspection/Create')
                ->has('vehicles')
                ->has('status')
                ->has('repairStatus')
                ->has('types')
            );
    }

    public function test_edit_renders_inertia_component_with_props(): void
    {
        $inspection = $this->makeInspection();

        $this->actingAs($this->owner)
            ->get(route('inspection.edit', Crypt::encrypt($inspection->id)))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inspection/Edit')
                ->has('inspection')
                ->has('vehicles')
                ->has('status')
                ->has('repairStatus')
                ->has('types')
                ->has('details')
            );
    }

    public function test_show_renders_inertia_component_with_props(): void
    {
        $inspection = $this->makeInspection();

        $this->actingAs($this->owner)
            ->get(route('inspection.show', Crypt::encrypt($inspection->id)))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inspection/Show')
                ->has('inspection')
                ->has('details')
            );
    }
}
