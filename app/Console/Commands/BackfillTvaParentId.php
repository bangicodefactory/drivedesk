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
 * **This is not safe to run everywhere, and it is not a migration.** Two
 * separate reviews found ways an unattended version destroys or mis-attributes
 * legal documents. Both guards below exist because of a specific verified
 * failure, not out of caution:
 *
 * 1. **`--apply` is refused while `demo_gateway` is on.** `DemoSeed` lists
 *    `tvas` first in `REFRESHED_TABLES` and clears them with a raw
 *    `DB::table('tvas')->where('parent_id', $owner->id)->delete()` — a hard
 *    delete that bypasses SoftDeletes — for the first owner, nightly at 03:30.
 *    A NULL-owner invoice does not match that filter today and survives.
 *    Stamping it with that owner's id hands it to the next night's wipe.
 * 2. **`booking_id` alone cannot attribute an invoice.** It has no foreign key,
 *    and `TvaSeeder` writes `booking_id => rand(1, 100)` with a NULL
 *    `parent_id`, so such a row joins to whichever booking holds that id.
 *    `idpaiment` cannot separate real rows from seeded ones either — it was
 *    added 2025-08-31, *after* `parent_id`, so the invoices this exists to
 *    repair have it NULL too.
 *
 * There is no signal in the schema that distinguishes a genuine legacy invoice
 * from seeder noise, so this reports by default and lists what it would touch.
 * A human decides, against the database in front of them, before `--apply`.
 */
class BackfillTvaParentId extends Command
{
    protected $signature = 'tva:backfill-parent-id
                            {--apply : Write the changes. Without this the command only reports.}
                            {--list=20 : How many candidate rows to print (0 for none).}';

    protected $description = 'Report (or repair) invoices whose parent_id is NULL, deriving the owner from the booking';

    public function handle(): int
    {
        if (! Schema::hasTable('tvas') || ! Schema::hasTable('bookings')) {
            $this->warn('tvas/bookings not present — nothing to do.');
            return self::SUCCESS;
        }

        $repairableCount = (clone $this->repairable())->count();
        $unattributable = $this->unattributableCount();

        $this->line("Repairable (NULL parent_id, booking has an owner): {$repairableCount}");
        $this->line("Unattributable (NULL parent_id, no owner derivable): {$unattributable}");

        if ($repairableCount > 0) {
            $this->listCandidates();
            $this->reportCollisions();
        }

        if (! $this->option('apply')) {
            $this->newLine();
            $this->info('Report only. Re-run with --apply to write.');
            return self::SUCCESS;
        }

        if (feature('demo_gateway')) {
            $this->newLine();
            $this->error('Refusing to write: demo_gateway is on for this client.');
            $this->line('  demo:seed runs nightly and hard-deletes every tvas row belonging to');
            $this->line('  the first owner (DemoSeed::REFRESHED_TABLES). An invoice with a NULL');
            $this->line('  parent_id is not matched by that delete and survives today — giving');
            $this->line('  it an owner would hand it to the next run. On a demo deployment the');
            $this->line('  NULL rows are TvaSeeder noise anyway, so there is nothing to repair.');
            return self::FAILURE;
        }

        $written = $this->repairable()->update([
            'tvas.parent_id'  => DB::raw('bookings.parent_id'),
            'tvas.updated_at' => now(),
        ]);

        $this->newLine();
        $this->info("Backfilled {$written} invoice(s). {$unattributable} still unattributable.");

        return self::SUCCESS;
    }

