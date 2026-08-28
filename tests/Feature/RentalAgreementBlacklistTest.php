<?php

namespace Tests\Feature;

use App\Models\DriverBlacklist;
use App\Models\RentalAgreement;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Blacklist enforcement on rental-agreement (contract) creation (BAN-252, PR 2),
 * covering the optional second driver. Blacklisted driver/driver2 blocks unless
 * acknowledged; overrides are recorded per blacklisted driver.
 */
class RentalAgreementBlacklistTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected User $driver;
    protected User $driver2;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        Permission::firstOrCreate(['name' => 'create rental agreement', 'guard_name' => 'web']);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo('create rental agreement');

        $this->driver  = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        $this->driver2 = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        $this->vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
    }

    private function blacklist(User $driver, string $reason): DriverBlacklist
    {
        return DriverBlacklist::create([
            'driver_user_id' => $driver->id,
            'parent_id'      => $this->owner->id,
            'reason'         => $reason,
            'blacklisted_by' => $this->owner->id,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'vehicle'            => $this->vehicle->id,
            'driver'             => $this->driver->id,
            'rental_start_date'  => '2026-07-01',
            'rental_end_date'    => '2026-07-04',
            'rental_start_time'  => '09:00',
            'rental_end_time'    => '18:00',
            'rental_duration'    => 100,
            'status'             => 'draft',
            'create_booking'     => 0,
        ], $overrides);
    }

    public function test_blocked_when_primary_driver_blacklisted_without_acknowledgement(): void
    {
        $this->blacklist($this->driver, 'No-show');

        $this->actingAs($this->owner)
            ->post(route('rental-agreement.store'), $this->payload())
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('rental_agreements', ['driver' => $this->driver->id]);
    }

    public function test_blocked_when_driver2_blacklisted_without_acknowledgement(): void
    {
        // Primary driver clean; the optional driver2 is blacklisted.
        $this->blacklist($this->driver2, 'Fraud');

        $this->actingAs($this->owner)
            ->post(route('rental-agreement.store'), $this->payload(['driver2' => $this->driver2->id]))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('rental_agreements', ['driver' => $this->driver->id]);
    }

    public function test_proceeds_with_acknowledgement_and_records_override_for_both(): void
    {
        $bl1 = $this->blacklist($this->driver, 'No-show');
        $bl2 = $this->blacklist($this->driver2, 'Fraud');

        $this->actingAs($this->owner)
            ->post(route('rental-agreement.store'), $this->payload([
                'driver2'              => $this->driver2->id,
                'acknowledge_blacklist' => 1,
            ]))
            ->assertRedirect();

        $agreement = RentalAgreement::where('driver', $this->driver->id)->first();
        $this->assertNotNull($agreement);

        foreach ([$bl1, $bl2] as $bl) {
            $bl->refresh();
            $this->assertCount(1, $bl->overrides);
            $this->assertSame('rental_agreement', $bl->overrides[0]['context_type']);
            $this->assertSame($agreement->id, $bl->overrides[0]['context_id']);
        }
    }
}
