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

        // client:install seeds the active client's branding (Setting rows) --
        // app name, logo, contact details. It is a standalone artisan command
        // rather than a Seeder class (deploys and CI run it as their own step),
        // but without it here a plain `migrate:fresh --seed` leaves the
        // deployment nameless until someone runs it by hand. firstOrCreate, so
        // running it twice is a no-op.
        Artisan::call('client:install');
    }
}
