<?php

namespace Tests\Feature\Console;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientInstallTest extends TestCase
{
    use RefreshDatabase;

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
        // Warm the cache the same way a guest request would, before any
        // branding exists — this is what actually happened seeding MarrueCar's
        // WhatsApp number after /reserve had already been hit once.
        $this->assertSame('', settings()['app_name']);

        config(['clients.acme.branding_seed' => ['app_name' => 'Acme Rentals']]);
        $this->artisan('client:install', ['--client' => 'acme'])->assertSuccessful();

        // Without flushing, this would still read the pre-seed cached value.
        $this->assertSame('Acme Rentals', settings()['app_name']);
    }

    public function test_no_branding_seed_exits_cleanly(): void
    {
        config(['clients.acme.branding_seed' => []]);

        $this->artisan('client:install', ['--client' => 'acme'])
            ->assertSuccessful()
            ->expectsOutputToContain('Nothing to do');
    }

    public function test_database_seeder_installs_branding_without_a_manual_step(): void
    {
        // A plain `migrate:fresh --seed` (the normal local dev reset) must
        // leave the storefront with real branding, not just the internal
        // demo/user data — nobody should have to remember a second command.
        config(['clients.' . config('app.client', 'drivedesk') . '.branding_seed' => [
            'app_name' => 'Seeded Via DatabaseSeeder',
        ]]);

        $this->seed();

        $this->assertDatabaseHas('settings', [
            'name' => 'app_name', 'value' => 'Seeded Via DatabaseSeeder', 'parent_id' => 1,
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
