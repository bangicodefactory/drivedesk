<?php

namespace Tests\Feature;

use App\Models\BookingRequest;
use App\Models\Guest;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * `booking-requests:backfill-parent-id` (BAN-327).
 *
 * storeBooking() never assigned parent_id, so every request taken through the
 * public form sits at the column default of 0. New rows are stamped now; this
 * repairs the ones already there.
 *
 * The cases below are the two ways a naive `UPDATE ... JOIN` gets it wrong:
 * attributing a request whose vehicle has no tenant either, and writing on a
 * report-only run.
 */
class BackfillBookingRequestParentIdCommandTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
    }

    /** Planted with the query builder so the fixture can hold parent_id = 0. */
    private function plantRequest(int $vehicleId, int $parentId = 0): int
    {
        return DB::table('booking_requests')->insertGetId([
            'vehicle'    => $vehicleId,
            'driver'     => Guest::factory()->create()->id,
            'status'     => 'pending',
            'parent_id'  => $parentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function tenantOf(int $requestId): int
    {
        return (int) DB::table('booking_requests')->where('id', $requestId)->value('parent_id');
    }

    public function test_it_reports_without_writing_by_default(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $id      = $this->plantRequest($vehicle->id);

        $this->artisan('booking-requests:backfill-parent-id')
            ->assertSuccessful()
            ->expectsOutputToContain('Report only');

        $this->assertSame(0, $this->tenantOf($id), 'a report-only run wrote to the database');
    }

    public function test_apply_takes_the_tenant_from_the_vehicle(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $id      = $this->plantRequest($vehicle->id);

        $this->artisan('booking-requests:backfill-parent-id', ['--apply' => true])
            ->assertSuccessful();

        $this->assertSame($this->owner->id, $this->tenantOf($id));
    }

    /**
     * A vehicle at parent_id = 0 belongs to no tenant either -- seeder or
     * console output. Writing 0 onto the request changes nothing but takes it
     * out of the `= 0` filter that makes a re-run possible, so it would become
     * unrepairable and drop out of the counts.
     */
    public function test_it_leaves_a_request_whose_vehicle_has_no_tenant(): void
    {
        $orphanVehicle = Vehicle::factory()->create(['parent_id' => 0]);
        $id            = $this->plantRequest($orphanVehicle->id);

        $this->artisan('booking-requests:backfill-parent-id', ['--apply' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('Unattributable');

        $this->assertSame(0, $this->tenantOf($id));
    }

    public function test_it_leaves_a_request_that_already_has_a_tenant(): void
    {
        $otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $vehicle    = Vehicle::factory()->create(['parent_id' => $this->owner->id]);

        // Deliberately mismatched: the row already names a tenant, so the
        // command must not "correct" it to the vehicle's.
        $id = $this->plantRequest($vehicle->id, $otherOwner->id);

        $this->artisan('booking-requests:backfill-parent-id', ['--apply' => true])
            ->assertSuccessful();

        $this->assertSame($otherOwner->id, $this->tenantOf($id));
    }

    public function test_a_second_run_finds_nothing_left_to_do(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $this->plantRequest($vehicle->id);

        $this->artisan('booking-requests:backfill-parent-id', ['--apply' => true])->assertSuccessful();

        $this->artisan('booking-requests:backfill-parent-id')
            ->assertSuccessful()
            ->expectsOutputToContain('Repairable (parent_id 0, vehicle has a tenant): 0');
    }

    /**
     * One deployment holds one business owner, so a split is a signal, not a
     * routine outcome -- the operator is about to change who sees which
     * requests and should be told before writing.
     */
    public function test_it_warns_when_the_rows_span_more_than_one_tenant(): void
    {
        $otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);

        $this->plantRequest(Vehicle::factory()->create(['parent_id' => $this->owner->id])->id);
        $this->plantRequest(Vehicle::factory()->create(['parent_id' => $otherOwner->id])->id);

        $this->artisan('booking-requests:backfill-parent-id')
            ->assertSuccessful()
            ->expectsOutputToContain('split across more than one tenant');
    }

    /**
     * The point of the repair: a request the owner could not see before is
     * theirs afterwards.
     */
    public function test_a_repaired_request_belongs_to_the_owner_afterwards(): void
    {
        $vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        $id      = $this->plantRequest($vehicle->id);

        $this->assertSame(
            0,
            BookingRequest::where('parent_id', $this->owner->id)->where('id', $id)->count()
        );

        $this->artisan('booking-requests:backfill-parent-id', ['--apply' => true])->assertSuccessful();

        $this->assertSame(
            1,
            BookingRequest::where('parent_id', $this->owner->id)->where('id', $id)->count()
        );
    }
}
