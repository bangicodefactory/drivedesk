<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\AllReminderPermissionsSeeder;
use Database\Seeders\DefaultDataUsersTableSeeder;
use Database\Seeders\DevDataSeeder;
use Database\Seeders\DriverSeeder;
use Database\Seeders\TvaSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Seeds (and refreshes) the showcase data a demo client shows off — fleet,
 * bookings, payments, expenses, inspections, reminders, agreements, credits and
 * the derived TVA invoices.
 *
 * Why this exists rather than plain `db:seed`:
 *   1. It is GATED to demo clients (feature 'demo_gateway'); it refuses on real
 *      clients so fake data can never land in production by accident.
 *   2. It RE-ANCHORS the time-relative demo data to "today" on every run.
 *      DevDataSeeder uses now()-relative dates but guards rows with firstOrCreate,
 *      so a plain re-run never moves the dates forward and the demo silently ages
 *      (upcoming reminders go overdue, last year's TVA report empties out). This
 *      command drops the dated rows first, so each run looks fresh.
 *
 * Idempotent: safe to run on every deploy and on a schedule.
 */
class DemoSeed extends Command
{
    protected $signature = 'demo:seed
        {--force : Run even when the active client is not a demo client (dangerous — injects fake data)}';

    protected $description = 'Seed/refresh showcase data for a demo client. Re-anchors dates to today. Refuses on non-demo clients unless --force.';

    /**
     * Dated, transactional tables wiped before reseeding so each run re-anchors
     * the demo to today. Ordered child → parent so reference rows go first.
     * Catalog tables (vehicles, places, addons, *_types) are intentionally NOT
     * here — they carry no stale dates and DevDataSeeder's firstOrCreate keeps
     * them stable across runs.
     */
    private const REFRESHED_TABLES = [
        'tvas',              // → booking_payments / bookings
        'booking_payments',  // → bookings
        'bookings',
        'credits',
        'expenses',
        'inspections',
        'reminders',
        'rental_agreements',
    ];

    public function handle(): int
    {
        if (! feature('demo_gateway') && ! $this->option('force')) {
            $client = config('app.client', 'directonderweg');
            $this->error("Refusing to run: client '{$client}' is not a demo client (feature 'demo_gateway' is off).");
            $this->line('  demo:seed injects fake showcase data — never run it against a real client.');
            $this->line('  Use --force only if you are certain this deployment should hold demo data.');

            return self::FAILURE;
        }

        $client = config('app.client', 'directonderweg');
        $this->info("Seeding demo data for client: {$client}");

        // 1. Base data (idempotent): owner + roles/permissions, TVA setup, drivers.
        //    --force skips db:seed's production confirmation; we've already gated above.
        foreach ([
            DefaultDataUsersTableSeeder::class,
            AllReminderPermissionsSeeder::class,
            TvaSeeder::class,
            DriverSeeder::class,
        ] as $seeder) {
            $this->call('db:seed', ['--class' => $seeder, '--force' => true]);
        }

        // 2. Refresh: drop the owner's dated rows so the reseed re-anchors to now.
        $owner = User::where('type', 'owner')->first();
        if ($owner) {
            $cleared = 0;
            foreach (self::REFRESHED_TABLES as $table) {
                $cleared += DB::table($table)->where('parent_id', $owner->id)->delete();
            }
            $this->info("Re-anchoring: cleared {$cleared} dated demo rows for owner #{$owner->id}.");
        }

        // 3. Reseed the showcase business data with fresh, today-relative dates.
        $this->call('db:seed', ['--class' => DevDataSeeder::class, '--force' => true]);

        $this->info('Demo data ready.');

        return self::SUCCESS;
    }
}
