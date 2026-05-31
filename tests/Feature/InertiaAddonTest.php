<?php

namespace Tests\Feature;

use App\Models\Addon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * BAN-61: verify the ported Addon GET pages render the correct Inertia
 * component with the expected prop names when INERTIA_ENABLED=true.
 *
 * Routes under test (web.php): addon.index, addon.create, addon.edit
 * (Route::resource('addon', AddonController::class), auth + XSS middleware).
 * index additionally checks the 'manage addon' permission inside the
 * controller. show() renders no view and store/update/destroy redirect, so
 * only the three GET view methods are ported. The two JSON endpoints
 * (addon.rate.calculation, addon.rate.reduction) are preserved unchanged and
 * are not Inertia pages.
 */
class InertiaAddonTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');
        config(['app.inertia_enabled' => true]);

        foreach (['manage addon', 'create addon', 'edit addon', 'delete addon'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(['manage addon', 'create addon', 'edit addon', 'delete addon']);
    }

    private function makeAddon(string $name = 'Child Seat', $price = 15, string $billingType = 'daily'): Addon
    {
        $addon = new Addon();
        $addon->name = $name;
        $addon->price = $price;
        $addon->billing_type = $billingType;
        $addon->parent_id = $this->owner->id;
        $addon->save();
        return $addon;
    }

    public function test_index_renders_addon_index_component_with_addons(): void
    {
        $this->makeAddon('Child Seat');
        $this->makeAddon('GPS', 9, 'total');

        $this->actingAs($this->owner)
            ->get(route('addon.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Addon/Index')
                ->has('addons', 2)
                ->where('addons.0.name', 'Child Seat')
                ->has('addons.0.price_formatted')
            );
    }

    public function test_create_renders_addon_create_component_with_billing_types(): void
    {
        $this->actingAs($this->owner)
            ->get(route('addon.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Addon/Create')
                ->has('billingType')
                ->where('billingType.daily', 'Daily')
            );
    }

    public function test_edit_renders_addon_edit_component_with_addon_and_billing_types(): void
    {
        $addon = $this->makeAddon();

        $this->actingAs($this->owner)
            ->get(route('addon.edit', $addon->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Addon/Edit')
                ->where('addon.id', $addon->id)
                ->where('addon.name', $addon->name)
                ->has('billingType')
            );
    }

    public function test_store_persists_addon_and_redirects_with_success(): void
    {
        $this->actingAs($this->owner)
            ->post(route('addon.store'), [
                'name' => 'New Addon',
                'price' => 20,
                'billing_type' => 'daily',
            ])
            ->assertRedirect(route('addon.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('addons', [
            'name' => 'New Addon',
            'billing_type' => 'daily',
            'parent_id' => $this->owner->id,
        ]);
    }

    public function test_store_without_name_redirects_back_with_error(): void
    {
        $this->actingAs($this->owner)
            ->from(route('addon.index'))
            ->post(route('addon.store'), ['name' => '', 'price' => '', 'billing_type' => ''])
            ->assertRedirect(route('addon.index'))
            ->assertSessionHas('error');
    }

    public function test_update_persists_addon_and_redirects_with_success(): void
    {
        $addon = $this->makeAddon('Old', 10, 'daily');

        $this->actingAs($this->owner)
            ->put(route('addon.update', $addon->id), [
                'name' => 'Updated',
                'price' => 30,
                'billing_type' => 'total',
            ])
            ->assertRedirect(route('addon.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('addons', [
            'id' => $addon->id,
            'name' => 'Updated',
            'billing_type' => 'total',
        ]);
    }

    public function test_destroy_deletes_addon_and_redirects_with_success(): void
    {
        $addon = $this->makeAddon();

        $this->actingAs($this->owner)
            ->delete(route('addon.destroy', $addon->id))
            ->assertRedirect(route('addon.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('addons', ['id' => $addon->id]);
    }
}
