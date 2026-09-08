<?php

namespace Tests\Feature\Console;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class ClientInstallTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    // These tests exercise the command against a fixed, made-up client. Pin
    // --client so they're deterministic regardless of the CI matrix's ambient
    // APP_CLIENT — without it, client:install installs the active client and
    // the assertions below break.
    public function test_seeds_branding_on_first_run(): void
    {
        config(['clients.acme.branding_seed' => [
            'app_name'  => 'Acme Rentals',
            'theme_color' => 'color1',
        ]]);

        $this->artisan('client:install', ['--client' => 'acme'])
            ->assertSuccessful()
            ->expectsOutputToContain('Seeded');

        $this->assertDatabaseHas('settings', ['name' => 'app_name',    'value' => 'Acme Rentals', 'parent_id' => 1]);
        $this->assertDatabaseHas('settings', ['name' => 'theme_color', 'value' => 'color1',          'parent_id' => 1]);
    }

    public function test_second_run_skips_existing_values(): void
    {
        config(['clients.acme.branding_seed' => [
            'app_name' => 'Acme Rentals',
        ]]);

        $this->artisan('client:install', ['--client' => 'acme'])->assertSuccessful();

        // Admin manually changes the value.
        Setting::where('name', 'app_name')->where('parent_id', 1)
            ->update(['value' => 'Custom Name']);

        // Second run must not overwrite the admin's edit.
        $this->artisan('client:install', ['--client' => 'acme'])
            ->assertSuccessful()
            ->expectsOutputToContain('Skipped');

        $this->assertDatabaseHas('settings', ['name' => 'app_name', 'value' => 'Custom Name', 'parent_id' => 1]);
    }

    public function test_seeding_invalidates_the_settings_cache(): void
    {
        // settings() memoises its row set for 5 minutes. Warm that cache the
        // way a guest request would -- before any branding exists -- and the
        // freshly seeded values stay invisible for the rest of the window.
        // This is not hypothetical: seeding a contact number after the public
        // storefront had already been requested once produced exactly this.
        $this->assertSame('', settings()['app_name']);

        config(['clients.acme.branding_seed' => ['app_name' => 'Acme Rentals']]);
        $this->artisan('client:install', ['--client' => 'acme'])->assertSuccessful();

        // Without the flush this still reads the pre-seed cached value.
        $this->assertSame('Acme Rentals', settings()['app_name']);
    }

    public function test_database_seeder_installs_branding_without_a_manual_step(): void
    {
        // A plain `migrate:fresh --seed` -- the normal local reset -- must leave
        // the deployment with real branding, not just the internal demo data.
        // Nobody should have to remember a second command for the storefront to
        // know its own name.
        config(['clients.' . config('app.client', 'drivedesk') . '.branding_seed' => [
            'app_name' => 'Seeded Via DatabaseSeeder',
        ]]);

        $this->seed();

        $this->assertDatabaseHas('settings', [
            'name' => 'app_name', 'value' => 'Seeded Via DatabaseSeeder', 'parent_id' => 1,
        ]);
    }

    public function test_no_branding_seed_exits_cleanly(): void
    {
        config(['clients.acme.branding_seed' => []]);

        $this->artisan('client:install', ['--client' => 'acme'])
            ->assertSuccessful()
            ->expectsOutputToContain('Nothing to do');
    }

    public function test_the_flush_targets_the_bucket_the_command_writes_to(): void
    {
        // The command always writes parent_id = 1. flushSettingsCache() with no
        // argument keys off the *acting* account, so an invocation with someone
        // authenticated would forget their bucket and leave the written one
        // stale -- reproducing the very staleness the flush exists to prevent,
        // while the unauthenticated test above stays green.
        $this->assertSame('', settingsFor(1)['app_name']);

        $owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->actingAs($owner);

        config(['clients.acme.branding_seed' => ['app_name' => 'Acme Rentals']]);
        $this->artisan('client:install', ['--client' => 'acme'])->assertSuccessful();

        $this->assertSame('Acme Rentals', settingsFor(1)['app_name']);
    }

    public function test_branding_is_seeded_even_when_a_later_seeder_refuses_to_run(): void
    {
        // Branding is essential; demo data is optional. DevDataSeeder hard-stops
        // on a non-demo client in production, and firstOrFail()s when no owner
        // exists (the web installer's path, where DefaultDataUsersTableSeeder
        // skips its body). Seeded last, branding would be missed in exactly the
        // situations where a deployment most needs it -- so this pins the order.
        $this->asClient('acme');   // demo_gateway off
        $this->app->detectEnvironment(fn () => 'production');

        config(['clients.acme.branding_seed' => ['app_name' => 'Survives The Stop']]);

        try {
            // Not $this->seed(): db:seed is a ConfirmableCommand, so in a
            // production environment it asks "do you really wish to run this?"
            // and the mocked console output has no answer for it.
            $this->artisan('db:seed', ['--force' => true])->run();
        } catch (\RuntimeException $e) {
            // Expected: DevDataSeeder refuses to fabricate business data here.
            // Anything else propagates and fails the test, which is right.
        }

        $this->assertDatabaseHas('settings', [
            'name' => 'app_name', 'value' => 'Survives The Stop', 'parent_id' => 1,
        ]);
    }

    public function test_client_option_overrides_app_client(): void
    {
        config(['clients.globex.branding_seed' => [
            'app_name' => 'Globex Rentals',
        ]]);

        $this->artisan('client:install', ['--client' => 'globex'])
            ->assertSuccessful()
            ->expectsOutputToContain('globex'); // confirms the --client flag was honoured

        $this->assertDatabaseHas('settings', ['name' => 'app_name', 'value' => 'Globex Rentals', 'parent_id' => 1]);
    }
}
