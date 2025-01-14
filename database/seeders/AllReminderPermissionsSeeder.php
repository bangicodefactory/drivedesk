<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class AllReminderPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // First check if permissions already exist
        $reminderPermissions = [
            ['name' => 'manage reminder', 'guard_name' => 'web'],
            ['name' => 'create reminder', 'guard_name' => 'web'],
            ['name' => 'edit reminder', 'guard_name' => 'web'],
            ['name' => 'delete reminder', 'guard_name' => 'web'],
        ];

        // Create permissions if they don't exist
        foreach ($reminderPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission['name'],
                'guard_name' => $permission['guard_name']
            ]);
        }

        // Get permission names for assignment
        $permissionNames = array_column($reminderPermissions, 'name');

        // Assign to super admin
        $superAdminRole = Role::where('name', 'super admin')->first();
        if($superAdminRole) {
            $superAdminRole->givePermissionTo($permissionNames);
        }

        // Assign to owner
        $ownerRole = Role::where('name', 'owner')->first();
        if($ownerRole) {
            $ownerRole->givePermissionTo($permissionNames);
        }

        // Assign to manager
        $managerRole = Role::where('name', 'manager')->first();
        if($managerRole) {
            $managerRole->givePermissionTo($permissionNames);
        }
    }
    
}
