<?php

namespace Tests\Feature;

use App\Mail\Common;
use App\Models\Notification;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Regression for DIRECTONDERWEG-5 (BAN-258): creating a rental agreement whose
 * vehicle has no resolvable type used to 500 in MessageReplace() — building the
 * {vehicle_type} replacement read `$vehicle->types->type`, i.e. property "type"
 * on null when the vehicle's type FK is dangling (the column defaults to 0 and
 * may never be assigned). The notification interpolation must degrade, not crash.
 */
class MessageReplaceVehicleTypeTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected User $driver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');

        Permission::firstOrCreate(['name' => 'create rental agreement', 'guard_name' => 'web']);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo('create rental agreement');

        $this->driver = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
    }

    public function test_rental_agreement_store_does_not_500_when_vehicle_type_is_missing(): void
    {
        Mail::fake();

        // type = 0 (the column default): no VehicleType row matches, so the
        // hasOne `types` relation resolves to null — the production condition.
        $vehicle = Vehicle::factory()->create([
            'parent_id' => $this->owner->id,
            'type'      => 0,
        ]);

        // The new_agreement email must be enabled so the controller reaches
        // MessageReplace() — the crash site.
        Notification::create([
            'module'        => 'new_agreement',
            'name'          => 'New Agreement',
            'subject'       => 'Agreement for {vehicle_type}',
            'message'       => 'Vehicle type: {vehicle_type}',
            'enabled_email' => 1,
            'parent_id'     => $this->owner->id,
        ]);

        $response = $this->actingAs($this->owner)->post(route('rental-agreement.store'), [
            'vehicle'           => $vehicle->id,
            'driver'            => $this->driver->id,
            'rental_start_date' => '2026-07-01',
            'rental_end_date'   => '2026-07-04',
            'rental_start_time' => '09:00',
            'rental_end_time'   => '18:00',
            'rental_duration'   => 100,
            'status'            => 'draft',
            'create_booking'    => 0,
        ]);

        // Pre-fix this threw `Attempt to read property "type" on null` → 500.
        $response->assertRedirect();
        $this->assertDatabaseHas('rental_agreements', [
            'vehicle' => $vehicle->id,
            'driver'  => $this->driver->id,
        ]);

        // The {vehicle_type} placeholder must render as "-" (the fallback), not
        // blank and not a crash.
        Mail::assertSent(Common::class, function (Common $mail) {
            return str_contains($mail->data['subject'] ?? '', 'Agreement for -')
                && str_contains($mail->data['message'] ?? '', 'Vehicle type: -');
        });
    }
}
