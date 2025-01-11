<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AddReminderPermissionsToSuperAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super admin')->first();
        
        if($superAdminRole) {
            $reminderPermissions = [
                'manage reminder',
                'create reminder',
                'edit reminder',
                'delete reminder'
            ];
            
            // Give permissions to super admin
            $superAdminRole->givePermissionTo($reminderPermissions);
        }
    
        
    }
}
