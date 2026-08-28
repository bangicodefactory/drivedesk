<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Covers the demo:seed command (BAN-247): showcase data for demo clients,
 * gated to feature('demo_gateway'), re-anchored to "today" on every run.
 */
class DemoSeedCommandTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    public function test_it_refuses_on_a_non_demo_client_and_seeds_nothing(): void
    {
        $this->asClient('acme'); // demo_gateway off

        $this->artisan('demo:seed')
            ->expectsOutputToContain('Refusing to run')
            ->assertFailed();

        $this->assertSame(0, Booking::count());
        $this->assertSame(0, Vehicle::count());
    }

    public function test_if_demo_skips_gracefully_on_a_non_demo_client(): void
    {
        $this->asClient('acme'); // demo_gateway off

        // Deploy/scheduler call demo:seed --if-demo unconditionally; on a real
        // client it must be a clean no-op (exit 0), not a deploy-breaking error.
        $this->artisan('demo:seed --if-demo')
            ->expectsOutputToContain('Skipping demo:seed')
            ->assertSuccessful();

        $this->assertSame(0, Booking::count());
        $this->assertSame(0, Vehicle::count());
    }

    public function test_force_is_not_honored_in_production_on_a_real_client(): void
    {
        $this->asClient('acme'); // demo_gateway off
        $this->app['env'] = 'production';

        // demo:seed's refresh step deletes the owner's bookings/payments/factures
        // before reseeding — on a real production client --force must not unlock
        // that, no matter how certain the operator claims to be.
        $this->artisan('demo:seed --force')
            ->expectsOutputToContain('Refusing to run')
            ->assertFailed();

        $this->assertSame(0, Booking::count());
        $this->assertSame(0, Vehicle::count());
    }

    public function test_force_still_works_outside_production(): void
    {
        $this->asClient('acme'); // demo_gateway off, env is 'testing'

        $this->artisan('demo:seed --force')->assertSuccessful();

        $this->assertGreaterThan(0, Vehicle::count());
    }

    public function test_demo_seed_works_in_production_on_a_demo_client(): void
    {
        // Pins the drivedesk deploy path: deploy.yml runs `demo:seed --if-demo`
        // on a host whose APP_ENV is production. DevDataSeeder's production
        // guard must exempt demo clients or demo data silently stops refreshing.
        $this->asClient('drivedesk'); // demo_gateway on
        $this->app['env'] = 'production';

        $this->artisan('demo:seed --if-demo')->assertSuccessful();

        $this->assertGreaterThan(0, Vehicle::count());
        $this->assertGreaterThan(0, Booking::count());
    }

    public function test_it_seeds_showcase_data_on_a_demo_client(): void
    {
        $this->asClient('drivedesk'); // demo_gateway on

        $this->artisan('demo:seed')->assertSuccessful();

        $owner = User::where('type', 'owner')->firstOrFail();

        $this->assertGreaterThan(0, Vehicle::where('parent_id', $owner->id)->count());
        $this->assertGreaterThan(0, Booking::where('parent_id', $owner->id)->count());

        // Dates are anchored to now: the seeder plants future bookings (+5/+15d),
        // so at least one booking must start after today.
        $this->assertTrue(
            Booking::where('parent_id', $owner->id)
                ->whereDate('start_date', '>', now()->toDateString())
                ->exists(),
            'Expected a future-dated booking anchored to today.'
        );
    }

    public function test_rerunning_does_not_duplicate_and_re_anchors_dates_to_today(): void
    {
        $this->asClient('drivedesk');

        $this->artisan('demo:seed')->assertSuccessful();
        $owner = User::where('type', 'owner')->firstOrFail();
        $firstCount = Booking::where('parent_id', $owner->id)->count();

        // Jump ~100 days forward and reseed. A plain firstOrCreate seeder would
        // leave the old dates frozen; demo:seed wipes + reseeds so they move.
        Carbon::setTestNow(now()->addDays(100));
        try {
            $this->artisan('demo:seed')->assertSuccessful();

            // No duplication — same number of bookings.
            $this->assertSame($firstCount, Booking::where('parent_id', $owner->id)->count());

            // Re-anchored: a booking now lives in the *new* today window. With the
            // old frozen dates (≈100 days in the past) nothing would match this.
            $this->assertTrue(
                Booking::where('parent_id', $owner->id)
                    ->whereDate('start_date', '>=', now()->subDays(65)->toDateString())
                    ->exists(),
                'Expected demo dates to re-anchor to the new "today" after reseeding.'
            );
        } finally {
            Carbon::setTestNow();
        }
    }
}
