<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * BAN-61: verify the ported Place GET pages render the correct Inertia component
 * with the expected prop names when INERTIA_ENABLED=true.
 */
class InertiaPlaceTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        foreach (['manage place', 'create place', 'edit place', 'delete place'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(['manage place', 'create place', 'edit place', 'delete place']);
    }

    public function test_index_renders_place_index_component_with_places(): void
    {
        Place::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('place.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Place/Index')
                ->has('places', 1)
                ->has('places.0.price_formatted')
            );
    }

    public function test_create_renders_place_create_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('place.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Place/Create')
            );
    }

    public function test_edit_renders_place_edit_component_with_place(): void
    {
        $place = Place::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('place.edit', $place->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Place/Edit')
                ->where('place.id', $place->id)
                ->has('place.name')
            );
    }
}
