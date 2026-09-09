<?php

namespace Tests\Feature;

use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Isolation coverage for every model carrying BelongsToTenant (Tranche S.1).
 *
 * BAN-296 scoped thirteen models without adding a test for any of them, which
 * CLAUDE.md §3 requires in the same PR: a green suite proved nothing had
 * broken, not that isolation worked.
 *
 * The model list is derived by scanning app/Models for the trait rather than
 * hand-written, so adding the trait to a new model cannot silently skip
 * coverage — the earlier version of this file claimed that guarantee while
 * using a literal list, which is exactly the kind of comment that turns out to
 * be false later. A model with no usable factory must be given a builder in
 * BUILDERS below or the run fails loudly rather than quietly skipping it.
 *
 * Each model is checked for the three properties the trait promises:
 *   1. another tenant's row does not resolve
 *   2. a super admin still sees it
 *   3. the scope is inert with no authenticated user (console, seeders, queue)
 */
class TenantScopeCoverageTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected User $otherOwner;

    /**
     * Models whose factory cannot insert, with a builder that can.
     *
     * Kept deliberately short: a factory that cannot insert is a bug in the
     * factory, and the fix belongs there so every other suite gets it too.
     * InspectionFactory and NotificationFactory were repaired for exactly
     * that reason and now go through the default path in makeRow().
     *
     * DriverFactory types driver_id as a 'DR-####' string into an integer
     * column, and DriverBlacklist has no factory at all.
     */
    private const BUILDERS = [
        \App\Models\Driver::class          => 'buildDriver',
        \App\Models\DriverBlacklist::class => 'buildDriverBlacklist',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        $this->owner      = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
    }

    /** Every model in app/Models that uses BelongsToTenant. */
    public static function scopedModels(): array
    {
        // PHPUnit resolves data providers before the Laravel app boots, so
        // app_path() and class_uses_recursive() are unavailable here — use the
        // filesystem and plain reflection instead.
        $cases = [];
        $dir = dirname(__DIR__, 2) . '/app/Models';

        foreach (glob($dir . '/*.php') as $file) {
            $class = 'App\\Models\\' . basename($file, '.php');

            if (! class_exists($class)) {
                continue;
            }

            $traits = [];
            for ($c = $class; $c !== false; $c = get_parent_class($c)) {
                $traits += class_uses($c) ?: [];
            }

            if (in_array(BelongsToTenant::class, $traits, true)) {
                $short = substr($class, strrpos($class, '\\') + 1);
                $cases[$short] = [$class];
            }
        }

        ksort($cases);

        return $cases;
    }

    /** DriverFactory types driver_id as a 'DR-####' string; the column is an integer. */
    private function buildDriver(int $parentId)
    {
        $user = \App\Models\User::factory()->driver()->create(['parent_id' => $parentId]);

        return \App\Models\Driver::create([
            'driver_id' => random_int(1, 100000),
            'user_id'   => $user->id,
            'gender'    => 'Male',
            'parent_id' => $parentId,
        ]);
    }

    /** DriverBlacklist has no factory. */
    private function buildDriverBlacklist(int $parentId)
    {
        $user = \App\Models\User::factory()->driver()->create(['parent_id' => $parentId]);

        return \App\Models\DriverBlacklist::create([
            'driver_user_id' => $user->id,
            'parent_id'      => $parentId,
            'reason'         => 'Coverage fixture',
            'blacklisted_by' => $this->owner->id,
        ]);
    }

    /** Build one row of $model owned by $parentId, with the scope inert. */
    private function makeRow(string $model, int $parentId)
    {
        if (isset(self::BUILDERS[$model])) {
            return $this->{self::BUILDERS[$model]}($parentId);
        }

        return $model::factory()->create(['parent_id' => $parentId]);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('scopedModels')]
    public function test_another_tenants_row_does_not_resolve(string $model): void
    {
        $row = $this->makeRow($model, $this->otherOwner->id);

        $this->actingAs($this->owner);

        $this->assertNull($model::find($row->id), $model . " resolved another tenant's row");
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('scopedModels')]
    public function test_super_admin_still_sees_every_tenants_rows(string $model): void
    {
        $row = $this->makeRow($model, $this->otherOwner->id);

        // parentId() returns a super admin's own id, which is never any tenant's
        // parent_id, so scoping on it would hide every row in the system.
        $superAdmin = User::factory()->create(['type' => 'super admin', 'parent_id' => 0]);
        $this->actingAs($superAdmin);

        $this->assertNotNull($model::find($row->id), $model . ' was hidden from a super admin');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('scopedModels')]
    public function test_scope_is_inert_without_an_authenticated_user(string $model): void
    {
        $row = $this->makeRow($model, $this->otherOwner->id);

        $this->assertFalse(auth()->check());

        $this->assertNotNull(
            $model::find($row->id),
            $model . ' was scoped with no authenticated user — console commands, '
                . 'seeders and queue jobs run this way and parentId() would fatal'
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('scopedModels')]
    public function test_a_tenant_still_reaches_its_own_row(string $model): void
    {
        $own = $this->makeRow($model, $this->owner->id);

        $this->actingAs($this->owner);

        $this->assertNotNull($model::find($own->id), $model . ' hid a row from its own tenant');
    }
}
