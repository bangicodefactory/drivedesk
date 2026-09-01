<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Isolation coverage for every model carrying BelongsToTenant (Tranche S.1).
 *
 * BAN-296 scoped thirteen models without adding a test for any of them, which
 * CLAUDE.md §3 requires in the same PR: a green suite proved nothing had
 * broken, not that isolation worked. Each model is checked for the same three
 * properties the trait promises, so a model added to the list later cannot be
 * scoped without being covered.
 *
 *   1. another tenant's row does not resolve
 *   2. a super admin still sees it
 *   3. the scope is inert with no authenticated user (console, seeders, queue)
 *
 * DriverBlacklist has no factory and is exercised through
 * DriverBlacklistTest/BookingBlacklistTest instead; Signature deliberately
 * carries no scope (no parent_id column — see the model).
 */
class TenantScopeCoverageTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected User $otherOwner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        $this->owner      = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
    }

    /** Every model that carries the trait, with the factory used to build one. */
    public static function scopedModels(): array
    {
        return [
            'Addon'          => [\App\Models\Addon::class],
            'Expense'        => [\App\Models\Expense::class],
            'ExpenseType'    => [\App\Models\ExpenseType::class],
            'Inspection'     => [\App\Models\Inspection::class],
            'InspectionType' => [\App\Models\InspectionType::class],
            'Notification'   => [\App\Models\Notification::class],
            'Option'         => [\App\Models\Option::class],
            'Place'          => [\App\Models\Place::class],
            'Reminder'       => [\App\Models\Reminder::class],
            'ReminderType'   => [\App\Models\ReminderType::class],
            'VehicleType'    => [\App\Models\VehicleType::class],
            'Credit'         => [\App\Models\Credit::class],
        ];
    }

    /**
     * Built with no authenticated user so the scope stays off and the row is
     * created against the other tenant deliberately.
     */
    private function foreignRow(string $model)
    {
        return $model::factory()->create(['parent_id' => $this->otherOwner->id]);
    }

    /**
     * @dataProvider scopedModels
     */
    public function test_another_tenants_row_does_not_resolve(string $model): void
    {
        $row = $this->foreignRow($model);

        $this->actingAs($this->owner);

        $this->assertNull(
            $model::find($row->id),
            $model . ' resolved another tenant\'s row'
        );
    }

    /**
     * @dataProvider scopedModels
     */
    public function test_super_admin_still_sees_every_tenants_rows(string $model): void
    {
        $row = $this->foreignRow($model);

        // parentId() returns a super admin's own id, which is never any tenant's
        // parent_id, so scoping on it would hide every row in the system.
        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $this->actingAs($superAdmin);

        $this->assertNotNull(
            $model::find($row->id),
            $model . ' was hidden from a super admin'
        );
    }

    /**
     * @dataProvider scopedModels
     */
    public function test_scope_is_inert_without_an_authenticated_user(string $model): void
    {
        $row = $this->foreignRow($model);

        $this->assertFalse(auth()->check());

        $this->assertNotNull(
            $model::find($row->id),
            $model . ' was scoped with no authenticated user — console commands, '
                . 'seeders and queue jobs run this way and parentId() would fatal'
        );
    }

    /**
     * @dataProvider scopedModels
     */
    public function test_a_tenant_still_reaches_its_own_row(string $model): void
    {
        $own = $model::factory()->create(['parent_id' => $this->owner->id]);

        $this->actingAs($this->owner);

        $this->assertNotNull(
            $model::find($own->id),
            $model . ' hid a row from its own tenant'
        );
    }
}
