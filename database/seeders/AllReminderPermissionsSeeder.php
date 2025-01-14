<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AllReminderPermissionsSeeder extends Seeder
{
    public function run()
    {
        try {
            // Create permissions first
            $permissions = [
                'manage reminder',
                'create reminder',
                'edit reminder',
                'delete reminder'
            ];

            // Explicitly create each permission
            foreach ($permissions as $permissionName) {
                Permission::create([
                    'name' => $permissionName,
                    'guard_name' => 'web'
                ]);
            }

            // Now assign permissions to roles
            $roles = ['super admin', 'owner', 'manager'];
            
            foreach ($roles as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    foreach ($permissions as $permission) {
                        try {
                            if (!$role->hasPermissionTo($permission)) {
                                $role->givePermissionTo($permission);
                            }
                        } catch (\Exception $e) {
                            echo "Error assigning '$permission' to role '$roleName': " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
            
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}