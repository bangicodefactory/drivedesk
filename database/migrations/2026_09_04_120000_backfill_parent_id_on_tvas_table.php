<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Backfill `tvas.parent_id` from each invoice's booking (roadmap S.1).
 *
 * `parent_id` was added nullable on 2025-07-11 to a table created 2025-02-04
 * and nothing backfilled it, so any invoice issued in between carries NULL and
 * matches no tenant. Once `Tva` gained a tenant scope (BAN-298) those rows fell
 * out of every scoped query, which is why seven call sites had to be pinned
 * with `acrossTenants()` (BAN-299/300) — two numbering paths, three deletes,
 * the renumber service and its year list.
 *
 * This closes the data gap those pins work around. It is deliberately *not*
 * paired with removing them: dropping a pin is a behaviour change per call
 * site and belongs in its own PR, after this has run and been verified against
 * real data.
 *
 * Notes on scope:
 *
 * - **Raw SQL, not Eloquent.** `Tva` now carries a global scope; a migration
 *   runs unauthenticated so it would be inert, but relying on that is exactly
 *   the kind of implicit assumption this tranche kept getting wrong.
 * - **Idempotent.** Only touches `parent_id IS NULL`, so re-running changes
 *   nothing.
 * - **No-op on drivedesk.** Its database was created fresh in 2026-08 with
 *   both migrations already in place (see docs/deploy.md §0), so no NULL rows
 *   can exist. The rows this repairs live in the `directonderweg` database,
 *   which runs from the separate `bangicodefactory/rentcar` repo — this
 *   migration has to be copied there by hand, per CLAUDE.md §10.1.
 * - Invoices with no `booking_id`, or whose booking is gone, keep NULL: there
 *   is nothing to derive an owner from, and guessing one would mis-attribute a
 *   legal document.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tvas') || ! Schema::hasTable('bookings')) {
            return;
        }

        $affected = DB::table('tvas')
            ->join('bookings', 'tvas.booking_id', '=', 'bookings.id')
            ->whereNull('tvas.parent_id')
            ->whereNotNull('bookings.parent_id')
            ->update(['tvas.parent_id' => DB::raw('bookings.parent_id')]);

        $orphans = DB::table('tvas')->whereNull('parent_id')->count();

        // Surfaced in the deploy log: a non-zero orphan count means invoices the
        // scope still cannot see, and the acrossTenants() pins must stay.
        echo "  tvas.parent_id backfilled: {$affected} row(s); "
            . "{$orphans} still NULL (no booking to derive an owner from)\n";
    }

    public function down(): void
    {
        // Intentionally not reversed. The column stays nullable and no schema
        // changes, so rolling back past this migration is safe — but restoring
        // NULLs would have to distinguish the rows written here from those the
        // application set legitimately, which is not recoverable after the fact.
        // Losing a correct owner on a legal document is worse than leaving it.
    }
};
