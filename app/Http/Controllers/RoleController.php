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
        if (! \Auth::user()->can('manage role')) {
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
                'user_permission' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->route('role.index')->with('error', $messages->first());
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
                'user_permission' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->route('role.index')->with('error', $messages->first());
        }

        $permissions = $this->resolveRequestedPermissions($request->user_permission);
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
     * `getAllPermissions()` rather than iterating `roles` as the screens do —
     * it covers permissions granted to the user directly as well as through a
     * role, so it is a superset of what the form offers and never rejects a
     * legitimate submission.
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
     * Map requested permission ids onto the caller's own permissions.
     *
     * Returns null if any id is one the caller does not hold, or is not a
     * permission at all — the previous `Permission::find($id)` returned null
     * for an unknown id and passed it straight to givePermissionTo(). Rejecting
     * the whole submission rather than silently dropping the bad ids keeps the
     * saved role identical to what the caller was shown.
     */
    private function resolveRequestedPermissions($requested): ?Collection
    {
        if (! is_array($requested)) {
            return null;
        }

        $assignable = $this->assignablePermissions()->keyBy('id');

        $resolved = new Collection();
        foreach ($requested as $id) {
            $permission = $assignable->get((int) $id);
            if ($permission === null) {
                return null;
            }
            $resolved->push($permission);
        }

        return $resolved;
    }
}
