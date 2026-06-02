<?php

namespace Tests\Feature;

use App\Models\InspectionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * BAN-63: verify the ported InspectionType GET pages render the correct Inertia
 * component with the expected prop names when INERTIA_ENABLED=true.
 */
class InertiaInspectionTypeTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');

        foreach (['manage inspection type', 'create inspection type', 'edit inspection type', 'delete inspection type'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(['manage inspection type', 'create inspection type', 'edit inspection type', 'delete inspection type']);
    }

    public function test_index_renders_inertia_component_with_types_prop(): void
    {
        InspectionType::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('inspection-type.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('InspectionType/Index')
                ->has('types', 1)
            );
    }

    public function test_create_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('inspection-type.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('InspectionType/Create')
            );
    }

    public function test_edit_renders_inertia_component_with_type_prop(): void
    {
        $type = InspectionType::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('inspection-type.edit', $type->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('InspectionType/Edit')
                ->has('inspectionType')
            );
    }
}
