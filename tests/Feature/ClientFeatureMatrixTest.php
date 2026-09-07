<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * What each client's config actually resolves to.
 *
 * Every other test that touches a flag forces it with `config([...])`, which
 * proves the *code branch* works but says nothing about what a given client is
 * really running. A flag flipped by accident — or a typo in a client file —
 * would change behaviour for a live deployment with nothing to catch it.
 *
 * These assertions are deliberately about money and legal behaviour, not the
 * whole flag list, so the file does not need editing every time a cosmetic
 * feature is added.
 */
class ClientFeatureMatrixTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    public function test_drivedesk_keeps_its_full_demo_surface(): void
    {
        // All four are `true` in _default.php; drivedesk is the showcase tenant
        // and keeps the whole surface on except the B2C storefront.
        $this->asClient('drivedesk');

        $this->assertTrue(feature('paypal'));
        $this->assertTrue(feature('stripe'));
        $this->assertTrue(feature('subscriptions'));
        $this->assertTrue(feature('booking_payment'));

        $this->assertTrue(feature('cash_split'));
        $this->assertTrue(feature('invoice_on_full_payment'));
        $this->assertTrue(feature('demo_gateway'));
        $this->assertTrue(feature('traffic_violations'));
        // The B2C storefront stays off — DriveDesk sells the platform (BAN-261).
        $this->assertFalse(feature('public_storefront'));
        // BAN-307: public self-registration creates a `type = 'owner'` account.
        // RegistrationTest forces this flag on to test the route's behaviour, so
        // this is the only assertion holding the live value down. If it goes
        // true, unauthenticated owner-creation returns to a real deployment.
        $this->assertFalse(feature('registration'));
    }

    /**
     * BAN-311 gave these keys an env path. Neither client config contained a
     * single env() call before, so a customer differing on any of them needed a
     * committed config file of its own. The env defaults must reproduce today's
     * values exactly -- this is what fails if a default is edited by accident.
     */
    public function test_the_env_backed_client_values_keep_their_shipped_defaults(): void
    {
        $this->asClient('drivedesk');

        $this->assertSame(['en', 'fr', 'nl', 'ar', 'ary'], config('client.supported_locales'));
        $this->assertSame('ary', config('client.public_default_locale'));
        $this->assertSame('admin@bangicode.ma', config('client.demo_request_to'));
        $this->assertSame(5000, config('client.cash_payment_max'));
    }

    /**
     * End to end under the client's *own* resolved config — no config() forcing.
     *
     * This is the assertion that would have failed before the flag flip, and the
     * one that fails if someone flips it back.
     */
    public function test_a_cash_payment_over_the_ceiling_splits_for_drivedesk(): void
    {
        $this->asClient('drivedesk');
        $this->assertSame(5000, (int) config('client.cash_payment_max'));

        // `create booking payment` is the one booking.payment.store checks —
        // without it the request redirects back and records nothing.
        $permissions = ['manage booking', 'create booking', 'edit booking', 'create booking payment'];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $owner->givePermissionTo($permissions);

        $booking = Booking::factory()->create([
            'parent_id'      => $owner->id,
            'vehicle'        => Vehicle::factory()->create(['parent_id' => $owner->id])->id,
            'driver'         => User::factory()->create(['parent_id' => $owner->id, 'type' => 'driver'])->id,
            'amount'         => 13000,
            'start_date'     => '2026-07-01',
            'end_date'       => '2026-07-11',
            'payment_status' => 'impaye',
        ]);

        $this->actingAs($owner)->post(route('booking.payment.store', $booking->id), [
            'amount'         => 13000,
            'date'           => '2026-07-01',
            'payment_method' => 'Espece',
        ]);

        $payments = BookingPayment::where('booking_id', $booking->id)->orderBy('id')->get();

        // 13000 over a 5000 cap → 5000 + 5000 + 3000, not one refused payment.
        $this->assertCount(3, $payments, 'cash was not split into compliant receipts');
        $this->assertEqualsCanonicalizing(
            [5000.0, 5000.0, 3000.0],
            $payments->pluck('amount')->map(fn ($a) => (float) $a)->all()
        );

        // Each receipt on its own day — two receipts sharing a date would put
        // more than the ceiling on one day, which is the rule being satisfied.
        $this->assertSame(3, $payments->pluck('date')->unique()->count(), 'receipts share a date');
    }
}
