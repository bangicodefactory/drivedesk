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

    // ── permissions the caller cannot see (BAN-306, review finding 1) ─────────
    //
    // edit() sends `assignedPermissions` = every id on the role, and
    // Roles/Edit.jsx:25 seeds the form from it, but only the *offered* ids get
    // a checkbox. So a role carrying a permission the caller does not hold
    // submits that id back invisibly. Rejecting the whole submission over it
    // would make such a role permanently unsavable; silently dropping it would
    // strip a permission the caller was never shown. Neither is acceptable:
    // ids already on the role pass through untouched.

    public function test_update_keeps_a_permission_the_caller_cannot_see(): void
    {
        $role = $this->ownRole();
        $role->givePermissionTo([$this->permA, $this->permC]);   // permC: not held by the owner

        // Exactly what the form posts back: both ids, one of them unofferable.
        $this->actingAs($this->owner)
            ->put(route('role.update', $role), [
                'title'           => 'testrole',
                'user_permission' => [$this->permA->id, $this->permC->id],
            ])
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('success');

        $this->assertTrue($role->fresh()->hasPermissionTo($this->permA));
        $this->assertTrue($role->fresh()->hasPermissionTo($this->permC));
    }

    public function test_update_cannot_strip_a_permission_the_caller_cannot_see(): void
    {
        $role = $this->ownRole();
        $role->givePermissionTo([$this->permA, $this->permC]);

        // permC omitted entirely — it was never on the caller's screen, so its
        // absence is not a decision they made.
        $this->actingAs($this->owner)
            ->put(route('role.update', $role), [
                'title'           => 'testrole',
                'user_permission' => [$this->permB->id],
            ])
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('success');

        $this->assertTrue($role->fresh()->hasPermissionTo($this->permB));
        $this->assertFalse($role->fresh()->hasPermissionTo($this->permA));
        $this->assertTrue($role->fresh()->hasPermissionTo($this->permC));
    }

    // ── a role mutator may list roles (BAN-306, review finding 2) ─────────────
    //
    // store() and destroy() redirect to role.index. Gating index() on
    // `manage role` alone meant a caller holding only `create role` was bounced
    // there, and the success flash was consumed and replaced by the denial —
    // a denial message for an operation that had in fact succeeded.

    public function test_index_is_allowed_for_a_creator_without_manage_role(): void
    {
        $creator = User::factory()->create(['type' => 'user', 'parent_id' => $this->owner->id]);
        $creator->givePermissionTo('create role');

        $this->actingAs($creator)->get(route('role.index'))->assertOk();
    }

    public function test_store_success_survives_for_a_creator_without_manage_role(): void
    {
        $creator = User::factory()->create(['type' => 'user', 'parent_id' => $this->owner->id]);
        $creator->givePermissionTo(['create role', $this->permA->name]);

        $this->actingAs($creator)
            ->post(route('role.store'), [
                'title'           => 'Manager',
                'user_permission' => [$this->permA->id],
            ])
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('success');

        // The redirect target must not turn that success into a denial.
        $this->actingAs($creator)->get(route('role.index'))->assertOk();
    }

    // ── malformed input is not an escalation attempt (BAN-306, finding 4) ─────

    public function test_store_rejects_a_non_array_user_permission(): void
    {
        $response = $this->actingAs($this->owner)
            ->post(route('role.store'), [
                'title'           => 'Scalar',
                'user_permission' => $this->permA->id,   // not an array
            ])
            ->assertRedirect();

        // A validation failure, not "Permission Denied." — the message a caller
        // and an audit trail see must say which of the two actually happened.
        $this->assertNotSame('Permission Denied.', session('error'));
        $this->assertDatabaseMissing('roles', ['name' => 'Scalar']);
    }

    // ── BAN-310: reserved role names ────────────────
    //
    // UserController sets a user's `type` verbatim from the chosen role's name,
    // so a role name is a privilege string. Refusing to assign such a role
    // (BAN-307) is the backstop; refusing to create one is the fix.

    public function test_store_rejects_a_role_named_owner(): void
    {
        $this->actingAs($this->owner)
            ->post(route('role.store'), [
                'title'           => 'owner',
                'user_permission' => [$this->permA->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('roles', ['name' => 'owner', 'parent_id' => $this->owner->id]);
    }

    /**
     * `type == 'owner'` is an exact comparison everywhere, so 'Owner' would not
     * escalate today -- but that is an accident of the comparisons, not a
     * decision, and the roles list an admin audits reads the same either way.
     */
    public function test_store_rejects_a_reserved_name_in_any_case_or_padding(): void
    {
        $this->actingAs($this->owner)
            ->post(route('role.store'), [
                'title'           => '  Super Admin ',
                'user_permission' => [$this->permA->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('roles', ['parent_id' => $this->owner->id, 'name' => '  Super Admin ']);
    }

    /**
     * The seeded `owner` role carries parent_id = the super admin's id
     * (DefaultDataUsersTableSeeder), which is exactly what parentId() returns
     * for a super admin -- so index() lists it and edit() resolves it, and
     * Roles/Edit.jsx always resubmits `title`. Rejecting the whole request on a
     * reserved name locked the super admin out of editing the one role every
     * tenant owner is assigned. The guard is about renaming *to* a reserved
     * name, not about touching a role that already has one.
     */
    public function test_update_allows_saving_a_role_that_already_has_a_reserved_name(): void
    {
        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $superAdmin->givePermissionTo(['edit role', $this->permA->name, $this->permB->name]);

        $ownerRole = Role::create([
            'name'       => 'owner',
            'guard_name' => 'web',
            'parent_id'  => $superAdmin->id,
        ]);
        $ownerRole->givePermissionTo($this->permA);

        $this->actingAs($superAdmin)
            ->put(route('role.update', $ownerRole), [
                'title'           => 'owner',      // unchanged, as the form posts it
                'user_permission' => [$this->permB->id],
            ])
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('success');

        $this->assertTrue($ownerRole->fresh()->hasPermissionTo($this->permB));
    }

    public function test_store_still_accepts_an_ordinary_role_name(): void
    {
        $this->actingAs($this->owner)
            ->post(route('role.store'), [
                'title'           => 'Ownership Clerk',
                'user_permission' => [$this->permA->id],
            ])
            ->assertRedirect(route('role.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['name' => 'Ownership Clerk', 'parent_id' => $this->owner->id]);
    }

    /**
     * update() cannot rename a role today -- fill() drops `title` because it is
     * not a column on `roles`. The guard is here so that fixing that does not
     * silently open a rename path to a reserved name.
     */
    public function test_update_rejects_renaming_to_a_reserved_name(): void
    {
        $role = $this->ownRole();
        $role->givePermissionTo($this->permA);

        $this->actingAs($this->owner)
            ->put(route('role.update', $role), [
                'title'           => 'super admin',
                'user_permission' => [$this->permA->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('testrole', $role->fresh()->name);
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
