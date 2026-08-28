<?php

namespace Tests\Feature;

use App\Models\Option;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * BAN-61: verify the ported Option GET pages render the correct Inertia
 * component with the expected prop names when INERTIA_ENABLED=true.
 *
 * Routes under test (web.php): option.index, option.create, option.edit
 * (Route::resource('option', OptionController::class), auth + XSS middleware).
 * index additionally checks the 'manage options' permission inside the
 * controller. show() renders no view and store/update/destroy redirect, so
 * only the three GET view methods are ported.
 */
class InertiaOptionTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        foreach (['manage options', 'create options', 'edit options', 'delete options'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(['manage options', 'create options', 'edit options', 'delete options']);
    }

    private function makeOption(string $name = 'GPS'): Option
    {
        $option = new Option();
        $option->name = $name;
        $option->parent_id = $this->owner->id;
        $option->save();
        return $option;
    }

    public function test_index_renders_option_index_component_with_options(): void
    {
        $this->makeOption('GPS');
        $this->makeOption('Child Seat');

        $this->actingAs($this->owner)
            ->get(route('option.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Option/Index')
                ->has('options', 2)
                ->where('options.0.name', 'GPS')
            );
    }

    public function test_create_renders_option_create_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('option.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Option/Create'));
    }

    public function test_edit_renders_option_edit_component_with_option(): void
    {
        $option = $this->makeOption();

        $this->actingAs($this->owner)
            ->get(route('option.edit', $option->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Option/Edit')
                ->where('option.id', $option->id)
                ->where('option.name', $option->name)
            );
    }

    public function test_store_persists_option_and_redirects_with_success(): void
    {
        $this->actingAs($this->owner)
            ->post(route('option.store'), ['name' => 'New Option'])
            ->assertRedirect(route('option.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('options', [
            'name' => 'New Option',
            'parent_id' => $this->owner->id,
        ]);
    }

    public function test_store_without_name_redirects_back_with_error(): void
    {
        $this->actingAs($this->owner)
            ->from(route('option.index'))
            ->post(route('option.store'), ['name' => ''])
            ->assertRedirect(route('option.index'))
            ->assertSessionHas('error');
    }

    public function test_update_persists_option_and_redirects_with_success(): void
    {
        $option = $this->makeOption('Old');

        $this->actingAs($this->owner)
            ->put(route('option.update', $option->id), ['name' => 'Updated'])
            ->assertRedirect(route('option.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('options', [
            'id' => $option->id,
            'name' => 'Updated',
        ]);
    }
}
