<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * BAN-60: verify the 9 ported Settings + user management pages render the
 * correct Inertia component when INERTIA_ENABLED=true.
 */
class InertiaSettingsAndUsersTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');

        foreach (['manage user', 'manage role', 'manage setting'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(['manage user', 'manage role', 'manage setting']);
    }

    public function test_settings_account_renders_account_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('setting.account'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Settings/Account')
                ->where('loginUser.name',  $this->owner->name)
                ->where('loginUser.email', $this->owner->email)
            );
    }

    public function test_settings_password_renders_password_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('setting.password'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Settings/Password'));
    }

    public function test_users_index_renders_users_index_with_payload(): void
    {
        User::factory()->create([
            'parent_id' => $this->owner->id,
            'type'      => 'employee',
            'name'      => 'Bob Builder',
        ]);

        $this->actingAs($this->owner)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Index')
                ->has('users')
                ->where('users.0.name', 'Bob Builder')
            );
    }

    public function test_users_create_renders_users_create_with_roles(): void
    {
        Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web', 'parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('users.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Create')
                ->has('userRoles')
            );
    }

    public function test_users_edit_renders_users_edit_with_user_and_roles(): void
    {
        $editable = User::factory()->create([
            'parent_id' => $this->owner->id,
            'type'      => 'employee',
        ]);

        $this->actingAs($this->owner)
            ->get(route('users.edit', $editable->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Users/Edit')
                ->where('user.id', $editable->id)
                ->has('userRoles')
            );
    }

    public function test_roles_index_renders_roles_index(): void
    {
        Role::firstOrCreate(['name' => 'foreman', 'guard_name' => 'web', 'parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('role.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Roles/Index')
                ->has('roles')
            );
    }

    public function test_roles_create_renders_roles_create(): void
    {
        $this->actingAs($this->owner)
            ->get(route('role.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Roles/Create')
                ->has('permissions')
            );
    }

    public function test_roles_edit_renders_roles_edit(): void
    {
        $role = Role::firstOrCreate(['name' => 'crew', 'guard_name' => 'web', 'parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('role.edit', $role->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Roles/Edit')
                ->where('role.id', $role->id)
                ->has('assignedPermissions')
            );
    }

    public function test_permissions_index_renders_permissions_index(): void
    {
        $this->actingAs($this->owner)
            ->get(route('permission.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Permissions/Index')
                ->has('permissions')
            );
    }
}
