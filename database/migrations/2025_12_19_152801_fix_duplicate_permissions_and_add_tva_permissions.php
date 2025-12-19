<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Remove duplicate reminder permissions (IDs 103-106)
        // These are the duplicates with timestamps
        DB::table('permissions')->whereIn('id', [103, 104, 105, 106])->delete();

        // 2. Ensure TVA permissions exist (they should from seeder, but let's be safe)
        $tvaPermissions = ['manage tva', 'manage tva report'];
        foreach ($tvaPermissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
        }

        // 3. Assign TVA permissions to all 'owner' roles
        $ownerRoles = Role::where('name', 'owner')->get();
        foreach ($ownerRoles as $role) {
            $role->givePermissionTo($tvaPermissions);
        }

        // 4. Assign TVA permissions to all 'manager' roles
        $managerRoles = Role::where('name', 'manager')->get();
        foreach ($managerRoles as $role) {
            $role->givePermissionTo($tvaPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove TVA permissions from all roles
        $tvaPermissions = ['manage tva', 'manage tva report'];
        
        $roles = Role::whereIn('name', ['owner', 'manager'])->get();
        foreach ($roles as $role) {
            $role->revokePermissionTo($tvaPermissions);
        }
    }
};
