<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class ClientInstall extends Command
{
    protected $signature = 'client:install
        {--client= : Override APP_CLIENT for this run (useful in testing)}';

    protected $description = 'Idempotently seed branding settings for the active client. Safe to run on every deploy.';

    public function handle(): int
    {
        // Note: --client only affects the branding_seed lookup. It does not
        // re-run ClientServiceProvider or swap the active client's bindings.
        $client = $this->option('client') ?? config('app.client', 'drivedesk');
        $seed   = config("clients.{$client}.branding_seed", []);

        if (empty($seed)) {
            $this->warn("No branding_seed found for client [{$client}]. Nothing to do.");
            return self::SUCCESS;
        }

        $this->info("Installing branding for client [{$client}] (parent_id = 1) …");

        $seeded  = [];
        $skipped = [];

        foreach ($seed as $key => $value) {
            // parent_id = 1 is the super-admin row created on first install.
            // Settings are scoped by owner; all global/admin settings live under id 1.
            // 'type' is intentionally null — the settings() helper reads all rows with
            // matching parent_id regardless of type; branding keys are untyped.
            $setting = Setting::firstOrCreate(
                ['name' => $key, 'parent_id' => 1],
                ['value' => $value],
            );

            if ($setting->wasRecentlyCreated) {
                $seeded[] = $key;
            } else {
                $skipped[] = $key;
            }
        }

        if ($seeded) {
            $this->line('  <fg=green>Seeded:</>  ' . implode(', ', $seeded));
        }
        if ($skipped) {
            $this->line('  <fg=yellow>Skipped</> (already set): ' . implode(', ', $skipped));
        }

        $this->info(sprintf(
            'Done — %d seeded, %d already set.',
            count($seeded),
            count($skipped),
        ));

        // settings() caches its row set for five minutes. Without this, branding
        // seeded here stays invisible for the rest of that window to anything
        // that already warmed the cache this run -- one guest request to the
        // storefront is enough -- so a fresh install looks unbranded and the
        // cause is invisible.
        //
        // Pinned to 1 because that is what this command writes, unconditionally
        // (see firstOrCreate above). The no-argument form keys off the acting
        // account, which is only ever 1 here by luck: an invocation with someone
        // authenticated -- Artisan::call from a request, or a test that
        // actingAs() first -- would forget their bucket and leave this one stale.
        flushSettingsCache(1);

        return self::SUCCESS;
    }
}
