<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Branding first, deliberately. client:install seeds the active client's
        // Setting rows -- app name, logo, contact details -- and it must not be
        // a casualty of an optional step failing: DevDataSeeder below hard-stops
        // on a non-demo client in production, and firstOrFail()s when no owner
        // exists (the web installer's path, where DefaultDataUsersTableSeeder
        // skips its body). Last in the list, branding would be skipped in
        // exactly the cases where a deployment most needs it.
        //
        // Order also matters the other way: DevDataSeeder reads settings() and
        // stamps company_name/company_address onto every demo facture it
        // generates, so running after this gives those the client's real
        // identity instead of settingsKeys() defaults.
        //
        // $this->command->call (not Artisan::call) so a "No branding_seed found"
        // warning reaches the operator instead of a discarded buffer.
        // Idempotent (firstOrCreate); CI runs it as its own step too.
        $this->command->call('client:install');

        $this->call([
            DefaultDataUsersTableSeeder::class,
            AllReminderPermissionsSeeder::class,
            TvaSeeder::class,
            DriverSeeder::class,
            DevDataSeeder::class,
        ]);
    }
}
