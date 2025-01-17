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
        try {
            // Define new permissions
            $newPermissions = [
                [
                    'name' => 'manage reminder',
                    'guard_name' => 'web',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'create reminder',
                    'guard_name' => 'web',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'edit reminder',
                    'guard_name' => 'web',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'delete reminder',
                    'guard_name' => 'web',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ];

            // Insert new permissions
            DB::table('permissions')->insert($newPermissions);

            // Get role IDs for owner and superadmin
            $ownerRole = DB::table('roles')->where('name', 'owner')->first();
            $superadminRole = DB::table('roles')->where('name', 'super admin')->first();

            // Get the new permission ID
            $newPermission = DB::table('permissions')
                ->where('name', 'manage reminder')
                ->first();
            $newPermission1 = DB::table('permissions')
                ->where('name', 'create reminder')
                ->first();
            $newPermission2 = DB::table('permissions')
                ->where('name', 'edit reminder')
                ->first();
            $newPermission3 = DB::table('permissions')
                ->where('name', 'delete reminder')
                ->first();

            // Assign new permission to roles
            $rolePermissions = [
                [
                    'role_id' => $ownerRole->id,
                    'permission_id' => $newPermission->id,
                ],
                [
                    'role_id' => $superadminRole->id,
                    'permission_id' => $newPermission->id,
                ],
                [
                    'role_id' => $ownerRole->id,
                    'permission_id' => $newPermission1->id,
                ],
                [
                    'role_id' => $superadminRole->id,
                    'permission_id' => $newPermission1->id,
                ],
                [
                    'role_id' => $ownerRole->id,
                    'permission_id' => $newPermission2->id,
                ],
                [
                    'role_id' => $superadminRole->id,
                    'permission_id' => $newPermission2->id,
                ],
                [
                    'role_id' => $ownerRole->id,
                    'permission_id' => $newPermission3->id,
                ],
                [
                    'role_id' => $superadminRole->id,
                    'permission_id' => $newPermission3->id,
                ],
            ];
            DB::table('role_has_permissions')->insert($rolePermissions);
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}
