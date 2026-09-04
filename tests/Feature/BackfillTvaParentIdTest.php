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
 * The tvas.parent_id backfill (roadmap S.1 follow-up).
 *
 * The migration itself has already run by the time a test boots, and a fresh
 * schema has no NULL rows to repair, so the class is loaded and up() invoked
 * directly against rows planted for the purpose. Rows are written with the
 * query builder rather than the model: Tva is tenant-scoped now, and these
 * fixtures deliberately model pre-scope data.
 */
class BackfillTvaParentIdTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    private function migration(): object
    {
        return require database_path('migrations/2026_09_04_120000_backfill_parent_id_on_tvas_table.php');
    }

    private function plantInvoice(?int $bookingId, ?int $parentId = null): int
    {
        return DB::table('tvas')->insertGetId([
            'parent_id'      => $parentId,
            'booking_id'     => $bookingId,
            'facture_number' => '1',
            'facture_date'   => '2025-03-01',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }

    public function test_it_takes_the_owner_from_the_invoices_booking(): void
    {
        $this->asClient('acme');

        $owner   = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $booking = Booking::factory()->create(['parent_id' => $owner->id]);
        $id      = $this->plantInvoice($booking->id);

        ob_start();
        $this->migration()->up();
        ob_end_clean();

        $this->assertSame(
            $owner->id,
            (int) DB::table('tvas')->where('id', $id)->value('parent_id')
        );
    }

    public function test_it_leaves_an_invoice_with_no_booking_alone(): void
    {
        $this->asClient('acme');

        $id = $this->plantInvoice(null);

        ob_start();
        $this->migration()->up();
        ob_end_clean();

        // Nothing to derive an owner from; guessing one would mis-attribute a
        // legal document, so it stays NULL and the acrossTenants() pins keep
        // covering it.
        $this->assertNull(DB::table('tvas')->where('id', $id)->value('parent_id'));
    }

    public function test_it_does_not_touch_an_invoice_that_already_has_an_owner(): void
    {
        $this->asClient('acme');

        $owner       = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $otherOwner  = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $booking     = Booking::factory()->create(['parent_id' => $owner->id]);

        // Disagrees with its booking on purpose: the backfill must not "correct" it.
        $id = $this->plantInvoice($booking->id, $otherOwner->id);

        ob_start();
        $this->migration()->up();
        ob_end_clean();

        $this->assertSame(
            $otherOwner->id,
            (int) DB::table('tvas')->where('id', $id)->value('parent_id')
        );
    }

    public function test_it_is_idempotent(): void
    {
        $this->asClient('acme');

        $owner   = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $booking = Booking::factory()->create(['parent_id' => $owner->id]);
        $id      = $this->plantInvoice($booking->id);

        ob_start();
        $this->migration()->up();
        $this->migration()->up();
        ob_end_clean();

        $this->assertSame(
            $owner->id,
            (int) DB::table('tvas')->where('id', $id)->value('parent_id')
        );
    }

    /**
     * The point of the exercise: once backfilled, the row is visible to the
     * tenant scope that BAN-298 added — which is what would eventually let the
     * acrossTenants() pins be removed.
     */
    public function test_a_backfilled_invoice_becomes_visible_to_its_tenant(): void
    {
        $this->asClient('acme');

        $owner   = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $booking = Booking::factory()->create(['parent_id' => $owner->id]);
        $id      = $this->plantInvoice($booking->id);

        $this->actingAs($owner);
        $this->assertNull(Tva::find($id), 'a NULL parent_id invoice should be invisible before the backfill');

        ob_start();
        $this->migration()->up();
        ob_end_clean();

        $this->assertNotNull(Tva::find($id), 'the backfilled invoice should be visible to its own tenant');
    }
}
