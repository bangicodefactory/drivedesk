<?php

namespace Tests\Feature;

use App\Models\RentalAgreement;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * BAN-259: rental end date must not precede the start date. Enforced server-side
 * with `after_or_equal:rental_start_date` on both store and update (same-day
 * agreements stay valid). The React pages also show a blocking modal, but the
 * server rule is the authoritative check.
 */
class RentalAgreementDateOrderTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected User $driver;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');

        foreach (['create rental agreement', 'edit rental agreement'] as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(['create rental agreement', 'edit rental agreement']);

        $this->driver  = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        $this->vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'vehicle'           => $this->vehicle->id,
            'driver'            => $this->driver->id,
            'rental_start_date' => '2026-07-10',
            'rental_end_date'   => '2026-07-12',
            'rental_start_time' => '09:00',
            'rental_end_time'   => '18:00',
            'rental_duration'   => 2,
            'status'            => 'draft',
            'create_booking'    => 0,
        ], $overrides);
    }

    public function test_store_rejects_end_date_before_start_date(): void
    {
        $this->actingAs($this->owner)
            ->post(route('rental-agreement.store'), $this->payload([
                'rental_start_date' => '2026-07-10',
                'rental_end_date'   => '2026-07-01',
            ]))
            ->assertSessionHasErrors('rental_end_date');

        $this->assertDatabaseMissing('rental_agreements', ['driver' => $this->driver->id]);
    }

    public function test_store_allows_same_day_agreement(): void
    {
        $this->actingAs($this->owner)
            ->post(route('rental-agreement.store'), $this->payload([
                'rental_start_date' => '2026-07-10',
                'rental_end_date'   => '2026-07-10',
            ]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('rental_agreements', ['driver' => $this->driver->id]);
    }

    public function test_store_allows_end_after_start(): void
    {
        $this->actingAs($this->owner)
            ->post(route('rental-agreement.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('rental_agreements', ['vehicle' => $this->vehicle->id]);
    }

    public function test_update_rejects_end_date_before_start_date(): void
    {
        // Seed a valid agreement through the normal flow, then try to break it.
        $this->actingAs($this->owner)->post(route('rental-agreement.store'), $this->payload());
        $agreement = RentalAgreement::where('driver', $this->driver->id)->firstOrFail();

        $this->actingAs($this->owner)
            ->put(route('rental-agreement.update', $agreement->id), [
                'vehicle'           => $this->vehicle->id,
                'driver'            => $this->driver->id,
                'rental_start_date' => '2026-07-10',
                'rental_end_date'   => '2026-07-01',
                'rental_start_time' => '09:00',
                'rental_end_time'   => '18:00',
                'rental_duration'   => 2,
                'status'            => 'draft',
            ])
            ->assertSessionHasErrors('rental_end_date');
    }
}
