<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repair `booking_requests.parent_id` from each request's vehicle (BAN-327).
 *
 * `storeBooking()` never assigned the column, so every request a deployment has
 * taken through the public form sits at its default of 0 and belongs to no
 * tenant. New rows are stamped as of BAN-327; this is for the ones already
 * there.
 *
 * A command rather than a migration, following `tva:backfill-parent-id`: a data
 * repair has no honest `down()`, and an operator should see what it is about to
 * touch on their own database first. It reports by default and writes only on
 * `--apply`.
 *
 * Attribution here is unambiguous in a way the TVA equivalent was not.
 * `booking_requests.vehicle` carries a real foreign key to `vehicles`, and
 * `vehicles.parent_id` is the tenant -- there is no join by a loose id and no
 * seeded noise to tell apart. Two things still make an operator's judgement
 * worth having, so both are reported before anything is written:
 *
 *  - a request whose vehicle sits at `parent_id = 0` itself (created by a
 *    seeder or a console script) attributes nothing and is left alone; and
 *  - on a deployment that somehow holds more than one tenant's vehicles, this
 *    splits the requests between them.
 *
 * Nothing reads the column yet -- `BookingRequest` has no tenant scope and
 * every read path is unscoped -- so applying this changes no screen today. It
 * is the precondition for scoping `index()`/`show()`, which cannot ship until
 * the rows carry a tenant, because scoping first would empty the customer's
 * list instead of narrowing it.
 */
class BackfillBookingRequestParentId extends Command
{
    protected $signature = 'booking-requests:backfill-parent-id
                            {--apply : Write the changes. Without this the command only reports.}
                            {--list=20 : How many candidate rows to print (0 for none).}';

    protected $description = 'Report (or repair) booking requests whose parent_id is 0, deriving the tenant from the vehicle';

    public function handle(): int
    {
        if (! Schema::hasTable('booking_requests') || ! Schema::hasTable('vehicles')) {
            $this->warn('booking_requests/vehicles not present — nothing to do.');

            return self::SUCCESS;
        }

        $repairable     = (clone $this->repairable())->count();
        $unattributable = $this->unattributableCount();

        $this->line("Repairable (parent_id 0, vehicle has a tenant): {$repairable}");
        $this->line("Unattributable (parent_id 0, no tenant derivable): {$unattributable}");

        if ($repairable > 0) {
            $this->listCandidates();
            $this->reportTenantSpread();
        }

        if (! $this->option('apply')) {
            $this->newLine();
            $this->info('Report only. Re-run with --apply to write.');

            return self::SUCCESS;
        }

        $written = $this->repairable()->update([
            'booking_requests.parent_id'  => DB::raw('vehicles.parent_id'),
            'booking_requests.updated_at' => now(),
        ]);

        $this->newLine();
        $this->info("Backfilled {$written} booking request(s). {$unattributable} still unattributable.");

        return self::SUCCESS;
    }

    /**
     * Requests that can be attributed: parent_id still 0, joined to a vehicle
     * that has a real tenant.
     *
     * `vehicles.parent_id` of 0 means "created outside a request" and
     * attributes nothing. Writing it would take the row out of the `= 0` filter
     * that makes a re-run possible, so it would become unrepairable and
     * invisible in the counts above.
     */
    private function repairable()
    {
        return DB::table('booking_requests')
            ->join('vehicles', 'booking_requests.vehicle', '=', 'vehicles.id')
            ->where('booking_requests.parent_id', 0)
            ->where('vehicles.parent_id', '>', 0);
    }

    private function unattributableCount(): int
    {
        return DB::table('booking_requests')
            ->leftJoin('vehicles', 'booking_requests.vehicle', '=', 'vehicles.id')
            ->where('booking_requests.parent_id', 0)
            ->where(fn ($q) => $q->whereNull('vehicles.id')->orWhere('vehicles.parent_id', '<=', 0))
            ->count();
    }

    /**
     * The counts alone give an operator nothing to judge. Print enough of each
     * candidate — who asked, for which car, when — to recognise it.
     */
    private function listCandidates(): void
    {
        $limit = (int) $this->option('list');
        if ($limit <= 0) {
            return;
        }

        $rows = (clone $this->repairable())
            ->leftJoin('guests', 'booking_requests.driver', '=', 'guests.id')
            ->orderBy('booking_requests.created_at')
            ->limit($limit)
            ->get([
                'booking_requests.id',
                'booking_requests.created_at',
                'booking_requests.status',
                'guests.name as guest',
                'vehicles.name as vehicle',
                'vehicles.parent_id as would_become',
            ]);

        $this->newLine();
        $this->table(
            ['id', 'created', 'status', 'guest', 'vehicle', 'parent_id would become'],
            $rows->map(fn ($r) => [$r->id, $r->created_at, $r->status, $r->guest, $r->vehicle, $r->would_become])->all()
        );

        if ($rows->count() < (clone $this->repairable())->count()) {
            $this->line('  (truncated — pass --list=0 to suppress, or a larger number)');
        }
    }

    /**
     * DriveDesk ships one deployment per business owner, so these should all
     * land on one tenant. More than one means either a second owner was created
     * (CLAUDE.md §10.1 — `UserController@store` allows it, so it is a
     * convention, not a constraint) or vehicles were imported across tenants.
     *
     * No screen changes today: nothing reads this column yet. It matters when
     * the read paths are scoped, at which point these rows stop being one list
     * and become two -- so the operator should establish now whether that split
     * is the truth about their deployment, while the rows are still all visible.
     */
    private function reportTenantSpread(): void
    {
        $tenants = (clone $this->repairable())
            ->select('vehicles.parent_id', DB::raw('COUNT(*) as total'))
            ->groupBy('vehicles.parent_id')
            ->pluck('total', 'vehicles.parent_id');

        if ($tenants->count() > 1) {
            $this->newLine();
            $this->warn('  These requests would be split across more than one tenant:');
            foreach ($tenants as $parentId => $total) {
                $this->warn("    parent_id {$parentId}: {$total} request(s)");
            }
            $this->warn('  No screen changes today -- nothing reads this column yet. It matters');
            $this->warn('  once the listing is tenant-scoped, when these stop being one list.');
            $this->warn('  Confirm the split is the truth about this deployment before applying.');
        }
    }
}
