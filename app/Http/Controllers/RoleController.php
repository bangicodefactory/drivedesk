<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;


/**
 * BAN-306. Every action here was reachable by any authenticated user: the
 * route is behind `auth` + `XSS` only (routes/web.php:202) and the controller
 * held no can() check, while `manage/create/edit/delete role` were seeded and
 * never read. Because this controller hands out permissions, that one gap
 * granted every other one — a staff user could POST role.update and give their
 * own role `delete booking`, `manage tva`, `create user`.
 *
 * Three guards below, in the order an attacker meets them:
 *
 *  1. the permission the action has always been named for;
 *  2. `assignablePermissions()` — you may only grant what you already hold.
 *     The create/edit screens already show that set, but a screen is
 *     presentation; the ids arrive in the request and were trusted;
 *  3. `findInTenant()` — edit/update/destroy took a bare `Role::find($id)`,
 *     so another tenant's role was editable and deletable even though
 *     index() has always scoped its list to `parent_id = parentId()`.
 */
class RoleController extends Controller
{

    public function index()
    {
        // Any of the four, not `manage role` alone: store() and destroy()
        // redirect here, so gating it narrower than they are gated meant a
        // caller holding only `create role` was bounced on arrival and their
        // success flash was consumed and replaced by this denial — a denial for
        // an operation that had in fact succeeded. If you may change a role you
        // may see the list of them; the nav link stays behind `manage role`.
        if (! \Auth::user()->canAny(['manage role', 'create role', 'edit role', 'delete role'])) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $roleData = Role::where('parent_id', parentId())
            ->whereNotIn('name', ['client', 'driver'])
            ->withCount('permissions')
            ->get();

        return Inertia::render('Roles/Index', [
            'roles' => $roleData->map(fn ($r) => [
                'id'                => $r->id,
                'name'              => $r->name,
                'permissions_count' => $r->permissions_count,
            ])->values()->all(),
        ]);
    }


