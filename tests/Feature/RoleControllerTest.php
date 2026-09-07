<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected User $outsider;
    protected User $otherOwner;
    protected Permission $permA;
    protected Permission $permB;
    protected Permission $permC;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        $this->permA = Permission::firstOrCreate(['name' => 'perm alpha', 'guard_name' => 'web']);
        $this->permB = Permission::firstOrCreate(['name' => 'perm beta',  'guard_name' => 'web']);
        // Held by nobody in this suite: the target of every escalation attempt.
        $this->permC = Permission::firstOrCreate(['name' => 'perm gamma', 'guard_name' => 'web']);

        foreach (['manage role', 'create role', 'edit role', 'delete role'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo([
            'manage role', 'create role', 'edit role', 'delete role',
            $this->permA->name, $this->permB->name,
        ]);

        // Same tenant, no role permissions at all.
        $this->outsider = User::factory()->create(['type' => 'user', 'parent_id' => $this->owner->id]);

        // A second tenant, to prove role lookups are scoped.
        $this->otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
    }

    /** A role owned by the second tenant — never reachable from $this->owner. */
    private function foreignRole(): Role
    {
        return Role::create([
            'name'       => 'foreignrole',
            'guard_name' => 'web',
            'parent_id'  => $this->otherOwner->id,
        ]);
    }

    private function ownRole(string $name = 'testrole'): Role
    {
        return Role::create([
            'name'       => $name,
            'guard_name' => 'web',
            'parent_id'  => $this->owner->id,
        ]);
    }

    // ── unauthenticated ───────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('role.index'))->assertRedirect(route('login'));
    }

    public function test_store_requires_auth(): void
    {
        $this->post(route('role.store'))->assertRedirect(route('login'));
    }

    public function test_update_requires_auth(): void
    {
        $this->put(route('role.update', $this->ownRole()))->assertRedirect(route('login'));
    }

    public function test_destroy_requires_auth(): void
    {
        $this->delete(route('role.destroy', $this->ownRole()))->assertRedirect(route('login'));
    }

    // ── authorization (BAN-306) ───────────────────────────────────────────────
    //
    // Every action was behind `auth` + `XSS` only, with no can() check anywhere
    // in the controller, while the four role permissions were seeded and never
    // read. The suite previously carried a NOTE recording that as intended.

    public function test_index_is_denied_without_manage_role(): void
    {
        $this->actingAs($this->outsider)->get(route('role.index'))->assertRedirect();
    }

    public function test_create_is_denied_without_create_role(): void
    {
        $this->actingAs($this->outsider)->get(route('role.create'))->assertRedirect();
    }

    public function test_store_is_denied_without_create_role(): void
    {
        $this->actingAs($this->outsider)
            ->post(route('role.store'), [
                'title'           => 'Manager',
                'user_permission' => [$this->permA->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('roles', ['name' => 'Manager']);
    }

    public function test_edit_is_denied_without_edit_role(): void
    {
        $this->actingAs($this->outsider)
            ->get(route('role.edit', $this->ownRole()))
            ->assertRedirect();
    }

    public function test_update_is_denied_without_edit_role(): void
    {
        $role = $this->ownRole();
        $role->givePermissionTo($this->permA);

        $this->actingAs($this->outsider)
            ->put(route('role.update', $role), [
                'title'           => 'testrole',
                'user_permission' => [$this->permB->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertTrue($role->fresh()->hasPermissionTo($this->permA));
    }

    public function test_destroy_is_denied_without_delete_role(): void
    {
        $role = $this->ownRole();

        $this->actingAs($this->outsider)
            ->delete(route('role.destroy', $role))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    /**
     * The denial must land before validation. Both paths flash `error`, so the
     * message itself is the only thing that separates them — a caller without
     * the permission must not be able to map the rules from the response.
     */
    public function test_update_denies_before_it_validates(): void
    {
        $role = $this->ownRole();

        $this->actingAs($this->outsider)
            ->put(route('role.update', $role), [])   // omits both required fields
            ->assertRedirect()
            ->assertSessionHas('error', 'Permission Denied.');
    }

    // ── privilege escalation (BAN-306) ────────────────────────────────────────
    //
    // store/update looped over the raw `user_permission` ids and called
    // Permission::find() on each with no check that the caller held it. The
    // create/edit screens only *offer* the caller's own permissions, which is
    // presentation, not authorization — a crafted POST granted anything.

    public function test_store_rejects_a_permission_the_caller_does_not_hold(): void
    {
        $this->actingAs($this->owner)
            ->post(route('role.store'), [
                'title'           => 'Escalated',
                'user_permission' => [$this->permA->id, $this->permC->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('roles', ['name' => 'Escalated']);
    }

    public function test_update_rejects_a_permission_the_caller_does_not_hold(): void
    {
        $role = $this->ownRole();
        $role->givePermissionTo($this->permA);

        $this->actingAs($this->owner)
            ->put(route('role.update', $role), [
                'title'           => 'testrole',
                'user_permission' => [$this->permC->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertFalse($role->fresh()->hasPermissionTo($this->permC));
        $this->assertTrue($role->fresh()->hasPermissionTo($this->permA));
    }

    /**
     * An unknown id is the same class of input as an unheld one — it must not
     * fall through to givePermissionTo(null).
     */
    public function test_store_rejects_an_unknown_permission_id(): void
    {
        $this->actingAs($this->owner)
            ->post(route('role.store'), [
                'title'           => 'Bogus',
                'user_permission' => [999999],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('roles', ['name' => 'Bogus']);
    }

    // ── tenant scoping (BAN-306) ──────────────────────────────────────────────
    //
    // edit/update/destroy resolved the role with a bare Role::find($id), so an
    // id from another tenant was editable and deletable even though index()
    // has always scoped its list to parent_id = parentId().

    public function test_edit_does_not_reach_another_tenants_role(): void
    {
        $this->actingAs($this->owner)
            ->get(route('role.edit', $this->foreignRole()))
            ->assertNotFound();
    }

    public function test_update_does_not_reach_another_tenants_role(): void
    {
        $role = $this->foreignRole();

        $this->actingAs($this->owner)
            ->put(route('role.update', $role), [
                'title'           => 'hijacked',
                'user_permission' => [$this->permA->id],
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'foreignrole']);
    }

    public function test_destroy_does_not_reach_another_tenants_role(): void
    {
        $role = $this->foreignRole();

        $this->actingAs($this->owner)
            ->delete(route('role.destroy', $role))
            ->assertNotFound();

        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    public function test_destroy_of_an_unknown_role_is_not_found(): void
    {
        $this->actingAs($this->owner)
            ->delete(route('role.destroy', 999999))
            ->assertNotFound();
    }

    // ── RoleController::index ─────────────────────────────────────────────────

    public function test_index_returns_200_for_authenticated_user(): void
    {
        $this->actingAs($this->owner)->get(route('role.index'))->assertOk();
    }

    // ── RoleController::store ─────────────────────────────────────────────────

    public function test_store_creates_role_and_redirects(): void
    {
        $this->actingAs($this->owner)
            ->post(route('role.store'), [
                'title'           => 'Manager',
                'user_permission' => [$this->permA->id, $this->permB->id],
            ])
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['name' => 'Manager', 'parent_id' => $this->owner->id]);
    }

    public function test_store_flashes_error_on_missing_title(): void
    {
        $this->actingAs($this->owner)
            ->post(route('role.store'), [
                'user_permission' => [$this->permA->id],
            ])
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('error');
    }

    public function test_store_flashes_error_on_missing_user_permission(): void
    {
        $this->actingAs($this->owner)
            ->post(route('role.store'), ['title' => 'Manager'])
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('error');
    }

    // ── RoleController::update ────────────────────────────────────────────────

    public function test_update_reassigns_permissions_and_redirects(): void
    {
        $role = $this->ownRole();
        $role->givePermissionTo($this->permA);

        $this->actingAs($this->owner)
            ->put(route('role.update', $role), [
                'title'           => 'testrole',
                'user_permission' => [$this->permB->id],
            ])
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('success');

        $this->assertTrue($role->fresh()->hasPermissionTo($this->permB));
        $this->assertFalse($role->fresh()->hasPermissionTo($this->permA));
    }

    public function test_update_flashes_error_on_missing_user_permission(): void
    {
        $role = $this->ownRole();

        $this->actingAs($this->owner)
            ->put(route('role.update', $role), ['title' => 'testrole'])
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('error');
    }

    // ── RoleController::destroy ───────────────────────────────────────────────

    public function test_destroy_deletes_role(): void
    {
        $role = $this->ownRole();

        $this->actingAs($this->owner)
            ->delete(route('role.destroy', $role))
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}
