<?php

namespace Tests\Feature;

use App\Models\LoggedHistory;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected Role $employeeRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        $perms = [
            'manage user',
            'create user',
            'edit user',
            'delete user',
            'manage logged history',
            'delete logged history',
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo($perms);

        // store() (non-super-admin path) accesses this record unconditionally before
        // the null-guard, so it must exist for any store test to reach the DB write.
        // Built explicitly so the module is pinned to the one store() looks up.
        Notification::create([
            'module'        => 'user_create',
            'name'          => 'New User',
            'subject'       => 'Welcome',
            'message'       => 'Your account was created.',
            'short_code'    => '{company_name}',
            'enabled_email' => 0,
            'parent_id'     => $this->owner->id,
        ]);

        $this->employeeRole = Role::create([
            'name'       => 'employee',
            'guard_name' => 'web',
            'parent_id'  => $this->owner->id,
        ]);
    }

    // ── unauthenticated ───────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    public function test_store_requires_auth(): void
    {
        $this->post(route('users.store'))->assertRedirect(route('login'));
    }

    public function test_update_requires_auth(): void
    {
        $target = User::factory()->create(['parent_id' => $this->owner->id]);
        $this->put(route('users.update', $target))->assertRedirect(route('login'));
    }

    public function test_destroy_requires_auth(): void
    {
        $target = User::factory()->create(['parent_id' => $this->owner->id]);
        $this->delete(route('users.destroy', $target))->assertRedirect(route('login'));
    }

    public function test_logged_history_requires_auth(): void
    {
        $this->get(route('logged.history'))->assertRedirect(route('login'));
    }

    // ── permission denied ─────────────────────────────────────────────────────

    public function test_index_denied_without_manage_user(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->get(route('users.index'))->assertSessionHas('error');
    }

    public function test_store_denied_without_create_user(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->post(route('users.store'), [])->assertSessionHas('error');
    }

    public function test_update_denied_without_edit_user(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $target  = User::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->put(route('users.update', $target), [])->assertSessionHas('error');
    }

    public function test_destroy_denied_without_delete_user(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $target  = User::factory()->create(['parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->delete(route('users.destroy', $target))->assertSessionHas('error');
    }

    public function test_logged_history_denied_without_manage_logged_history(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $this->actingAs($noPerms)->get(route('logged.history'))->assertSessionHas('error');
    }

    // ── UserController::index ─────────────────────────────────────────────────

    public function test_index_returns_200_for_authorized_user(): void
    {
        $this->actingAs($this->owner)->get(route('users.index'))->assertOk();
    }

    // ── UserController::store ─────────────────────────────────────────────────

    public function test_store_creates_employee_and_redirects(): void
    {
        $this->actingAs($this->owner)
            ->post(route('users.store'), [
                'name'     => 'John Doe',
                'email'    => 'johndoe@test.com',
                'password' => 'password123',
                'role'     => $this->employeeRole->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['email' => 'johndoe@test.com']);
    }

    public function test_store_flashes_error_on_missing_name(): void
    {
        $this->actingAs($this->owner)
            ->post(route('users.store'), [
                'email'    => 'johndoe@test.com',
                'password' => 'password123',
                'role'     => $this->employeeRole->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── UserController::update ────────────────────────────────────────────────

    public function test_update_persists_changes(): void
    {
        $target = User::factory()->create(['name' => 'Old Name', 'parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->put(route('users.update', $target), [
                'name'  => 'New Name',
                'email' => $target->email,
                'role'  => $this->employeeRole->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'New Name']);
    }

    public function test_update_flashes_error_on_missing_name(): void
    {
        $target = User::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->put(route('users.update', $target), [
                'email' => $target->email,
                'role'  => $this->employeeRole->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── UserController::destroy ───────────────────────────────────────────────

    public function test_destroy_deletes_user(): void
    {
        $target = User::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->delete(route('users.destroy', $target))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    // ── UserController::loggedHistory ─────────────────────────────────────────

    public function test_logged_history_returns_200_for_authorized_user(): void
    {
        $this->actingAs($this->owner)->get(route('logged.history'))->assertOk();
    }

    // ── UserController::loggedHistoryShow ─────────────────────────────────────
    // NOTE: resources/views/logged_history/show.blade.php does not exist yet,
    // so only the auth guard is verifiable; the 200 path is untestable until the view lands.

    public function test_logged_history_show_requires_auth(): void
    {
        $history = LoggedHistory::factory()->create(['parent_id' => $this->owner->id]);
        $this->get(route('logged.history.show', $history->id))->assertRedirect(route('login'));
    }

    // ── UserController::loggedHistoryDestroy ──────────────────────────────────

    public function test_logged_history_destroy_requires_auth(): void
    {
        $history = LoggedHistory::factory()->create(['parent_id' => $this->owner->id]);
        $this->delete(route('logged.history.destroy', $history->id))->assertRedirect(route('login'));
    }

    public function test_logged_history_destroy_denied_without_delete_logged_history(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $history = LoggedHistory::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($noPerms)
            ->delete(route('logged.history.destroy', $history->id))
            ->assertSessionHas('error');
    }

    public function test_logged_history_destroy_deletes_record(): void
    {
        $history = LoggedHistory::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->delete(route('logged.history.destroy', $history->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('logged_histories', ['id' => $history->id]);
    }

    // ── UserController::create ────────────────────────────────────────────────

    public function test_create_requires_auth(): void
    {
        $this->get(route('users.create'))->assertRedirect(route('login'));
    }

    public function test_create_renders_inertia_component(): void
    {
        $this->actingAs($this->owner)
            ->get(route('users.create'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Users/Create')
                ->has('userRoles')
            );
    }

    // ── UserController::edit ──────────────────────────────────────────────────

    public function test_edit_requires_auth(): void
    {
        $target = User::factory()->create(['parent_id' => $this->owner->id]);
        $this->get(route('users.edit', $target))->assertRedirect(route('login'));
    }

    public function test_edit_renders_inertia_component(): void
    {
        $target = User::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->get(route('users.edit', $target))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Users/Edit')
                ->has('user.id')
                ->has('userRoles')
            );
    }

    // ── UserController::store — validation: duplicate email ───────────────────

    public function test_store_flashes_error_on_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@test.com', 'parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->post(route('users.store'), [
                'name'     => 'Duplicate User',
                'email'    => 'existing@test.com',
                'password' => 'password123',
                'role'     => $this->employeeRole->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── UserController::update — sets role ───────────────────────────────────

    public function test_update_assigns_new_role(): void
    {
        $target = User::factory()->create(['parent_id' => $this->owner->id]);
        $newRole = Role::create(['name' => 'manager', 'guard_name' => 'web', 'parent_id' => $this->owner->id]);

        $this->actingAs($this->owner)
            ->put(route('users.update', $target), [
                'name'  => $target->name,
                'email' => $target->email,
                'role'  => $newRole->id,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertTrue($target->fresh()->hasRole('manager'));
    }

    // ── UserController::loggedHistory (Blade) ─────────────────────────────────
    // NOTE: loggedHistory renders a Blade view. This test confirms the 200 status.
    public function test_logged_history_returns_view_for_authorized_user(): void
    {
        $this->actingAs($this->owner)
            ->get(route('logged.history'))
            ->assertOk();
    }

    // ── UserController::index — super admin lists owners ─────────────────────

    public function test_index_lists_owners_for_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);
        $superAdmin->givePermissionTo('manage user');

        User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $this->actingAs($superAdmin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (\Inertia\Testing\AssertableInertia $page) => $page
                ->component('Users/Index')
                ->has('users')
            );
    }

    // ── UserController::store — super admin creates owner ────────────────────

    public function test_store_creates_owner_as_super_admin_and_redirects(): void
    {
        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);
        $superAdmin->givePermissionTo('create user');

        // Ensure the 'owner' role exists
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);

        $this->actingAs($superAdmin)
            ->post(route('users.store'), [
                'name'     => 'New Owner',
                'email'    => 'newowner@test.com',
                'password' => 'password123',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'newowner@test.com',
            'type'  => 'owner',
        ]);
    }

    public function test_store_flashes_error_on_missing_name_as_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);
        $superAdmin->givePermissionTo('create user');

        $this->actingAs($superAdmin)
            ->post(route('users.store'), [
                'email'    => 'owner@test.com',
                'password' => 'password123',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_store_flashes_error_on_duplicate_email_as_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);
        $superAdmin->givePermissionTo('create user');

        User::factory()->create(['email' => 'taken@test.com']);

        $this->actingAs($superAdmin)
            ->post(route('users.store'), [
                'name'     => 'Owner',
                'email'    => 'taken@test.com',
                'password' => 'password123',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── UserController::update — super admin path ────────────────────────────

    public function test_update_persists_changes_as_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);
        $superAdmin->givePermissionTo('edit user');

        $target = User::factory()->create([
            'name'      => 'Old Name',
            'parent_id' => 0,
            'type'      => 'owner',
        ]);

        $this->actingAs($superAdmin)
            ->put(route('users.update', $target), [
                'name'  => 'Updated Name',
                'email' => $target->email,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'Updated Name']);
    }

    public function test_update_flashes_error_on_missing_name_as_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);
        $superAdmin->givePermissionTo('edit user');

        $target = User::factory()->create(['parent_id' => 0, 'type' => 'owner']);

        $this->actingAs($superAdmin)
            ->put(route('users.update', $target), [
                'email' => $target->email,
                // name missing
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}
