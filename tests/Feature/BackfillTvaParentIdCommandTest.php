<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Tva;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * `tva:backfill-parent-id` (roadmap S.1 follow-up).
 *
 * Rows are planted with the query builder rather than the model so the fixture
 * can hold a NULL `parent_id`, which is the state being repaired and which the
 * model's own creating hook would fill in.
 *
 * The first four cases are the ones the earlier migration got wrong: it wrote
 * `parent_id = 0` from a seeder booking, and it attributed rows whose
 * `booking_id` is random noise. Both are covered here.
 */
class BackfillTvaParentIdCommandTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    private function plantInvoice(?int $bookingId, ?int $parentId = null, string $factureNumber = '1'): int
    {
        return DB::table('tvas')->insertGetId([
            'parent_id'      => $parentId,
            'booking_id'     => $bookingId,
            'facture_number' => $factureNumber,
            'facture_date'   => '2025-03-01',
            'month'          => 3,
            'year'           => 2025,
            'total_amount'   => 0,
            'tva_amount'     => 0,
            'status'         => 'generated',
            'generated_date' => now(),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    private function ownerOf(int $tvaId): ?int
    {
        $value = DB::table('tvas')->where('id', $tvaId)->value('parent_id');

        return $value === null ? null : (int) $value;
    }

    public function test_it_reports_without_writing_by_default(): void
    {
        $this->asClient('acme');

        $owner   = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $booking = Booking::factory()->create(['parent_id' => $owner->id]);
        $id      = $this->plantInvoice($booking->id);

        $this->artisan('tva:backfill-parent-id')
            ->expectsOutputToContain('Report only')
            ->assertExitCode(0);

        $this->assertNull($this->ownerOf($id), 'the default run must not write');
    }

    public function test_apply_takes_the_owner_from_the_booking(): void
    {
        $this->asClient('acme');

        $owner   = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $booking = Booking::factory()->create(['parent_id' => $owner->id]);
        $id      = $this->plantInvoice($booking->id);

        $this->artisan('tva:backfill-parent-id --apply')->assertExitCode(0);

        $this->assertSame($owner->id, $this->ownerOf($id));
    }

    /**
     * The migration this replaced would have written 0 here. bookings.parent_id
     * is NOT NULL DEFAULT 0, so its whereNotNull guard excluded nothing — and a
     * row written to 0 leaves the IS NULL filter, so it could never be repaired
     * on a later run.
     */
    public function test_apply_skips_a_booking_with_no_owner(): void
    {
        $this->asClient('acme');

        $booking = Booking::factory()->create(['parent_id' => 0]);
        $id      = $this->plantInvoice($booking->id);

        $this->artisan('tva:backfill-parent-id --apply')->assertExitCode(0);

        $this->assertNull($this->ownerOf($id), 'parent_id = 0 attributes nothing and must not be written');
    }

    public function test_apply_skips_an_invoice_whose_booking_does_not_exist(): void
    {
        $this->asClient('acme');

        // TvaSeeder writes booking_id => rand(1, 100) with a NULL parent_id.
        $id = $this->plantInvoice(999999);

        $this->artisan('tva:backfill-parent-id --apply')->assertExitCode(0);

        $this->assertNull($this->ownerOf($id));
    }

    public function test_apply_leaves_an_invoice_that_already_has_an_owner(): void
    {
        $this->asClient('acme');

        $owner      = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $booking    = Booking::factory()->create(['parent_id' => $owner->id]);

        // Disagrees with its booking on purpose: the command must not "correct" it.
        $id = $this->plantInvoice($booking->id, $otherOwner->id);

        $this->artisan('tva:backfill-parent-id --apply')->assertExitCode(0);

        $this->assertSame($otherOwner->id, $this->ownerOf($id));
    }

    public function test_apply_is_idempotent(): void
    {
        $this->asClient('acme');

        $owner   = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $booking = Booking::factory()->create(['parent_id' => $owner->id]);
        $id      = $this->plantInvoice($booking->id);

        $this->artisan('tva:backfill-parent-id --apply')->assertExitCode(0);
        $this->artisan('tva:backfill-parent-id --apply')->assertExitCode(0);

        $this->assertSame($owner->id, $this->ownerOf($id));
    }

    /** Merging a NULL row into a tenant's bucket can duplicate a facture number. */
    public function test_it_warns_when_a_backfill_would_collide_with_an_existing_number(): void
    {
        $this->asClient('acme');

        $owner   = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $booking = Booking::factory()->create(['parent_id' => $owner->id]);

        $this->plantInvoice($booking->id, $owner->id, '7');   // already owned, number 7
        $this->plantInvoice($booking->id, null, '7');          // would become a second 7

        $this->artisan('tva:backfill-parent-id')
            ->expectsOutputToContain('collide')
            ->assertExitCode(0);
    }

    /**
     * The point of the exercise: a repaired row becomes visible to the tenant
     * scope, which is what would eventually let the acrossTenants() pins go.
     */
    public function test_a_repaired_invoice_becomes_visible_to_its_tenant(): void
    {
        $this->asClient('acme');

        $owner   = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $booking = Booking::factory()->create(['parent_id' => $owner->id]);
        $id      = $this->plantInvoice($booking->id);

        $this->actingAs($owner);
        $this->assertNull(Tva::find($id), 'a NULL parent_id invoice should be invisible beforehand');

        $this->artisan('tva:backfill-parent-id --apply')->assertExitCode(0);

        $this->assertNotNull(Tva::find($id));
    }
}
