<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AllReminderPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionNames = ['manage reminder', 'create reminder', 'edit reminder', 'delete reminder'];

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $ownerRole = Role::where('name', 'owner')->first();
        $superadminRole = Role::where('name', 'super admin')->first();

        foreach ($permissionNames as $name) {
            $permission = Permission::where('name', $name)->where('guard_name', 'web')->first();
            if (!$permission) {
                continue;
            }
            DB::table('role_has_permissions')->insertOrIgnore([
                ['role_id' => $ownerRole->id, 'permission_id' => $permission->id],
                ['role_id' => $superadminRole->id, 'permission_id' => $permission->id],
            ]);
        }
    }
}
