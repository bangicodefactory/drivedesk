<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Backfill the `manage driver blacklist` permission (BAN-252) onto existing
 * tenants' owner/manager roles. Data-only (no schema change) — mirrors
 * 2025_12_19_152801_fix_duplicate_permissions_and_add_tva_permissions. New
 * tenants get it from DefaultDataUsersTableSeeder.
 */
return new class extends Migration
{
    private string $permission = 'manage driver blacklist';

    public function up(): void
    {
        Permission::firstOrCreate(['name' => $this->permission, 'guard_name' => 'web']);

        foreach (Role::whereIn('name', ['owner', 'manager'])->get() as $role) {
            $role->givePermissionTo($this->permission);
        }
    }

    public function down(): void
    {
        foreach (Role::whereIn('name', ['owner', 'manager'])->get() as $role) {
            $role->revokePermissionTo($this->permission);
        }
    }
};
