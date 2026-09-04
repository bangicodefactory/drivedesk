<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repair `tvas.parent_id` from each invoice's booking (roadmap S.1 follow-up).
 *
 * `parent_id` was added nullable on 2025-07-11 to a table created 2025-02-04
 * and nothing backfilled it, so invoices issued in between carry NULL and match
 * no tenant. Once `Tva` gained a tenant scope, those rows fell out of every
 * scoped query — which is why seven call sites are pinned with
 * `acrossTenants()`.
 *
 * **Why this is a command and not a migration.** The first attempt (BAN-301)
 * was a migration that ran automatically and derived the owner from
 * `tvas.booking_id`. Review found that unsafe, and it is worth writing down why,
 * because the reasoning applies to any future attempt:
 *
 * - `tvas.booking_id` has no foreign key, and `TvaSeeder` writes
 *   `booking_id => rand(1, 100)` with a NULL `parent_id`. Those rows join to
 *   whichever booking happens to hold that id, so an automatic backfill stamps
 *   an unrelated tenant's ownership onto an invoice — mis-attributing a legal
 *   document, the exact outcome the migration's own docblock promised it
 *   avoided.
 * - `demo:seed --if-demo` runs on **every** deploy and calls that seeder, so a
 *   migration would meet fresh unattributable rows on each release.
 * - `idpaiment` cannot be used to tell real rows from seeded ones: it was added
 *   2025-08-31, *after* `parent_id`, so the legacy invoices this exists to
 *   repair have it NULL too.
 *
 * There is therefore no signal in the schema that separates a genuine legacy
 * invoice from seeder noise. So this does not guess: it **reports by default**
 * and only writes when a human passes `--apply`, having read the report against
 * the database in front of them.
 *
 * Safe to run anywhere. Idempotent. Never writes `parent_id = 0`.
 */
class BackfillTvaParentId extends Command
{
    protected $signature = 'tva:backfill-parent-id
                            {--apply : Write the changes. Without this the command only reports.}';

    protected $description = 'Report (or repair) invoices whose parent_id is NULL, deriving the owner from the booking';

    public function handle(): int
    {
        if (! Schema::hasTable('tvas') || ! Schema::hasTable('bookings')) {
            $this->warn('tvas/bookings not present — nothing to do.');
            return self::SUCCESS;
        }

        // Only a booking with a real owner can attribute an invoice.
        // bookings.parent_id is NOT NULL DEFAULT 0, so 0 means "created outside
        // a request" (seeder, console, import) and attributes nothing — writing
        // it would take the row out of both this report and the IS NULL filter
        // that makes re-runs possible, i.e. it could never be repaired again.
        $repairable = DB::table('tvas')
            ->join('bookings', 'tvas.booking_id', '=', 'bookings.id')
            ->whereNull('tvas.parent_id')
            ->where('bookings.parent_id', '>', 0);

        $repairableCount = (clone $repairable)->count();

        $unattributable = DB::table('tvas')
            ->leftJoin('bookings', 'tvas.booking_id', '=', 'bookings.id')
            ->whereNull('tvas.parent_id')
            ->where(function ($q) {
                $q->whereNull('bookings.id')->orWhere('bookings.parent_id', '<=', 0);
            })
            ->count();

        $this->line("Invoices with a NULL parent_id and a booking that has an owner: {$repairableCount}");
        $this->line("Invoices with a NULL parent_id that cannot be attributed:      {$unattributable}");

        if ($repairableCount > 0) {
            $this->newLine();
            $this->warn('Read this before --apply:');
            $this->line('  tvas.booking_id has no foreign key, and TvaSeeder writes random');
            $this->line('  booking ids with a NULL parent_id. Confirm the rows above are real');
            $this->line('  invoices and not seeded demo data before writing.');

            $this->reportNumberingCollisions($repairable);
        }

        if (! $this->option('apply')) {
            $this->newLine();
            $this->info('Report only. Re-run with --apply to write.');
            return self::SUCCESS;
        }

        $written = $repairable->update(['tvas.parent_id' => DB::raw('bookings.parent_id')]);

        $this->newLine();
        $this->info("Backfilled {$written} invoice(s). {$unattributable} still unattributable.");

        return self::SUCCESS;
    }

    /**
     * Invoice numbers are sequenced per (parent_id, year), with NULL as its own
     * bucket. Merging a NULL row into a tenant's bucket can therefore surface a
     * facture_number that already exists there — visible in the Factures list,
     * and enough to block the planned unique index. Report it rather than
     * discover it afterwards; TvaRenumberService is the tool that resolves it.
     */
    private function reportNumberingCollisions($repairable): void
    {
        $collisions = (clone $repairable)
            ->join('tvas as existing', function ($join) {
                $join->on('existing.parent_id', '=', 'bookings.parent_id')
                    ->on('existing.facture_number', '=', 'tvas.facture_number')
                    ->on(DB::raw('YEAR(existing.facture_date)'), '=', DB::raw('YEAR(tvas.facture_date)'));
            })
            ->distinct()
            ->count('tvas.id');

        if ($collisions > 0) {
            $this->newLine();
            $this->warn("  {$collisions} of them would collide with a facture_number that tenant");
            $this->warn('  already uses that year. Plan a TvaRenumberService run alongside.');
        }
    }
}
