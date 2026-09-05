<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            DefaultDataUsersTableSeeder::class,
            AllReminderPermissionsSeeder::class,
            TvaSeeder::class,
            DriverSeeder::class,
            DevDataSeeder::class,
        ]);

        // client:install seeds the active client's branding (Setting rows) —
        // app name, logo, contact info, etc. It's a standalone artisan command
        // (also run as its own CI step) rather than a Seeder class, but without
        // it here, a local `migrate:fresh --seed` leaves the storefront with no
        // branding/contact info until someone remembers to run it by hand.
        // Idempotent (firstOrCreate), so this is harmless to also run in CI.
        Artisan::call('client:install');
    }
}
