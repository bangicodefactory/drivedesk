<?php

namespace Tests\Feature;

use App\Models\DriverBlacklist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Blacklisting capability (BAN-252, PR 1): blacklist/un-blacklist a driver with a
 * reason, permission- and tenant-gated, history-preserving.
 */
class DriverBlacklistTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;

    protected User $driverUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');

        Permission::firstOrCreate(['name' => 'manage driver blacklist', 'guard_name' => 'web']);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo('manage driver blacklist');

        $this->driverUser = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
    }

    public function test_blacklist_denied_without_permission(): void
    {
        $employee = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($employee)
            ->post(route('driver.blacklist', $this->driverUser->id), ['reason' => 'x'])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('driver_blacklists', 0);
    }

    public function test_blacklist_requires_a_reason(): void
    {
        $this->actingAs($this->owner)
            ->post(route('driver.blacklist', $this->driverUser->id), [])
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('driver_blacklists', 0);
    }

    public function test_blacklist_creates_an_active_row(): void
    {
        $this->actingAs($this->owner)
            ->post(route('driver.blacklist', $this->driverUser->id), ['reason' => 'Damaged a vehicle'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('driver_blacklists', [
            'driver_user_id' => $this->driverUser->id,
            'parent_id'      => $this->owner->id,
            'reason'         => 'Damaged a vehicle',
            'blacklisted_by' => $this->owner->id,
            'lifted_at'      => null,
        ]);
    }

    public function test_blacklist_is_idempotent(): void
    {
        $this->actingAs($this->owner)->post(route('driver.blacklist', $this->driverUser->id), ['reason' => 'first']);
        $this->actingAs($this->owner)
            ->post(route('driver.blacklist', $this->driverUser->id), ['reason' => 'second'])
            ->assertSessionHas('error');

        $this->assertSame(1, DriverBlacklist::where('driver_user_id', $this->driverUser->id)->whereNull('lifted_at')->count());
    }

    public function test_unblacklist_lifts_and_keeps_history(): void
    {
        $row = DriverBlacklist::create([
            'driver_user_id' => $this->driverUser->id,
            'parent_id'      => $this->owner->id,
            'reason'         => 'No-show',
            'blacklisted_by' => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->post(route('driver.unblacklist', $this->driverUser->id))
            ->assertSessionHas('success');

        $row->refresh();
        $this->assertNotNull($row->lifted_at);            // lifted
        $this->assertSame($this->owner->id, $row->lifted_by);
        $this->assertDatabaseHas('driver_blacklists', ['id' => $row->id]); // history kept
    }

    public function test_blacklist_is_tenant_scoped(): void
    {
        // A driver belonging to a different tenant.
        $otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $otherDriver = User::factory()->driver()->create(['parent_id' => $otherOwner->id]);

        $this->actingAs($this->owner)
            ->post(route('driver.blacklist', $otherDriver->id), ['reason' => 'cross-tenant'])
            ->assertSessionHas('error');

        $this->assertDatabaseCount('driver_blacklists', 0);
    }
}
