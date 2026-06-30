<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Driver;
use App\Models\Place;
use App\Models\Tva;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected User $driver;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('directonderweg');

        // Create permissions required by BookingController
        $perms = [
            'manage booking', 'create booking', 'show booking',
            'edit booking', 'delete booking',
            'create booking payment', 'delete booking payment',
        ];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner  = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo($perms);

        $this->driver  = User::factory()->driver()->create(['parent_id' => $this->owner->id]);
        $this->vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeBooking(array $overrides = []): Booking
    {
        return Booking::factory()->create(array_merge([
            'vehicle'        => $this->vehicle->id,
            'driver'         => $this->driver->id,
            'parent_id'      => $this->owner->id,
            'vehicle_details' => [
                'id'            => $this->vehicle->id,
                'name'          => $this->vehicle->name,
                'license_plate' => $this->vehicle->license_plate,
            ],
        ], $overrides));
    }

    // ── unauthenticated ───────────────────────────────────────────────────────

    public function test_index_requires_auth(): void
    {
        $this->get(route('booking.index'))->assertRedirect(route('login'));
    }

    public function test_index_renders_paginated_inertia_component(): void
    {
        $this->makeBooking();

        $this->actingAs($this->owner)
            ->get(route('booking.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Booking/Index')
                ->where('bookings.current_page', 1)
                ->has('bookings.data')
                ->has('bookings.last_page')
                ->has('bookings.total')
            );
    }

    public function test_index_filters_by_month(): void
    {
        $this->makeBooking(['start_date' => '2026-05-10']);
        $this->makeBooking(['start_date' => '2026-05-20']);
        $this->makeBooking(['start_date' => '2026-06-15']);

        $this->actingAs($this->owner)
            ->get(route('booking.index', ['month' => '2026-05']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Booking/Index')
                ->where('filters.month', '2026-05')
                ->where('bookings.total', 2)
            );
    }

    public function test_index_ignores_invalid_month(): void
    {
        $this->makeBooking(['start_date' => '2026-05-10']);
        $this->makeBooking(['start_date' => '2026-06-15']);

        // A malformed month must be ignored (filter not applied), never reach
        // the query builder, and echo back as empty in the filters prop.
        $this->actingAs($this->owner)
            ->get(route('booking.index', ['month' => '2026-13']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.month', '')
                ->where('bookings.total', 2)
            );
    }

    public function test_index_shows_all_bookings_on_one_page_when_month_selected(): void
    {
        // 30 May bookings would span two pages at 25/page; selecting the month
        // returns them all on a single page (page size capped at 300).
        collect(range(1, 30))->each(fn () => $this->makeBooking(['start_date' => '2026-05-10']));
        $this->makeBooking(['start_date' => '2026-06-15']); // other month, excluded

        $this->actingAs($this->owner)
            ->get(route('booking.index', ['month' => '2026-05']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Booking/Index')
                ->where('bookings.total', 30)
                ->where('bookings.per_page', 300) // capped page size, not 25
                ->where('bookings.last_page', 1)
                ->has('bookings.data', 30)
            );
    }

    public function test_index_still_paginates_when_no_month_selected(): void
    {
        // Without a month filter the list keeps the default 25/page pagination.
        collect(range(1, 30))->each(fn () => $this->makeBooking());

        $this->actingAs($this->owner)
            ->get(route('booking.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('bookings.per_page', 25)
                ->where('bookings.last_page', 2)
                ->has('bookings.data', 25)
            );
    }

    public function test_store_requires_auth(): void
    {
        $this->post(route('booking.store'))->assertRedirect(route('login'));
    }

    public function test_show_requires_auth(): void
    {
        $booking = $this->makeBooking();
        $this->get(route('booking.show', Crypt::encrypt($booking->id)))
            ->assertRedirect(route('login'));
    }

    public function test_destroy_requires_auth(): void
    {
        $booking = $this->makeBooking();
        $this->delete(route('booking.destroy', $booking->id))
            ->assertRedirect(route('login'));
    }

    public function test_payment_store_requires_auth(): void
    {
        $booking = $this->makeBooking();
        $this->post(route('booking.payment.store', $booking->id))
            ->assertRedirect(route('login'));
    }

    // ── permission denied ─────────────────────────────────────────────────────

    public function test_store_denied_without_permission(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($noPerms)
            ->post(route('booking.store'), [])
            ->assertSessionHas('error', __('Permission Denied.'));
    }

    public function test_show_denied_without_permission(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $booking = $this->makeBooking();

        $this->actingAs($noPerms)
            ->get(route('booking.show', Crypt::encrypt($booking->id)))
            ->assertSessionHas('error', __('Permission Denied.'));
    }

    // ── BookingController::create / edit form loaders (BAN-239) ──────────────

    public function test_create_renders_inertia_component_with_dropdown_props(): void
    {
        $this->actingAs($this->owner)
            ->get(route('booking.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Booking/Create')
                ->has('vehicles')
                ->has('drivers')
                ->has('places')
                ->has('addons')
                ->has('statuses')
            );
    }

    public function test_edit_renders_inertia_component_with_dropdown_props(): void
    {
        $booking = $this->makeBooking();

        $this->actingAs($this->owner)
            ->get(route('booking.edit', Crypt::encrypt($booking->id)))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Booking/Edit')
                ->has('booking')
                ->has('vehicles')
                ->has('drivers')
                ->has('places')
                ->has('addons')
            );
    }

    public function test_create_returns_all_drivers_newest_first(): void
    {
        // Regression for BAN-266: the picker filters client-side, so the create
        // page must send every driver (not a capped slice that hid older ones),
        // ordered newest-first. created_at isn't fillable, so set it directly
        // (not via mass assignment) to keep the order deterministic.
        $this->driver->name = 'Old One';
        $this->driver->created_at = '2025-01-01 00:00:00';
        $this->driver->save();
        $mid = User::factory()->driver()->create(['parent_id' => $this->owner->id, 'name' => 'Mid One']);
        $mid->created_at = '2025-06-01 00:00:00';
        $mid->save();
        $new = User::factory()->driver()->create(['parent_id' => $this->owner->id, 'name' => 'New One']);
        $new->created_at = '2025-12-01 00:00:00';
        $new->save();

        $this->actingAs($this->owner)
            ->get(route('booking.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('drivers', 3)
                ->where('drivers.0.name', 'New One')  // most recently created first
                ->where('drivers.2.name', 'Old One')
            );
    }

    // ── BookingController::store ──────────────────────────────────────────────

    public function test_store_flashes_error_on_invalid_dates(): void
    {
        $this->actingAs($this->owner)
            ->post(route('booking.store'), [
                'vehicle'          => $this->vehicle->id,
                'start_date_time'  => '2026-06-05 09:00',
                'end_date_time'    => '2026-06-01 09:00', // before start → fails after:
                'driver'           => $this->driver->id,
                // String literals pass the 'string' validator rule; date validation
                // fires first so these never reach the DB insert.
                'pickup_address'   => 'Airport',
                'drop_off_address' => 'Hotel',
                'status'           => 'yet_to_start',
                'amount'           => 300,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_store_creates_booking_and_redirects_to_show(): void
    {
        $pickup  = Place::factory()->create();
        $dropOff = Place::factory()->create();

        $this->actingAs($this->owner)
            ->post(route('booking.store'), [
                'vehicle'          => $this->vehicle->id,
                'start_date_time'  => '2026-06-01 09:00',
                'end_date_time'    => '2026-06-04 18:00',
                'driver'           => $this->driver->id,
                'pickup_address'   => (string) $pickup->id,
                'drop_off_address' => (string) $dropOff->id,
                'status'           => 'yet_to_start',
                'amount'           => 300,
            ])
            ->assertRedirect();

        $booking = Booking::where('vehicle', $this->vehicle->id)
            ->where('driver', $this->driver->id)
            ->first();

        $this->assertNotNull($booking);
        $this->assertEquals('impaye', $booking->payment_status);
        $this->assertEquals($this->owner->id, $booking->parent_id);
    }

    public function test_store_flashes_error_when_required_field_missing(): void
    {
        $this->actingAs($this->owner)
            ->post(route('booking.store'), [
                // missing vehicle, start_date_time, etc.
                'status' => 'yet_to_start',
                'amount' => 300,
            ])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── BookingController::show ───────────────────────────────────────────────

    public function test_show_returns_404_for_other_tenant(): void
    {
        $otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $otherOwner->givePermissionTo('show booking');
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $booking = $this->makeBooking(); // belongs to $this->owner

        $this->actingAs($otherOwner)
            ->get(route('booking.show', Crypt::encrypt($booking->id)))
            ->assertStatus(404);
    }

    public function test_show_returns_200_for_own_booking(): void
    {
        $booking = $this->makeBooking();

        $this->actingAs($this->owner)
            ->get(route('booking.show', Crypt::encrypt($booking->id)))
            ->assertStatus(200);
    }

    // ── BookingController::destroy ────────────────────────────────────────────

    public function test_destroy_deletes_booking_and_associated_tva(): void
    {
        $booking = $this->makeBooking();
        $tva = Tva::factory()->create(['booking_id' => $booking->id]);

        $this->actingAs($this->owner)
            ->delete(route('booking.destroy', $booking->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSoftDeleted('tvas', ['id' => $tva->id]);
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    public function test_destroy_requires_delete_booking_permission(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $booking = $this->makeBooking();

        $this->actingAs($noPerms)
            ->delete(route('booking.destroy', $booking->id))
            ->assertSessionHas('error');
    }

    // ── BookingController::bulkDestroy ────────────────────────────────────────

    public function test_bulk_destroy_deletes_selected_bookings(): void
    {
        $b1 = $this->makeBooking();
        $b2 = $this->makeBooking();

        $this->actingAs($this->owner)
            ->post(route('booking.bulk-destroy'), ['ids' => [$b1->id, $b2->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('bookings', ['id' => $b1->id]);
        $this->assertDatabaseMissing('bookings', ['id' => $b2->id]);
    }

    public function test_bulk_destroy_flashes_error_when_no_ids(): void
    {
        $this->actingAs($this->owner)
            ->post(route('booking.bulk-destroy'), ['ids' => []])
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── BookingController::matchingIds (select-all-across-pages) ──────────────

    public function test_matching_ids_returns_every_id_for_month_across_pages(): void
    {
        // 30 May bookings span two paginated pages (25/page); 5 June bookings
        // must be excluded. matchingIds returns the whole filtered set, the
        // pre-Inertia "select all" reach the page-only checkbox lost.
        $may = collect(range(1, 30))->map(fn () => $this->makeBooking(['start_date' => '2026-05-10'])->id);
        $this->makeBooking(['start_date' => '2026-06-15']);
        $this->makeBooking(['start_date' => '2026-06-16']);
        $this->makeBooking(['start_date' => '2026-06-17']);
        $this->makeBooking(['start_date' => '2026-06-18']);
        $this->makeBooking(['start_date' => '2026-06-19']);

        $res = $this->actingAs($this->owner)
            ->getJson(route('booking.matching-ids', ['month' => '2026-05']))
            ->assertOk()
            ->assertJsonCount(30, 'ids');

        $this->assertEqualsCanonicalizing($may->all(), $res->json('ids'));
    }

    public function test_matching_ids_respects_search_filter(): void
    {
        // Search matches on the booking's driver name (the orWhereHas branch).
        $named = User::factory()->driver()->create(['parent_id' => $this->owner->id, 'name' => 'Zsearchdriver']);
        $hit   = $this->makeBooking(['driver' => $named->id]);
        $this->makeBooking(); // default driver, unrelated name

        $this->actingAs($this->owner)
            ->getJson(route('booking.matching-ids', ['search' => 'Zsearchdriver']))
            ->assertOk()
            ->assertExactJson(['ids' => [$hit->id]]);
    }

    public function test_matching_ids_scoped_to_tenant(): void
    {
        $mine = $this->makeBooking();

        $other = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->makeBooking(['parent_id' => $other->id]);

        $this->actingAs($this->owner)
            ->getJson(route('booking.matching-ids'))
            ->assertOk()
            ->assertExactJson(['ids' => [$mine->id]]);
    }

    public function test_matching_ids_ignores_invalid_month(): void
    {
        // A malformed month must be ignored (no filter), so every booking is
        // returned — mirrors index()'s handling of the same input.
        $this->makeBooking(['start_date' => '2026-05-10']);
        $this->makeBooking(['start_date' => '2026-06-15']);

        $this->actingAs($this->owner)
            ->getJson(route('booking.matching-ids', ['month' => '2026-13']))
            ->assertOk()
            ->assertJsonCount(2, 'ids');
    }

    public function test_matching_ids_denied_without_manage_booking_permission(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($noPerms)
            ->getJson(route('booking.matching-ids'))
            ->assertStatus(403);
    }

    public function test_matching_ids_requires_auth(): void
    {
        $this->get(route('booking.matching-ids'))->assertRedirect(route('login'));
    }

    public function test_bulk_destroy_only_deletes_callers_own_bookings_and_tva(): void
    {
        // Security regression (BAN-268): ids are client-supplied. A crafted list
        // mixing the caller's own booking with another tenant's must delete ONLY
        // the caller's booking + its TVA, never the other tenant's rows.
        $mine   = $this->makeBooking();
        $myTva  = Tva::factory()->create(['booking_id' => $mine->id]);

        $other     = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $theirs    = $this->makeBooking(['parent_id' => $other->id]);
        $theirTva  = Tva::factory()->create(['booking_id' => $theirs->id]);

        $this->actingAs($this->owner)
            ->post(route('booking.bulk-destroy'), ['ids' => [$mine->id, $theirs->id]])
            ->assertRedirect()
            ->assertSessionHas('success');

        // Caller's own rows gone…
        $this->assertDatabaseMissing('bookings', ['id' => $mine->id]);
        $this->assertSoftDeleted('tvas', ['id' => $myTva->id]);

        // …the other tenant's rows untouched.
        $this->assertDatabaseHas('bookings', ['id' => $theirs->id]);
        $this->assertDatabaseHas('tvas', ['id' => $theirTva->id, 'deleted_at' => null]);
    }

    public function test_bulk_destroy_requires_delete_booking_permission(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $booking = $this->makeBooking();

        $this->actingAs($noPerms)
            ->post(route('booking.bulk-destroy'), ['ids' => [$booking->id]])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    // ── BookingController::paymentStore ──────────────────────────────────────

    public function test_payment_store_creates_payment_and_marks_partially_paid(): void
    {
        $booking = $this->makeBooking(['amount' => 600, 'payment_status' => 'impaye']);

        $this->actingAs($this->owner)
            ->post(route('booking.payment.store', $booking->id), [
                'amount'         => 200,
                'date'           => now()->format('Y-m-d'),
                'payment_method' => 'Virement bancaire',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('booking_payments', [
            'booking_id'     => $booking->id,
            'amount'         => 200,
            'payment_method' => 'Virement bancaire',
        ]);

        $this->assertDatabaseHas('bookings', [
            'id'             => $booking->id,
            'payment_status' => 'partiellement_paye',
        ]);
    }

    public function test_payment_store_marks_paid_when_balance_cleared(): void
    {
        $booking = $this->makeBooking(['amount' => 300, 'payment_status' => 'impaye']);

        $this->actingAs($this->owner)
            ->post(route('booking.payment.store', $booking->id), [
                'amount'         => 300,
                'date'           => now()->format('Y-m-d'),
                'payment_method' => 'Carte',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'id'             => $booking->id,
            'payment_status' => 'paye',
        ]);
    }

    public function test_payment_store_rejects_cash_above_5000(): void
    {
        $booking = $this->makeBooking(['amount' => 10000]);

        $this->actingAs($this->owner)
            ->post(route('booking.payment.store', $booking->id), [
                'amount'         => 5001,
                'date'           => now()->format('Y-m-d'),
                'payment_method' => 'Espece',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['amount']);

        $this->assertDatabaseMissing('booking_payments', ['booking_id' => $booking->id]);
    }

    public function test_payment_store_rejects_zero_amount(): void
    {
        $booking = $this->makeBooking();

        $this->actingAs($this->owner)
            ->post(route('booking.payment.store', $booking->id), [
                'amount'         => 0,
                'date'           => now()->format('Y-m-d'),
                'payment_method' => 'Carte',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['amount']);
    }

    public function test_payment_store_requires_create_booking_payment_permission(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $booking = $this->makeBooking();

        $this->actingAs($noPerms)
            ->post(route('booking.payment.store', $booking->id), [
                'amount'         => 100,
                'date'           => now()->format('Y-m-d'),
                'payment_method' => 'Carte',
            ])
            ->assertSessionHas('error', __('Permission Denied.'));
    }

    // ── BookingController::paymentStore — Inertia requests ───────────────────

    public function test_payment_store_inertia_error_returns_redirect_not_json(): void
    {
        $booking = $this->makeBooking(['amount' => 10000]);

        $this->actingAs($this->owner)
            ->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('booking.payment.store', $booking->id), [
                'amount'         => 5001,
                'date'           => now()->format('Y-m-d'),
                'payment_method' => 'Espece',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['amount']);

        $this->assertDatabaseMissing('booking_payments', ['booking_id' => $booking->id]);
    }

    public function test_payment_store_inertia_success_returns_redirect_not_json(): void
    {
        $booking = $this->makeBooking(['amount' => 300]);

        $this->actingAs($this->owner)
            ->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('booking.payment.store', $booking->id), [
                'amount'         => 100,
                'date'           => now()->format('Y-m-d'),
                'payment_method' => 'Carte',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    // ── BookingController::paymentDestroy ─────────────────────────────────────

    public function test_payment_destroy_deletes_payment_and_recalculates_to_impaye(): void
    {
        $booking = $this->makeBooking(['amount' => 300, 'payment_status' => 'partiellement_paye']);
        $payment = BookingPayment::factory()->create([
            'booking_id'     => $booking->id,
            'amount'         => 100,
            'payment_method' => 'Carte',
            'parent_id'      => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->delete(route('booking.payment.destroy', [$booking->id, $payment->id]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('booking_payments', ['id' => $payment->id]);
        $this->assertDatabaseHas('bookings', [
            'id'             => $booking->id,
            'payment_status' => 'impaye',
        ]);
    }

    public function test_payment_destroy_deletes_linked_tva(): void
    {
        $booking = $this->makeBooking(['amount' => 300]);
        $payment = BookingPayment::factory()->create([
            'booking_id' => $booking->id,
            'amount'     => 100,
            'parent_id'  => $this->owner->id,
        ]);
        $tva = Tva::factory()->create([
            'booking_id' => $booking->id,
            'idpaiment'  => $payment->id,
        ]);

        $this->actingAs($this->owner)
            ->delete(route('booking.payment.destroy', [$booking->id, $payment->id]))
            ->assertRedirect();

        $this->assertSoftDeleted('tvas', ['id' => $tva->id]);
    }

    // ── BookingController::planning (BAN-238) ────────────────────────────────

    public function test_planning_returns_200_with_booking_and_vehicle_data(): void
    {
        $this->makeBooking();

        $this->actingAs($this->owner)
            ->get(route('planning'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Booking/Planning')
                ->has('bookingData')
                ->has('vehicleData')
            );
    }

    public function test_planning_denied_without_manage_booking_permission(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);

        $this->actingAs($noPerms)
            ->get(route('planning'))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_planning_fires_at_most_2_booking_related_queries(): void
    {
        // Create 5 bookings — before the fix each triggered a lazy driver load
        foreach (range(1, 5) as $_) {
            $this->makeBooking();
        }

        $bookingQueries = 0;
        DB::listen(function ($query) use (&$bookingQueries) {
            if (preg_match('/\bbookings\b|\busers\b/i', $query->sql)) {
                $bookingQueries++;
            }
        });

        $this->actingAs($this->owner)->get(route('planning'))->assertOk();

        $this->assertLessThanOrEqual(2, $bookingQueries,
            "planning() should fire at most 2 queries for bookings+drivers (fired {$bookingQueries})"
        );
    }

    // ── BookingController::importExcel (BAN-236) ─────────────────────────────

    private function makeImportFile(array $dataRows): UploadedFile
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['NOM & PRENOM', 'DATE DEBUT', 'HEURE', 'LA MARQUE', 'IMMATRICULATION', 'DATE RETOUR', 'HEURE RETOUR', 'PERIODE', 'PRIX', 'METHOD'],
            ...$dataRows,
        ]);
        $path = tempnam(sys_get_temp_dir(), 'import_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        return new UploadedFile($path, 'import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_import_creates_booking_for_new_driver_and_vehicle(): void
    {
        $file = $this->makeImportFile([
            ['John Doe', '2026-06-01', '09:00', 'Toyota', 'AA-123-BB', '2026-06-05', '18:00', '4', '500', 'cash'],
        ]);

        $this->actingAs($this->owner)
            ->post(route('booking.import'), ['file' => $file])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', ['parent_id' => $this->owner->id, 'amount' => 500]);
        $this->assertDatabaseHas('users', ['name' => 'John Doe', 'type' => 'driver', 'parent_id' => $this->owner->id]);
        $this->assertDatabaseHas('vehicles', ['license_plate' => 'AA-123-BB', 'parent_id' => $this->owner->id]);
    }

    public function test_import_reuses_vehicle_when_plate_differs_only_by_nbsp(): void
    {
        // Pre-existing vehicle; the import row's plate carries a leading
        // non-breaking space (as pasted from Excel). It must reuse the vehicle,
        // not create a duplicate (IST-229).
        $this->makeBooking(); // ensures $this->vehicle exists for the owner
        $existing = Vehicle::factory()->create(['parent_id' => $this->owner->id, 'license_plate' => 'NB-555-SP']);
        $before = Vehicle::where('parent_id', $this->owner->id)->count();

        $file = $this->makeImportFile([
            ['Nora Space', '2026-06-01', '09:00', 'Seat', "\u{00a0}NB-555-SP", '2026-06-03', '18:00', '2', '250', 'cash'],
        ]);

        $this->actingAs($this->owner)
            ->post(route('booking.import'), ['file' => $file])
            ->assertRedirect();

        $this->assertSame($before, Vehicle::where('parent_id', $this->owner->id)->count(), 'NBSP plate variant must not create a duplicate vehicle');
        $this->assertSame(1, Vehicle::where('parent_id', $this->owner->id)->where('license_plate', 'NB-555-SP')->count());
    }

    public function test_import_two_new_drivers_with_colliding_emails_get_unique_emails(): void
    {
        // "Ali O'Brien" and "Ali O Brien" both normalise to ali.o.brien@import.local
        // (space → '.' and apostrophe → '.' via str_replace) — second must get a suffix
        $file = $this->makeImportFile([
            ["Ali O'Brien", '2026-06-01', '09:00', 'Ford', 'XX-001-YY', '2026-06-03', '18:00', '2', '200', 'cash'],
            ['Ali O Brien',  '2026-06-04', '09:00', 'Ford', 'XX-002-YY', '2026-06-06', '18:00', '2', '200', 'cash'],
        ]);

        $this->actingAs($this->owner)
            ->post(route('booking.import'), ['file' => $file])
            ->assertRedirect();

        $emails = User::where('type', 'driver')->where('parent_id', $this->owner->id)
            ->pluck('email')->toArray();

        $this->assertCount(count(array_unique($emails)), $emails, 'All imported driver emails must be unique');
    }

    public function test_import_driver_ids_increment_correctly_across_rows(): void
    {
        $file = $this->makeImportFile([
            ['Alpha Driver', '2026-06-01', '09:00', 'BMW', 'ZZ-001-AA', '2026-06-02', '18:00', '1', '100', 'cash'],
            ['Beta Driver',  '2026-06-03', '09:00', 'BMW', 'ZZ-002-AA', '2026-06-04', '18:00', '1', '100', 'cash'],
        ]);

        $this->actingAs($this->owner)
            ->post(route('booking.import'), ['file' => $file])
            ->assertRedirect();

        $driverIds = Driver::where('parent_id', $this->owner->id)->pluck('driver_id')->sort()->values()->toArray();
        $this->assertCount(count(array_unique($driverIds)), $driverIds, 'driver_id must be unique across imported drivers');
    }

    public function test_import_reuses_existing_driver_and_vehicle_across_rows(): void
    {
        $file = $this->makeImportFile([
            ['John Doe', '2026-06-01', '09:00', 'Toyota', 'AA-123-BB', '2026-06-03', '18:00', '2', '300', 'cash'],
            ['John Doe', '2026-06-05', '09:00', 'Toyota', 'AA-123-BB', '2026-06-07', '18:00', '2', '300', 'cash'],
        ]);

        $this->actingAs($this->owner)
            ->post(route('booking.import'), ['file' => $file])
            ->assertRedirect();

        $this->assertSame(1, User::where('name', 'John Doe')->where('type', 'driver')->count(), 'Same driver name must not create a duplicate user');
        $this->assertSame(1, Vehicle::where('license_plate', 'AA-123-BB')->where('parent_id', $this->owner->id)->count(), 'Same plate must not create a duplicate vehicle');
        $this->assertSame(2, Booking::where('parent_id', $this->owner->id)->count(), 'Both rows should produce a booking');
    }

    public function test_import_denied_without_create_booking_permission(): void
    {
        $noPerms = User::factory()->create(['type' => 'employee', 'parent_id' => $this->owner->id]);
        $file = $this->makeImportFile([
            ['Jane Doe', '2026-06-01', '09:00', 'Fiat', 'BB-999-CC', '2026-06-02', '18:00', '1', '100', 'cash'],
        ]);

        $this->actingAs($noPerms)
            ->post(route('booking.import'), ['file' => $file])
            ->assertSessionHas('error');
    }

    public function test_import_skips_rows_where_start_is_not_before_end(): void
    {
        // Row 1: return date before start (day/month swapped) → must be skipped.
        // Row 2: valid → must be imported. Proves the guard is per-row and that
        // a skipped row creates no driver/vehicle/booking.
        $file = $this->makeImportFile([
            ['Swap Victim',  '2026-05-01', '09:00', 'Seat', 'SW-001-AP', '2026-03-05', '18:00', '2', '800', 'cash'],
            ['Valid Client', '2026-05-01', '09:00', 'Seat', 'VL-002-AP', '2026-05-03', '18:00', '2', '800', 'cash'],
        ]);

        $this->actingAs($this->owner)
            ->post(route('booking.import'), ['file' => $file])
            ->assertRedirect()
            ->assertSessionHas('import_skipped');

        // Swapped row imported nothing…
        $this->assertDatabaseMissing('users', ['name' => 'Swap Victim']);
        $this->assertDatabaseMissing('vehicles', ['license_plate' => 'SW-001-AP']);
        // …valid row went through.
        $this->assertDatabaseHas('users', ['name' => 'Valid Client', 'parent_id' => $this->owner->id]);
        $this->assertDatabaseHas('vehicles', ['license_plate' => 'VL-002-AP', 'parent_id' => $this->owner->id]);
    }

    public function test_import_allows_same_day_rental_when_return_time_is_later(): void
    {
        // Same calendar day is valid as long as the return time is after pickup.
        $file = $this->makeImportFile([
            ['Same Day', '2026-05-10', '09:00', 'Seat', 'SD-003-AP', '2026-05-10', '18:00', '1', '300', 'cash'],
        ]);

        $this->actingAs($this->owner)
            ->post(route('booking.import'), ['file' => $file])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('vehicles', ['license_plate' => 'SD-003-AP', 'parent_id' => $this->owner->id]);
    }
}
