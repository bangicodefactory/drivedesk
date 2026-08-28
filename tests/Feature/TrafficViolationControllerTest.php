<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\TrafficViolation;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

/**
 * Traffic violation CRUD (BAN-260) — auth, permissions, tenancy, validation,
 * the feature flag, and the auto-match that runs on write.
 */
class TrafficViolationControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    private const PERMISSIONS = [
        'manage traffic violation',
        'create traffic violation',
        'edit traffic violation',
        'delete traffic violation',
    ];

    protected User $owner;

    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');
        // The module is what is under test, not any client's configuration.
        // directonderweg turned the flag off (2026-08-10); these suites assert the
        // feature itself, so they force it on rather than inheriting a client's.
        config(['client.features.traffic_violations' => true]);

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo(self::PERMISSIONS);

        $this->vehicle = Vehicle::factory()->create([
            'parent_id'     => $this->owner->id,
            'license_plate' => '12345 A 6',
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'reference'     => 'PV-001',
            'authority'     => 'Police',
            'license_plate' => '12345 A 6',
            'occurred_date' => '2026-06-03',
            'occurred_time' => '14:32',
            'location'      => 'Avenue Hassan II',
            'description'   => 'Excès de vitesse',
            'amount'        => '400',
        ], $overrides);
    }

    private function bookingCovering(): Booking
    {
        return Booking::factory()->create([
            'parent_id'  => $this->owner->id,
            'vehicle'    => $this->vehicle->id,
            'driver'     => User::factory()->create(['parent_id' => $this->owner->id, 'type' => 'driver'])->id,
            'start_date' => '2026-06-01',
            'start_time' => '09:00:00',
            'end_date'   => '2026-06-05',
            'end_time'   => '18:00:00',
            'status'     => 'completed',
        ]);
    }

    private function violation(array $overrides = []): TrafficViolation
    {
        return TrafficViolation::factory()->create(array_merge([
            'parent_id'     => $this->owner->id,
            'license_plate' => '12345 A 6',
            'occurred_at'   => '2026-06-03 14:32:00',
        ], $overrides));
    }

    // ── Authentication ───────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('traffic-violation.index'))->assertRedirect(route('login'));
    }

    public function test_store_requires_auth(): void
    {
        $this->post(route('traffic-violation.store'), $this->validPayload())
            ->assertRedirect(route('login'));
    }

    public function test_destroy_requires_auth(): void
    {
        $violation = $this->violation();

        $this->delete(route('traffic-violation.destroy', $violation->id))
            ->assertRedirect(route('login'));
    }

    // ── Permissions ──────────────────────────────────────────────────────────

    public function test_index_denied_without_permission(): void
    {
        $employee = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($employee)
            ->get(route('traffic-violation.index'))
            ->assertSessionHas('error');
    }

    public function test_store_denied_without_permission(): void
    {
        $employee = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($employee)
            ->post(route('traffic-violation.store'), $this->validPayload())
            ->assertSessionHas('error');

        $this->assertDatabaseCount('traffic_violations', 0);
    }

    public function test_destroy_denied_without_permission(): void
    {
        $employee  = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $violation = $this->violation();

        $this->actingAs($employee)
            ->delete(route('traffic-violation.destroy', $violation->id))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('traffic_violations', ['id' => $violation->id]);
    }

    // ── Feature flag ─────────────────────────────────────────────────────────

    public function test_a_client_with_the_flag_off_gets_a_404_not_a_half_rendered_page(): void
    {
        // The *outcome* of the flag being off, for any client. Which clients
        // actually have it off is ClientFeatureMatrixTest's job; this is about
        // what a user hits.
        //
        // Forced rather than inherited from a real client: a test that borrows a
        // live client's `false` changes meaning the day that client flips it.
        // This is the client-config layer specifically — the sibling test below
        // covers the .env override, which returns from feature() before the
        // client layer is ever read (helper.php:838).
        config(['client.features.traffic_violations' => false]);

        $this->actingAs($this->owner)
            ->get(route('traffic-violation.index'))
            ->assertNotFound();
    }

    public function test_module_is_404_when_the_feature_is_off(): void
    {
        config(['features.traffic_violations' => false]);

        $this->actingAs($this->owner)
            ->get(route('traffic-violation.index'))
            ->assertNotFound();
    }

    // ── Index ────────────────────────────────────────────────────────────────

    public function test_index_renders_the_inertia_page(): void
    {
        $this->violation();

        $this->actingAs($this->owner)
            ->get(route('traffic-violation.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('TrafficViolation/Index')
                ->where('violations.current_page', 1)
                ->has('violations.data', 1)
                ->has('violations.last_page')
                ->has('violations.total')
                ->has('statuses')
                ->has('unmatchedCount'));
    }

    public function test_index_only_shows_the_current_tenants_violations(): void
    {
        $this->violation(['reference' => 'MINE']);
        $this->violation(['parent_id' => 9999, 'reference' => 'THEIRS']);

        $this->actingAs($this->owner)
            ->get(route('traffic-violation.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('violations.data', 1)
                ->where('violations.data.0.reference', 'MINE'));
    }

    public function test_index_filters_to_the_unmatched_queue(): void
    {
        $booking = $this->bookingCovering();
        $this->violation(['reference' => 'MATCHED', 'booking_id' => $booking->id]);
        $this->violation(['reference' => 'UNMATCHED', 'booking_id' => null]);

        $this->actingAs($this->owner)
            ->get(route('traffic-violation.index', ['confidence' => 'unmatched']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('violations.data', 1)
                ->where('violations.data.0.reference', 'UNMATCHED'));
    }

    public function test_index_search_matches_the_plate(): void
    {
        $this->violation(['reference' => 'PV-A', 'license_plate' => '12345 A 6']);
        $this->violation(['reference' => 'PV-B', 'license_plate' => '99999 Z 9']);

        $this->actingAs($this->owner)
            ->get(route('traffic-violation.index', ['search' => '99999']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('violations.data', 1)
                ->where('violations.data.0.reference', 'PV-B'));
    }

    // ── Store ────────────────────────────────────────────────────────────────

    public function test_store_creates_a_violation_and_auto_matches_the_renter(): void
    {
        $booking = $this->bookingCovering();

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.store'), $this->validPayload())
            ->assertSessionHas('success');

        $this->assertDatabaseHas('traffic_violations', [
            'reference'        => 'PV-001',
            'parent_id'        => $this->owner->id,
            'vehicle_id'       => $this->vehicle->id,
            'booking_id'       => $booking->id,
            'driver_user_id'   => $booking->getAttributes()['driver'],
            'match_confidence' => 'exact',
            'match_source'     => 'auto',
            'status'           => 'new',
        ]);
    }

    public function test_occurred_at_serializes_without_a_timezone_shift(): void
    {
        // The JSX splits occurred_at with slice(11,16) to fill the edit form and
        // render the list. That is only correct while app.timezone leaves the
        // instant unshifted. If a client ever sets TIMEZONE=Africa/Casablanca,
        // this fails here rather than silently displaying every fine an hour off.
        $this->actingAs($this->owner)
            ->post(route('traffic-violation.store'), $this->validPayload());

        $json = json_decode(TrafficViolation::first()->toJson(), true);

        $this->assertStringStartsWith('2026-06-03T14:32:00', $json['occurred_at']);
    }

    public function test_store_combines_the_date_and_time_into_the_instant(): void
    {
        $this->actingAs($this->owner)
            ->post(route('traffic-violation.store'), $this->validPayload());

        $this->assertSame(
            '2026-06-03 14:32:00',
            TrafficViolation::first()->occurred_at->format('Y-m-d H:i:s')
        );
    }

    public function test_store_records_no_match_when_no_rental_covers_the_instant(): void
    {
        $this->actingAs($this->owner)
            ->post(route('traffic-violation.store'), $this->validPayload())
            ->assertSessionHas('success');

        $this->assertDatabaseHas('traffic_violations', [
            'reference'        => 'PV-001',
            'booking_id'       => null,
            'driver_user_id'   => null,
            'match_confidence' => 'none',
        ]);
    }

    public function test_store_normalizes_a_blank_reference_to_null(): void
    {
        // '' would occupy the (parent_id, reference) unique index and reject
        // the next hand-entered violation.
        $this->actingAs($this->owner)
            ->post(route('traffic-violation.store'), $this->validPayload(['reference' => '']));
        $this->actingAs($this->owner)
            ->post(route('traffic-violation.store'), $this->validPayload(['reference' => '']));

        $this->assertSame(2, TrafficViolation::whereNull('reference')->count());
    }

    public function test_store_rejects_a_duplicate_reference_with_a_message(): void
    {
        $this->violation(['reference' => 'PV-001']);

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.store'), $this->validPayload(['reference' => 'PV-001']))
            ->assertSessionHas('error');

        $this->assertSame(1, TrafficViolation::where('reference', 'PV-001')->count());
    }

    public function test_store_requires_the_plate_and_the_instant(): void
    {
        foreach (['license_plate', 'occurred_date', 'occurred_time'] as $field) {
            $this->actingAs($this->owner)
                ->post(route('traffic-violation.store'), $this->validPayload([$field => '']))
                ->assertSessionHas('error');
        }

        $this->assertDatabaseCount('traffic_violations', 0);
    }

    public function test_store_rejects_a_malformed_time_instead_of_fataling(): void
    {
        // occurredAt() feeds this to Carbon::parse, which throws on garbage.
        // <input type="time"> constrains the form; the endpoint cannot assume one.
        foreach (['abc', '25:99', '14', '14:32:99'] as $time) {
            $this->actingAs($this->owner)
                ->post(route('traffic-violation.store'), $this->validPayload(['occurred_time' => $time]))
                ->assertSessionHas('error');
        }

        $this->assertDatabaseCount('traffic_violations', 0);
    }

    public function test_update_rejects_a_malformed_time(): void
    {
        $violation = $this->violation();

        $this->actingAs($this->owner)
            ->put(route('traffic-violation.update', $violation->id), $this->validPayload(['occurred_time' => 'abc']))
            ->assertSessionHas('error');
    }

    public function test_store_accepts_a_time_with_seconds(): void
    {
        $this->actingAs($this->owner)
            ->post(route('traffic-violation.store'), $this->validPayload(['occurred_time' => '14:32:10']))
            ->assertSessionHas('success');

        $this->assertSame(
            '2026-06-03 14:32:10',
            TrafficViolation::first()->occurred_at->format('Y-m-d H:i:s')
        );
    }

    public function test_store_rejects_a_dangerous_document_upload(): void
    {
        Storage::fake('public');

        // The scan is served from our own origin, so .html/.svg would be stored XSS.
        foreach (['payload.html', 'payload.svg', 'shell.php'] as $name) {
            $this->actingAs($this->owner)->post(
                route('traffic-violation.store'),
                $this->validPayload(['document' => UploadedFile::fake()->create($name, 10)])
            )->assertSessionHas('error');
        }

        $this->assertDatabaseCount('traffic_violations', 0);
    }

    public function test_uploaded_notice_is_stored_under_a_content_derived_extension(): void
    {
        Storage::fake('public');

        // Genuine PNG bytes under an .html client name — UploadedFile::fake()
        // keys its generator off the extension, so it cannot express this.
        // `mimes:` validates content (guessExtension) and passes; keeping the
        // client extension for storage would serve it as text/html from our own
        // origin, and a PNG carrying <script> in a comment chunk is stored XSS.
        $source = storage_path('app/'.uniqid('polyglot_').'.png');
        imagepng(imagecreatetruecolor(10, 10), $source);

        $file = new UploadedFile($source, 'payload.html', null, null, true);

        $this->actingAs($this->owner)
            ->post(route('traffic-violation.store'), $this->validPayload(['document' => $file]))
            ->assertSessionHas('success');

        $document = TrafficViolation::first()->document;

        $this->assertStringEndsNotWith('.html', $document);
        $this->assertMatchesRegularExpression('/\.(png|jpg|jpeg)$/', $document);
    }

    public function test_uploaded_notice_name_is_slugged(): void
    {
        Storage::fake('public');

        $this->actingAs($this->owner)->post(
            route('traffic-violation.store'),
            $this->validPayload(['document' => UploadedFile::fake()->image('../../étrange nom!.png')])
        );

        $document = TrafficViolation::first()->document;

        $this->assertStringNotContainsString('/', $document);
        $this->assertStringNotContainsString(' ', $document);
        $this->assertStringNotContainsString('!', $document);
    }

    public function test_store_rejects_a_non_numeric_amount(): void
    {
        $this->actingAs($this->owner)
            ->post(route('traffic-violation.store'), $this->validPayload(['amount' => 'abc']))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('traffic_violations', 0);
    }

    public function test_store_saves_an_uploaded_notice(): void
    {
        Storage::fake('public');

        $this->actingAs($this->owner)->post(
            route('traffic-violation.store'),
            $this->validPayload(['document' => UploadedFile::fake()->create('notice.pdf', 20, 'application/pdf')])
        );

        $this->assertNotNull(TrafficViolation::first()->document);
    }

    // ── Show ─────────────────────────────────────────────────────────────────

    public function test_show_renders_the_page_with_candidates(): void
    {
        $booking   = $this->bookingCovering();
        $violation = $this->violation(['booking_id' => $booking->id]);

        $this->actingAs($this->owner)
            ->get(route('traffic-violation.show', $violation->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('TrafficViolation/Show')
                ->where('violation.id', $violation->id)
                ->has('candidates', 1)
                ->where('candidates.0.booking_id', $booking->id)
                ->where('candidates.0.is_current', true)
                ->where('candidates.0.reason', 'within_window'));
    }

    public function test_show_refuses_another_tenants_violation(): void
    {
        $violation = $this->violation(['parent_id' => 9999]);

        $this->actingAs($this->owner)
            ->get(route('traffic-violation.show', $violation->id))
            ->assertRedirect(route('traffic-violation.index'));
    }

    // ── Update ───────────────────────────────────────────────────────────────

    public function test_update_persists_changes(): void
    {
        $violation = $this->violation(['reference' => 'PV-OLD']);

        $this->actingAs($this->owner)
            ->put(route('traffic-violation.update', $violation->id), $this->validPayload(['reference' => 'PV-NEW']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('traffic_violations', [
            'id'        => $violation->id,
            'reference' => 'PV-NEW',
        ]);
    }

    public function test_update_rematches_when_the_instant_moves(): void
    {
        $booking   = $this->bookingCovering();
        $violation = $this->violation(['occurred_at' => '2026-07-20 10:00:00']);

        $this->assertNull($violation->booking_id);

        $this->actingAs($this->owner)->put(
            route('traffic-violation.update', $violation->id),
            $this->validPayload(['occurred_date' => '2026-06-03', 'occurred_time' => '14:32'])
        );

        $this->assertSame($booking->id, (int) $violation->fresh()->booking_id);
    }

    public function test_update_keeps_a_manual_assignment(): void
    {
        $booking   = $this->bookingCovering();
        $violation = $this->violation([
            'occurred_at'  => '2026-07-20 10:00:00',
            'booking_id'   => $booking->id,
            'match_source' => 'manual',
        ]);

        // Moving the instant far away would normally clear the match; a human
        // decision outranks the matcher.
        $this->actingAs($this->owner)->put(
            route('traffic-violation.update', $violation->id),
            $this->validPayload(['occurred_date' => '2026-09-01', 'occurred_time' => '08:00'])
        );

        $fresh = $violation->fresh();
        $this->assertSame($booking->id, (int) $fresh->booking_id);
        $this->assertSame('manual', $fresh->match_source);
    }

    public function test_update_rebinds_the_vehicle_when_a_manual_matchs_plate_changes(): void
    {
        $booking = $this->bookingCovering();
        $other   = Vehicle::factory()->create([
            'parent_id'     => $this->owner->id,
            'license_plate' => '99999 Z 9',
        ]);

        $violation = $this->violation([
            'booking_id'   => $booking->id,
            'vehicle_id'   => $this->vehicle->id,
            'match_source' => 'manual',
        ]);

        $this->actingAs($this->owner)->put(
            route('traffic-violation.update', $violation->id),
            $this->validPayload(['license_plate' => '99999 Z 9'])
        );

        $fresh = $violation->fresh();

        // The pinned rental survives — a human decided it.
        $this->assertSame($booking->id, (int) $fresh->booking_id);
        $this->assertSame('manual', $fresh->match_source);

        // But the vehicle is a fact about the plate, not a decision. Leaving the
        // old id here made the page contradict itself.
        $this->assertSame($other->id, (int) $fresh->vehicle_id);
    }

    public function test_update_clears_the_vehicle_when_a_manual_matchs_plate_becomes_unknown(): void
    {
        $booking   = $this->bookingCovering();
        $violation = $this->violation([
            'booking_id'   => $booking->id,
            'vehicle_id'   => $this->vehicle->id,
            'match_source' => 'manual',
        ]);

        $this->actingAs($this->owner)->put(
            route('traffic-violation.update', $violation->id),
            $this->validPayload(['license_plate' => 'NOT-IN-FLEET'])
        );

        $this->assertNull($violation->fresh()->vehicle_id);
    }

    public function test_update_denied_without_permission(): void
    {
        $employee  = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $violation = $this->violation(['reference' => 'PV-KEEP']);

        $this->actingAs($employee)
            ->put(route('traffic-violation.update', $violation->id), $this->validPayload(['reference' => 'PV-HACK']))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('traffic_violations', ['id' => $violation->id, 'reference' => 'PV-KEEP']);
    }

    public function test_update_refuses_another_tenants_violation(): void
    {
        $violation = $this->violation(['parent_id' => 9999, 'reference' => 'THEIRS']);

        $this->actingAs($this->owner)
            ->put(route('traffic-violation.update', $violation->id), $this->validPayload(['reference' => 'MINE']))
            ->assertRedirect(route('traffic-violation.index'));

        $this->assertDatabaseHas('traffic_violations', ['id' => $violation->id, 'reference' => 'THEIRS']);
    }

    // ── Destroy ──────────────────────────────────────────────────────────────

    public function test_destroy_deletes_the_violation(): void
    {
        $violation = $this->violation();

        $this->actingAs($this->owner)
            ->delete(route('traffic-violation.destroy', $violation->id))
            ->assertRedirect(route('traffic-violation.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('traffic_violations', ['id' => $violation->id]);
    }

    public function test_destroy_refuses_another_tenants_violation(): void
    {
        $violation = $this->violation(['parent_id' => 9999]);

        $this->actingAs($this->owner)
            ->delete(route('traffic-violation.destroy', $violation->id))
            ->assertRedirect(route('traffic-violation.index'));

        $this->assertDatabaseHas('traffic_violations', ['id' => $violation->id]);
    }

    // ── Create / Edit pages ──────────────────────────────────────────────────

    public function test_create_renders(): void
    {
        $this->actingAs($this->owner)
            ->get(route('traffic-violation.create'))
            ->assertInertia(fn (Assert $page) => $page->component('TrafficViolation/Create'));
    }

    public function test_edit_renders(): void
    {
        $violation = $this->violation();

        $this->actingAs($this->owner)
            ->get(route('traffic-violation.edit', $violation->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('TrafficViolation/Edit')
                ->where('violation.id', $violation->id));
    }
}
