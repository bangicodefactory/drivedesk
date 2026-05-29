<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;


class RoleController extends Controller
{

    public function index()
    {
        $roleData = Role::where('parent_id', parentId())
            ->whereNotIn('name', ['client', 'driver'])
            ->withCount('permissions')
            ->get();

        if (config('app.inertia_enabled')) {
            return Inertia::render('Roles/Index', [
                'roles' => $roleData->map(fn ($r) => [
                    'id'                => $r->id,
                    'name'              => $r->name,
                    'permissions_count' => $r->permissions_count,
                ])->values()->all(),
            ]);
        }

        return view('role.index', compact('roleData'));
    }


    public function create()
    {
        $permissionList = new Collection();
        foreach (\Auth::user()->roles as $role) {
            $permissionList = $permissionList->merge($role->permissions);
        }

        if (config('app.inertia_enabled')) {
            return Inertia::render('Roles/Create', [
                'permissions' => $permissionList->unique('id')->map(fn ($p) => [
                    'id'   => $p->id,
                    'name' => $p->name,
                ])->values()->all(),
            ]);
        }

        return view('role.create', compact('permissionList'));
    }


    public function store(Request $request)
    {

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

        $userRole = new Role();
        $userRole->name = $request->title;
        $userRole->parent_id = parentId();
        $userRole->save();
        foreach ($request->user_permission as $permission) {
            $result = Permission::find($permission);
            $userRole->givePermissionTo($result);
        }
        return redirect()->route('role.index')->with('success', __('Role successfully created.'));

    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        $role = Role::find($id);
        $permissionList = new Collection();
        foreach (\Auth::user()->roles as $userRole) {
            $permissionList = $permissionList->merge($userRole->permissions);
        }

        $assignPermission = $role->permissions;
        $assignPermission = $assignPermission->pluck('id')->toArray();

        if (config('app.inertia_enabled')) {
            return Inertia::render('Roles/Edit', [
                'role' => [
                    'id'   => $role->id,
                    'name' => $role->name,
                ],
                'permissions' => $permissionList->unique('id')->map(fn ($p) => [
                    'id'   => $p->id,
                    'name' => $p->name,
                ])->values()->all(),
                'assignedPermissions' => $assignPermission,
            ]);
        }

        return view('role.edit', compact('role', 'permissionList', 'assignPermission'));
    }


    public function update(Request $request, $id)
    {
        $userRole = Role::find($id);
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
        $permissionData = $request->except(['permissions']);
        $assignPermissions = $request->user_permission;
        $userRole->fill($permissionData)->save();

        $permissionList = Permission::all();
        foreach ($permissionList as $revokePermission) {
            $userRole->revokePermissionTo($revokePermission);
        }
        foreach ($assignPermissions as $assignPermission) {
            $assign = Permission::find($assignPermission);
            $userRole->givePermissionTo($assign);
        }
        return redirect()->route('role.index')->with('success', __('Role successfully updated.'));

    }


    public function destroy($id)
    {
        $userRole = Role::find($id);
        $userRole->delete();
        return redirect()->route('role.index')->with('success', 'Role successfully deleted.');
    }

}
