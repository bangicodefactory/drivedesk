<?php

namespace App\Console\Commands;

use App\Models\Setting;
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
        {--force : Run even when the active client is not a demo client (dangerous — injects fake data; not honored in production)}
        {--if-demo : On a non-demo client, skip quietly (exit 0) instead of failing — for unconditional deploy/scheduler use}';

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
        // --force is an escape hatch for local/staging experiments only. In
        // production it is never honored: the refresh step below deletes the
        // real owner's bookings, payments and factures before reseeding.
        $forceAllowed = $this->option('force') && ! app()->isProduction();

        if (! feature('demo_gateway') && ! $forceAllowed) {
            $client = config('app.client', 'drivedesk');

            // --if-demo: deploy/scheduler call this on every client unconditionally;
            // on a real client it's a clean no-op, not a deploy-breaking error.
            if ($this->option('if-demo')) {
                $this->info("Skipping demo:seed — client '{$client}' is not a demo client.");

                return self::SUCCESS;
            }

            $this->error("Refusing to run: client '{$client}' is not a demo client (feature 'demo_gateway' is off).");
            $this->line('  demo:seed injects fake showcase data — never run it against a real client.');

            if ($this->option('force')) {
                $this->line('  --force is not honored in production: the refresh step would delete the real owner\'s bookings, payments and factures.');
            } else {
                $this->line('  Use --force only if you are certain this deployment should hold demo data.');
            }

            return self::FAILURE;
        }

        $client = config('app.client', 'drivedesk');
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

        // 4. Contact details for the showcase agency (BAN-341).
        //
        //    This belongs HERE and not in `branding_seed`, which was the first
        //    attempt. `branding_seed` is the *variant* default: client:install
        //    applies it on every deploy of every deployment running
        //    APP_CLIENT=drivedesk, which is all of them. And `company_email` is
        //    not the footer row it looks like -- ContactController::recipient()
        //    delivers the public contact form to it, invoice1.blade.php prints it
        //    on invoice PDFs (:524, :653), and helper.php interpolates
        //    {company_email} into driver-facing mail. Seeding it there would have
        //    pointed a real customer's renters at the vendor's inbox and put the
        //    vendor's address on their invoices.
        //
        //    This command is gated on feature('demo_gateway') and no-ops on a
        //    real client, so what it writes reaches demo deployments only.
        //
        //    parent_id = 1 is the bucket a guest reads: settings() falls back to
        //    it for unauthenticated requests (helper.php:140), which is what the
        //    storefront footer, /contact and the booking confirmation render from.
        $demoContact = [
            // One source for the address, so an env override moves the demo inbox
            // and the storefront together instead of leaving them disagreeing.
            'company_email'  => config('client.demo_request_to'),
            // Numeric ranges only. A word like "Closed" would be stored as data
            // and render untranslated on the French and Arabic storefronts, and
            // there is no settings form for these keys to correct it from.
            'hours_weekday'  => '09:00 - 19:00',
            'hours_saturday' => '09:00 - 14:00',
        ];

        $wrote = 0;
        foreach (array_filter($demoContact) as $key => $value) {
            $setting = Setting::firstOrCreate(
                ['name' => $key, 'parent_id' => 1],
                ['value' => $value],
            );

            if ($setting->wasRecentlyCreated) {
                $wrote++;
            }
        }

        if ($wrote) {
            flushSettingsCache(1);
            $this->info("Seeded {$wrote} demo contact setting(s).");
        }

        $this->info('Demo data ready.');

        return self::SUCCESS;
    }
}
