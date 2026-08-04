<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Backfill the traffic-violation permissions (BAN-260) onto existing tenants'
 * owner/manager roles. Data-only (no schema change) — mirrors
 * 2026_06_19_000002_add_driver_blacklist_permission. New tenants get them from
 * DefaultDataUsersTableSeeder.
 */
return new class extends Migration
{
    /** @var string[] */
    private array $permissions = [
        'manage traffic violation',
        'create traffic violation',
        'edit traffic violation',
        'delete traffic violation',
    ];

    public function up(): void
    {
        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (Role::whereIn('name', ['owner', 'manager'])->get() as $role) {
            $role->givePermissionTo($this->permissions);
        }
    }

    public function down(): void
    {
        foreach (Role::whereIn('name', ['owner', 'manager'])->get() as $role) {
            foreach ($this->permissions as $permission) {
                $role->revokePermissionTo($permission);
            }
        }
    }
};
