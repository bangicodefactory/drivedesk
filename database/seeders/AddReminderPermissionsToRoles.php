<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AddReminderPermissionsToRoles extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ownerRole = Role::where('name', 'owner')->first();
    if($ownerRole) {
        $ownerRole->givePermissionTo([
            'manage reminder',
            'create reminder',
            'edit reminder',
            'delete reminder'
        ]);
    }
    }
}