    /**
     * Invoices that can be attributed: live, NULL-owner, joined to a booking
     * that has a real owner.
     *
     * `bookings.parent_id` is NOT NULL DEFAULT 0, so 0 means "created outside a
     * request" (seeder, console, import) and attributes nothing. Writing it
     * would take the row out of the `IS NULL` filter that makes re-runs
     * possible — unrepairable afterwards, and invisible in the counts above.
     *
     * Trashed rows are excluded: `DB::table()` bypasses the SoftDeletes scope,
     * and repairing a deleted invoice inflates the report while giving guard 1
     * something else to destroy.
     */
    private function repairable()
    {
        return DB::table('tvas')
            ->join('bookings', 'tvas.booking_id', '=', 'bookings.id')
            ->whereNull('tvas.parent_id')
            ->whereNull('tvas.deleted_at')
            ->where('bookings.parent_id', '>', 0);
    }

    private function unattributableCount(): int
    {
        return DB::table('tvas')
            ->leftJoin('bookings', 'tvas.booking_id', '=', 'bookings.id')
            ->whereNull('tvas.parent_id')
            ->whereNull('tvas.deleted_at')
            ->where(fn ($q) => $q->whereNull('bookings.id')->orWhere('bookings.parent_id', '<=', 0))
            ->count();
    }

    /**
     * The counts alone give an operator nothing to judge. Print enough of each
     * candidate to recognise seeder noise: `TvaSeeder` writes
     * `facture_number` as `INV-XXXXXX<n>` against a random booking id.
     */
    private function listCandidates(): void
    {
        $limit = (int) $this->option('list');
        if ($limit <= 0) {
            return;
        }

        $rows = (clone $this->repairable())
            ->orderBy('tvas.facture_date')
            ->limit($limit)
            ->get(['tvas.id', 'tvas.facture_number', 'tvas.facture_date', 'tvas.booking_id', 'bookings.parent_id as would_become']);

        $this->newLine();
        $this->table(
            ['tva id', 'facture_number', 'facture_date', 'booking_id', 'parent_id would become'],
            $rows->map(fn ($r) => [$r->id, $r->facture_number, $r->facture_date, $r->booking_id, $r->would_become])->all()
        );

        if ($rows->count() < (clone $this->repairable())->count()) {
            $this->line('  (truncated — pass --list=0 to suppress, or a larger number)');
        }
    }

    /**
     * Invoice numbers are sequenced per (parent_id, year), with NULL as its own
     * bucket, so merging a NULL row into a tenant's bucket can duplicate a
     * number there. Two cases, both real:
     *
     *  - against an invoice the tenant already owns; and
     *  - against another row in this same batch — reachable because
     *    `BookingController` seeds its next number from a global
     *    `orderByDesc('id')` rather than the per-(parent_id, year) counter, so
     *    the NULL bucket is not internally unique either.
     *
     * Both sides are limited to live rows: `TvaRenumberService` works
     * `withoutTrashed()`, so a collision against a deleted invoice would send
     * the operator to a tool that cannot see it.
     */
    private function reportCollisions(): void
    {
        $againstExisting = (clone $this->repairable())
            ->join('tvas as existing', function ($join) {
                $join->on('existing.parent_id', '=', 'bookings.parent_id')
                    ->on('existing.facture_number', '=', 'tvas.facture_number')
                    ->on(DB::raw('YEAR(existing.facture_date)'), '=', DB::raw('YEAR(tvas.facture_date)'))
                    ->whereNull('existing.deleted_at');
            })
            ->distinct()
            ->count('tvas.id');

        $withinBatch = (clone $this->repairable())
            ->select(DB::raw('COUNT(*) - COUNT(DISTINCT tvas.facture_number) as dupes'))
            ->groupBy('bookings.parent_id', DB::raw('YEAR(tvas.facture_date)'))
            ->pluck('dupes')
            ->sum();

        $total = $againstExisting + (int) $withinBatch;

        if ($total > 0) {
            $this->newLine();
            $this->warn("  {$total} of these would collide on facture_number within a tenant and year:");
            $this->warn("    {$againstExisting} against an invoice that tenant already owns");
            $this->warn("    {$withinBatch} against another row in this same batch");
            $this->warn('  Plan a TvaRenumberService run alongside — --apply does not skip them.');
        }
    }
}
