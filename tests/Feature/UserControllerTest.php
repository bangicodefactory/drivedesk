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

    /**
     * BAN-317: this route renders resources/views/logged_history/index.blade.php,
     * which extends layouts.app, which @includes admin.menu -- and line 5 of that
     * menu called \App\Models\Subscription::find() on a model BAN-199 deleted,
     * behind `feature('subscriptions')`, which was TRUE for drivedesk. A hard 500.
     *
     * The suite could not see it, because asClient('acme') set
     * subscriptions => false and short-circuited before the missing class -- the
     * trap CLAUDE.md 10.2.6 describes. BAN-317 forced the flag to expose it;
     * BAN-318 then retired the flag outright, so there is no longer a switch to
     * force. What remains worth guarding is that the page renders at all.
     */
    public function test_logged_history_renders(): void
    {
        $this->actingAs($this->owner)
            ->get(route('logged.history'))
            ->assertOk();
    }

    /**
     * The pricing permissions were the *other* half of the dead menu: they gated
     * links to subscriptions.index, subscription.transaction, coupons.index and
     * coupons.history, four routes that do not exist.
     *
     * They never actually threw, and an earlier version of this docblock said
     * they did. The Subscription::find() call sat in the @php block at the top of
     * admin/menu.blade.php, so it killed the request before any menu markup was
     * evaluated -- the route() calls further down were unreachable.
     *
     * What this user now pins is the leftover: `manage pricing packages` and
     * `manage pricing transation` still gated the "System Settings" heading whose
     * only pricing entries were removed, so a role holding just those rendered a
     * section header with nothing under it.
     */
    public function test_a_pricing_only_role_does_not_get_an_empty_settings_heading(): void
    {
        foreach (['manage logged history', 'manage pricing packages', 'manage pricing transation'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $superAdmin->givePermissionTo(['manage logged history', 'manage pricing packages', 'manage pricing transation']);

        $this->actingAs($superAdmin)
            ->get(route('logged.history'))
            ->assertOk()
            // Nothing in the section is reachable for this role any more.
            ->assertDontSee('System Settings');
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

    // ── BAN-308: the by-id lookups are tenant-scoped ──────────────────
    //
    // index() and loggedHistory() have always scoped their lists, but every
    // lookup that takes an id off the URL resolved against the whole table.

    public function test_destroy_does_not_reach_a_user_outside_the_tenant(): void
    {
        $foreign = User::factory()->create(['type' => 'employee', 'parent_id' => 999]);

        $this->actingAs($this->owner)
            ->delete(route('users.destroy', $foreign))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $foreign->id]);
    }

    /** Previously a null dereference, not a 404. */
    public function test_destroy_of_an_unknown_user_is_not_found(): void
    {
        $this->actingAs($this->owner)
            ->delete(route('users.destroy', 999999))
            ->assertNotFound();
    }

    public function test_edit_does_not_reach_a_user_outside_the_tenant(): void
    {
        $foreign = User::factory()->create(['type' => 'employee', 'parent_id' => 999]);

        $this->actingAs($this->owner)
            ->get(route('users.edit', $foreign))
            ->assertNotFound();
    }

    public function test_logged_history_destroy_does_not_reach_another_tenants_record(): void
    {
        $foreign = LoggedHistory::factory()->create(['parent_id' => 999]);

        $this->actingAs($this->owner)
            ->delete(route('logged.history.destroy', $foreign->id))
            ->assertNotFound();

        $this->assertDatabaseHas('logged_histories', ['id' => $foreign->id]);
    }

    // ── BAN-308 review: the gaps the first pass left ────────────

    /**
     * edit() had no can() check at all -- the resource route carries only
     * `auth` + `XSS`. Tenant-scoping it narrowed who could be read without
     * addressing who could read.
     */
    public function test_edit_is_denied_without_edit_user(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $target  = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($noPerms)
            ->get(route('users.edit', $target))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    /**
     * The super-admin exemption was justified by two update() tests but applied
     * to the whole helper, so destroy() -- this PR's headline bug -- stayed open
     * for them. Their own index() lists only owners they created, so nothing is
     * lost by scoping it.
     */
    public function test_destroy_does_not_reach_another_tenants_user_as_super_admin(): void
    {
        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $superAdmin->givePermissionTo('delete user');

        $staff = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($superAdmin)
            ->delete(route('users.destroy', $staff))
            ->assertNotFound();

        $this->assertDatabaseHas('users', ['id' => $staff->id]);
    }

    public function test_edit_does_not_reach_another_tenants_user_as_super_admin(): void
    {
        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $superAdmin->givePermissionTo('edit user');

        $staff = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($superAdmin)
            ->get(route('users.edit', $staff))
            ->assertNotFound();
    }

    /** Support keeps what it actually needs: the owner it created. */
    public function test_super_admin_can_still_edit_an_owner_it_created(): void
    {
        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $superAdmin->givePermissionTo('edit user');

        $ownedOwner = User::factory()->create(['type' => 'owner', 'parent_id' => $superAdmin->id]);

        $this->actingAs($superAdmin)
            ->get(route('users.edit', $ownedOwner))
            ->assertOk();
    }

    // ── BAN-312: a support login is visible to the customer ────────────
    //
    // parentId() returns a super admin their *own* id, which is no tenant's
    // key, so their activity-log row could never appear on the customer's own
    // screen. In a product where the vendor holds a login to every customer's
    // deployment, that is the one audit trail that has to be airtight.

    public function test_activity_log_key_is_the_owner_for_a_super_admin(): void
    {
        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);

        $this->actingAs($superAdmin);

        $this->assertSame($this->owner->id, activityLogParentId());
        $this->assertNotSame((int) $superAdmin->id, activityLogParentId());
    }

    public function test_activity_log_key_is_the_owner_for_staff_and_for_the_owner(): void
    {
        $staff = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($staff);
        $this->assertSame($this->owner->id, activityLogParentId());

        $this->actingAs($this->owner);
        $this->assertSame($this->owner->id, activityLogParentId());
    }

    /** Before install there is no owner; the key must not blow up. */
    public function test_activity_log_key_falls_back_when_no_owner_exists(): void
    {
        $this->owner->forceDelete();

        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $this->actingAs($superAdmin);

        $this->assertSame((int) $superAdmin->id, activityLogParentId());
    }

    /**
     * One-owner-per-deployment is enforced going forward, but nothing repaired
     * deployments that predate it -- and DefaultDataUsersTableSeeder creates
     * owner@gmail.com at install regardless. Picking the first `type = 'owner'`
     * row on such a deployment would point the activity log at the wrong tenant:
     * the real owner's existing rows vanish from their own screen, and their
     * staff read and delete rows keyed to somebody else -- the cross-tenant
     * audit access BAN-308 closed one PR earlier. Two owners means the
     * deployment key is not knowable, so behave exactly as before.
     */
    public function test_activity_log_key_falls_back_when_two_owners_exist(): void
    {
        $secondOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $this->actingAs($secondOwner);
        $this->assertSame((int) $secondOwner->id, activityLogParentId());

        $this->actingAs($this->owner);
        $this->assertSame($this->owner->id, activityLogParentId());

        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $this->actingAs($superAdmin);
        $this->assertSame((int) $superAdmin->id, activityLogParentId());
    }

    /**
     * The owner is reachable from users.destroy: their parent_id is the super
     * admin's id, which is what parentId() returns for a super admin, and
     * index() lists exactly those rows with a delete control. Deleting them
     * leaves the deployment with no owner -- every activity-log row keyed to
     * them becomes unreadable and new ones are orphaned again.
     */
    public function test_destroy_refuses_to_delete_the_deployments_only_owner(): void
    {
        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $superAdmin->givePermissionTo('delete user');

        $soleOwner = User::factory()->create(['type' => 'owner', 'parent_id' => $superAdmin->id]);
        $this->owner->forceDelete();     // leave exactly one owner

        $this->actingAs($superAdmin)
            ->delete(route('users.destroy', $soleOwner))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $soleOwner->id]);
    }

    public function test_destroy_still_deletes_an_ordinary_user(): void
    {
        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $superAdmin->givePermissionTo('delete user');

        $staff = User::factory()->create(['type' => 'employee', 'parent_id' => $superAdmin->id]);

        $this->actingAs($superAdmin)
            ->delete(route('users.destroy', $staff))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_the_owner_sees_a_row_written_during_a_support_login(): void
    {
        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);

        // What userLoggedHistory() now writes while support is acting.
        $this->actingAs($superAdmin);
        $supportRow = LoggedHistory::factory()->create([
            'user_id'   => $superAdmin->id,
            'parent_id' => activityLogParentId(),
        ]);

        $this->actingAs($this->owner)
            ->get(route('logged.history'))
            ->assertOk()
            ->assertSee($superAdmin->name);

        $this->assertSame($this->owner->id, (int) $supportRow->fresh()->parent_id);
    }

    /**
     * BAN-315 excludes user and role rows on purpose. An owner's parent_id is
     * the super admin who created them -- that is what makes the owner
     * resolvable from users.index -- so routing this through the new helper
     * would have pointed the first owner at themselves.
     */
    public function test_owner_creation_still_stamps_the_creating_super_admin(): void
    {
        $this->owner->forceDelete();

        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $superAdmin->givePermissionTo('create user');
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);

        $this->actingAs($superAdmin)
            ->post(route('users.store'), [
                'name'     => 'First Owner',
                'email'    => 'first@test.com',
                'password' => 'password123',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email'     => 'first@test.com',
            'type'      => 'owner',
            'parent_id' => $superAdmin->id,
        ]);
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

    /**
     * BAN-307. This previously asserted the opposite: setUp already creates an
     * owner, so the test was proving a super admin could add a second one. That
     * is the behaviour being removed -- DriveDesk ships one deployment per
     * business owner, and a second owner is a second tenant living inside one
     * customer's database.
     */
    public function test_store_refuses_a_second_owner_as_super_admin(): void
    {
        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);
        $superAdmin->givePermissionTo('create user');
        Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);

        $this->actingAs($superAdmin)
            ->post(route('users.store'), [
                'name'     => 'New Owner',
                'email'    => 'newowner@test.com',
                'password' => 'password123',
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('users', ['email' => 'newowner@test.com']);
    }

    /** The first owner is still creatable -- this is an invariant, not a ban. */
    public function test_store_creates_the_first_owner_as_super_admin(): void
    {
        // setUp's owner is the only one; a deployment before install has none.
        $this->owner->forceDelete();

        $superAdmin = User::factory()->create([
            'type'      => 'super admin',
            'parent_id' => 0,
        ]);
        $superAdmin->givePermissionTo('create user');
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

    /**
     * The staff branch took `Role::findById($request->role)` unscoped while
     * create() only offers this tenant's roles, so a crafted id set the new
     * user's type to anything at all -- 'owner' included.
     */
    public function test_store_refuses_a_role_id_from_outside_the_tenant(): void
    {
        // The seeded 'owner' role belongs to the super admin, not to this owner.
        $foreignOwnerRole = Role::create([
            'name'       => 'owner',
            'guard_name' => 'web',
            'parent_id'  => 999,
        ]);

        $this->actingAs($this->owner)
            ->post(route('users.store'), [
                'name'     => 'Sneaky Owner',
                'email'    => 'sneaky@test.com',
                'password' => 'password123',
                'role'     => $foreignOwnerRole->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@test.com']);
    }

    /**
     * `type` and `parent_id` are fillable and the super-admin branch filled from
     * $request->all(), so an existing user could be promoted to owner or moved
     * to another tenant. Neither field is on the edit form.
     */
    // ── BAN-307 review: update() is the fifth owner-creating path ────────────────────
    //
    // store() was fixed; update()'s staff branch was not. Both of its lookups
    // were unscoped, and `type` is set verbatim from the chosen role's name.

    public function test_update_refuses_a_role_id_from_outside_the_tenant(): void
    {
        $target = User::factory()->create([
            'type'      => 'employee',
            'parent_id' => $this->owner->id,
        ]);

        // The seeded 'owner' role belongs to the super admin, so edit()'s picker
        // never offers it -- but Role::findById() found it anyway.
        $foreignOwnerRole = Role::create([
            'name'       => 'owner',
            'guard_name' => 'web',
            'parent_id'  => 999,
        ]);

        $this->actingAs($this->owner)
            ->put(route('users.update', $target), [
                'name'  => 'Promoted',
                'email' => $target->email,
                'role'  => $foreignOwnerRole->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('employee', $target->fresh()->type);
    }

    /**
     * `super admin` is the stronger escape: BelongsToTenant::tenantScopeApplies()
     * returns false for that type, so a user carrying it drops the tenant scope
     * on every model in the app. Role names are user-supplied -- RoleController
     * validates uniqueness, not content -- so a tenant-scoped role named
     * `super admin` passes every scoping check.
     */
    public function test_update_refuses_a_role_named_super_admin(): void
    {
        $target = User::factory()->create([
            'type'      => 'employee',
            'parent_id' => $this->owner->id,
        ]);

        $trojan = Role::create([
            'name'       => 'super admin',
            'guard_name' => 'web',
            'parent_id'  => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->put(route('users.update', $target), [
                'name'  => 'Escalated',
                'email' => $target->email,
                'role'  => $trojan->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('employee', $target->fresh()->type);
    }

    public function test_store_refuses_a_role_named_super_admin(): void
    {
        $trojan = Role::create([
            'name'       => 'super admin',
            'guard_name' => 'web',
            'parent_id'  => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->post(route('users.store'), [
                'name'     => 'Trojan',
                'email'    => 'trojan@test.com',
                'password' => 'password123',
                'role'     => $trojan->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('users', ['email' => 'trojan@test.com']);
    }

    /**
     * BAN-310: the assignment backstop is case-insensitive too. A role named
     * 'Owner' predating the RoleController guard must still not be assignable.
     */
    public function test_store_refuses_a_reserved_role_name_in_any_case(): void
    {
        $trojan = Role::create([
            'name'       => 'Owner',
            'guard_name' => 'web',
            'parent_id'  => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->post(route('users.store'), [
                'name'     => 'Cased Trojan',
                'email'    => 'cased@test.com',
                'password' => 'password123',
                'role'     => $trojan->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('users', ['email' => 'cased@test.com']);
    }

    public function test_update_does_not_reach_a_user_outside_the_tenant(): void
    {
        $foreign = User::factory()->create([
            'name'      => 'Foreign User',
            'type'      => 'employee',
            'parent_id' => 999,
        ]);

        $this->actingAs($this->owner)
            ->put(route('users.update', $foreign), [
                'name'  => 'Hijacked',
                'email' => $foreign->email,
                'role'  => $this->employeeRole->id,
            ])
            ->assertNotFound();

        $this->assertSame('Foreign User', $foreign->fresh()->name);
    }

    /**
     * `password` is fillable and User has no `hashed` cast, so a crafted
     * super-admin update wrote it to the column in plaintext and locked the
     * account out. The edit form never posts it.
     */
    public function test_update_ignores_a_password_in_the_request_as_super_admin(): void
    {
        $target = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $originalHash = $target->password;

        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $superAdmin->givePermissionTo('edit user');

        $this->actingAs($superAdmin)
            ->put(route('users.update', $target), [
                'name'     => 'Renamed',
                'email'    => $target->email,
                'password' => 'plaintext-secret',
            ])
            ->assertRedirect(route('users.index'));

        $target->refresh();
        $this->assertSame('Renamed', $target->name);
        $this->assertSame($originalHash, $target->password);
    }

    public function test_update_cannot_promote_a_user_to_owner(): void
    {
        $employee = User::factory()->create([
            'type'      => 'employee',
            'parent_id' => $this->owner->id,
        ]);

        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $superAdmin->givePermissionTo('edit user');

        $this->actingAs($superAdmin)
            ->put(route('users.update', $employee), [
                'name'      => 'Promoted',
                'email'     => $employee->email,
                'type'      => 'owner',
                'parent_id' => 0,
            ])
            ->assertRedirect(route('users.index'));

        $employee->refresh();
        $this->assertSame('Promoted', $employee->name);
        $this->assertSame('employee', $employee->type);
        $this->assertSame($this->owner->id, (int) $employee->parent_id);
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
