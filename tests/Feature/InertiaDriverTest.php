<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * BAN-61: verify the ported Driver GET pages render the correct Inertia
 * component with the expected prop names when INERTIA_ENABLED=true.
 *
 * Routes under test (web.php): driver.index, driver.create, driver.show,
 * driver.edit, and the non-resource driver.new.create -> newCreate (which
 * renders the same Driver/Create component as driver.create). All sit behind
 * auth + XSS middleware; index additionally checks the 'manage driver'
 * permission inside the controller.
 */
class InertiaDriverTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');
        config(['app.inertia_enabled' => true]);

        foreach (['manage driver', 'create driver', 'edit driver', 'delete driver', 'show driver'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(['manage driver', 'create driver', 'edit driver', 'delete driver', 'show driver']);
    }

    private function makeDriverUser(): User
    {
        $user = User::factory()->create([
            'type' => 'driver',
            'parent_id' => $this->owner->id,
            'name' => 'John Doe',
        ]);

        $driver = new Driver();
        $driver->forceFill([
            'driver_id' => 1,
            'user_id' => $user->id,
            'gender' => 'Male',
            'parent_id' => $this->owner->id,
            'license_number' => 'LIC-1',
            'issue_date' => '2024-01-01',
            'expiration_date' => '2030-01-01',
            'birth_date' => '1990-01-01',
        ])->save();

        return $user->fresh();
    }

    public function test_index_renders_driver_index_component_with_drivers(): void
    {
        $this->makeDriverUser();

        $this->actingAs($this->owner)
            ->get(route('driver.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Driver/Index')
                ->has('drivers', 1)
                ->has('drivers.0.driver_id_display')
            );
    }

    public function test_create_renders_driver_create_component_with_gender(): void
    {
        $this->actingAs($this->owner)
            ->get(route('driver.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Driver/Create')
                ->has('gender')
            );
    }

    public function test_new_create_renders_driver_create_component_with_gender(): void
    {
        $this->actingAs($this->owner)
            ->get(route('driver.new.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Driver/Create')
                ->has('gender')
            );
    }

    public function test_show_renders_driver_show_component_with_driver_and_user(): void
    {
        $user = $this->makeDriverUser();

        $this->actingAs($this->owner)
            ->get(route('driver.show', $user->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Driver/Show')
                ->where('user.id', $user->id)
                ->where('user.first_name', 'John')
                ->where('user.last_name', 'Doe')
                ->has('driver.driver_id_display')
            );
    }

    public function test_edit_renders_driver_edit_component_with_driver_user_and_gender(): void
    {
        $user = $this->makeDriverUser();

        $this->actingAs($this->owner)
            ->get(route('driver.edit', $user->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Driver/Edit')
                ->where('user.id', $user->id)
                ->where('user.first_name', 'John')
                ->has('driver')
                ->has('gender')
            );
    }
}