    public function create()
    {
        if (! \Auth::user()->can('create role')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        return Inertia::render('Roles/Create', [
            'permissions' => $this->offeredPermissions(),
        ]);
    }


    public function store(Request $request)
    {
        if (! \Auth::user()->can('create role')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $validator = \Validator::make(
            $request->all(), [
                'title' => 'required|unique:roles,name,null,id,parent_id,' . parentId(),
                'user_permission' => 'required|array',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->route('role.index')->with('error', $messages->first());
        }

        // BAN-310: a role name becomes a user's `type` verbatim, so creating
        // one called `owner` or `super admin` is how a tenant mints a privilege
        // level for itself. UserController refuses to assign such a role; this
        // stops the string existing at all.
        if (isReservedRoleName($request->title)) {
            return redirect()->back()->with('error', __('That role name is reserved.'));
        }

        $permissions = $this->resolveRequestedPermissions($request->user_permission);
        if ($permissions === null) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $userRole = new Role();
        $userRole->name = $request->title;
        $userRole->parent_id = parentId();
        $userRole->save();
        foreach ($permissions as $permission) {
            $userRole->givePermissionTo($permission);
        }
        return redirect()->route('role.index')->with('success', __('Role successfully created.'));

    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        if (! \Auth::user()->can('edit role')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $role = $this->findInTenant($id);

        $assignPermission = $role->permissions->pluck('id')->toArray();

        return Inertia::render('Roles/Edit', [
            'role' => [
                'id'   => $role->id,
                'name' => $role->name,
            ],
            'permissions' => $this->offeredPermissions(),
            'assignedPermissions' => $assignPermission,
        ]);
    }


    public function update(Request $request, $id)
    {
        if (! \Auth::user()->can('edit role')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $userRole = $this->findInTenant($id);

        $validator = \Validator::make(
            $request->all(), [
                'title' => 'required|unique:roles,name,' . $userRole->id . ',id,parent_id,' . parentId(),
                'user_permission' => 'required|array',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->route('role.index')->with('error', $messages->first());
        }

        // BAN-310. Note that update() cannot actually rename a role today:
        // fill() drops `title` because it is not a column on `roles` (the
        // pre-existing oddity BAN-306 deferred). This guard is here so that
        // fixing that does not silently open a rename path to a reserved name.
        if (isReservedRoleName($request->title)) {
            return redirect()->back()->with('error', __('That role name is reserved.'));
        }

        $permissions = $this->resolveRequestedPermissions($request->user_permission, $userRole);
        if ($permissions === null) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $permissionData = $request->except(['permissions']);
        $userRole->fill($permissionData)->save();

        $permissionList = Permission::all();
        foreach ($permissionList as $revokePermission) {
            $userRole->revokePermissionTo($revokePermission);
        }
        foreach ($permissions as $assignPermission) {
            $userRole->givePermissionTo($assignPermission);
        }
        return redirect()->route('role.index')->with('success', __('Role successfully updated.'));

    }


    public function destroy($id)
    {
        if (! \Auth::user()->can('delete role')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $userRole = $this->findInTenant($id);
        $userRole->delete();
        return redirect()->route('role.index')->with('success', 'Role successfully deleted.');
    }

    /**
     * Resolve a role id within the caller's tenant, matching the scope
     * index() has always applied to its list. A bare `Role::find($id)` let an
     * id from another tenant through; it also returned null for an unknown id
     * and then fataled on `->delete()`. 404 covers both, and does not tell the
     * caller whether the id exists elsewhere.
     */
    private function findInTenant($id): Role
    {
        $role = Role::where('parent_id', parentId())->find($id);

        abort_if($role === null, 404);

        return $role;
    }

    /**
     * The permissions the caller may hand out: their own.
     *
     * `getAllPermissions()` rather than iterating `roles` as the screens did —
     * it covers permissions granted to the user directly as well as through a
     * role, so it is a superset of what that walk produced.
     *
     * It is *not* the whole set a submission may contain: a role can already
     * hold permissions its editor does not, and those are not the caller's to
     * grant or to strip. resolveRequestedPermissions() carries them through.
     */
    private function assignablePermissions(): Collection
    {
        return \Auth::user()->getAllPermissions();
    }

    /** The same set, shaped for the Create/Edit screens. */
    private function offeredPermissions(): array
    {
        return $this->assignablePermissions()
            ->unique('id')
            ->map(fn ($p) => [
                'id'   => $p->id,
                'name' => $p->name,
            ])->values()->all();
    }

    /**
     * Map requested permission ids onto the set the caller may hand out.
     *
     * Returns null if any id is one the caller may not grant, or is not a
     * permission at all — the previous `Permission::find($id)` returned null
     * for an unknown id and passed it straight to givePermissionTo(). Rejecting
     * the whole submission rather than silently dropping the bad ids keeps the
     * saved role identical to what the caller was shown.
     *
     * `$role` carries the permissions it already holds. Those the caller cannot
     * see are neither theirs to grant nor theirs to strip, so they pass through
     * untouched — accepted when the form posts them back, and re-added when it
     * does not. Without that, a role holding one permission its editor lacks
     * could not be saved at all: edit() sends every assigned id and
     * Roles/Edit.jsx seeds the form from it, but only the offered ids get a
     * checkbox, so the rest ride along invisibly. Filtering them out of
     * `assignedPermissions` instead would have silently revoked them on save.
     */
    private function resolveRequestedPermissions($requested, ?Role $role = null): ?Collection
    {
        if (! is_array($requested)) {
            return null;
        }

        $assignable = $this->assignablePermissions()->keyBy('id');

        $retained = $role
            ? $role->permissions->reject(fn ($p) => $assignable->has($p->id))->keyBy('id')
            : new Collection();

        $resolved = new Collection();
        foreach ($requested as $id) {
            $id = (int) $id;
            $permission = $assignable->get($id) ?? $retained->get($id);
            if ($permission === null) {
                return null;
            }
            $resolved->push($permission);
        }

        foreach ($retained as $permission) {
            if (! $resolved->contains(fn ($p) => $p->id === $permission->id)) {
                $resolved->push($permission);
            }
        }

        return $resolved;
    }
}
