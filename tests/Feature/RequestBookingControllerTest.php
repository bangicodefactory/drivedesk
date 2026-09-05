<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingRequest;
use App\Models\Guest;
use App\Models\Place;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\WithClient;
use Tests\TestCase;

class RequestBookingControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithClient;

    protected User $owner;
    protected Vehicle $vehicle;
    protected Place $pickup;
    protected Place $dropOff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asClient('acme');

        $perms = ['create booking', 'delete booking'];
        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner  = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $this->owner->givePermissionTo($perms);

        $this->vehicle = Vehicle::factory()->create(['parent_id' => $this->owner->id]);
        // BAN-297: the storefront form now takes its tenant from the requested
        // vehicle, so the places have to sit in the same tenant as $this->vehicle.
        $this->pickup  = Place::factory()->create(['parent_id' => $this->owner->id]);
        $this->dropOff = Place::factory()->create(['parent_id' => $this->owner->id]);
    }

    // ── unauthenticated ───────────────────────────────────────────────────────
    //
    // NOTE: booking_requests.index and .show are registered via Route::resource()
    // outside any auth group and are therefore publicly accessible — no redirect test.
    // The approve and refuse routes ARE inside the auth middleware group.

    public function test_confirm_booking_requires_auth(): void
    {
        $req = $this->makeRequest();
        $this->post(route('booking_requests.approve', $req->id))->assertRedirect(route('login'));
    }

    public function test_refuse_booking_requires_auth(): void
    {
        $req = $this->makeRequest();
        $this->post(route('booking_requests.refuse', $req->id))->assertRedirect(route('login'));
    }

    // ── public storeBooking ───────────────────────────────────────────────────

    public function test_store_booking_creates_guest_and_request(): void
    {
        $this->post(route('booking.store_request'), [
            'vehicle_id'        => $this->vehicle->id,
            'name'              => 'Alice Dupont',
            'email'             => 'alice@example.com',
            'phone_number'      => '+33600000001',
            'pickup_address'    => $this->pickup->id,
            'drop_off_address'  => $this->dropOff->id,
            'start_date'        => '2026-07-01',
            'end_date'          => '2026-07-04',
            'start_time'        => '09:00',
            'end_time'          => '18:00',
        ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('guests', ['email' => 'alice@example.com']);
        $this->assertDatabaseHas('booking_requests', ['status' => 'pending']);
    }

    public function test_store_booking_calculates_amount_from_days_and_daily_rate(): void
    {
        $vehicle = Vehicle::factory()->create([
            'daily_rate' => 100.0,
            'parent_id'  => $this->owner->id,
        ]);

        $this->post(route('booking.store_request'), [
            'vehicle_id'       => $vehicle->id,
            'name'             => 'Bob Martin',
            'email'            => 'bob@example.com',
            'phone_number'     => '+33600000002',
            'pickup_address'   => $this->pickup->id,
            'drop_off_address' => $this->dropOff->id,
            'start_date'       => '2026-07-01', // 3 days
            'end_date'         => '2026-07-04',
            'start_time'       => '09:00',
            'end_time'         => '18:00',
        ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', [
            'amount' => 300.0, // 3 days × 100
        ]);
    }

    /**
     * BAN-297: the public form is served to guests, for whom tenantExistsRule()
     * is inert, so the tenant is taken from the requested vehicle instead. A
     * place belonging to some other tenant must not be bookable against this
     * tenant's car, even anonymously.
     */
    public function test_store_booking_rejects_a_place_from_another_tenant(): void
    {
        $otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $foreign    = Place::factory()->create(['parent_id' => $otherOwner->id]);

        $this->post(route('booking.store_request'), [
            'vehicle_id'       => $this->vehicle->id,
            'name'             => 'Dana',
            'email'            => 'dana@example.com',
            'phone_number'     => '+33600000009',
            'pickup_address'   => $foreign->id,
            'drop_off_address' => $this->dropOff->id,
            'start_date'       => '2026-07-01',
            'end_date'         => '2026-07-04',
            'start_time'       => '09:00',
            'end_time'         => '18:00',
        ])->assertSessionHasErrors(['pickup_address']);

        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_car_details_only_offers_places_from_the_cars_tenant(): void
    {
        $otherOwner = User::factory()->create(['type' => 'owner', 'parent_id' => 0]);
        $foreign    = Place::factory()->create(['parent_id' => $otherOwner->id]);

        $this->get(route('client.details', $this->vehicle->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/CarDetails')
                ->where('places', fn ($places) => collect($places)
                    ->pluck('id')
                    ->doesntContain($foreign->id))
            );
    }

    public function test_car_details_excludes_similar_cars_marked_unavailable_for_rent(): void
    {
        $hidden = Vehicle::factory()->create([
            'parent_id'  => $this->owner->id,
            'type'       => $this->vehicle->type,
            'available_for_rent' => false,
        ]);

        $this->get(route('client.details', $this->vehicle->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/CarDetails')
                ->where('similarCars', fn ($cars) => collect($cars)->pluck('id')->doesntContain($hidden->id))
            );
    }

    public function test_store_booking_rejects_end_before_start(): void
    {
        $this->post(route('booking.store_request'), [
            'vehicle_id'       => $this->vehicle->id,
            'name'             => 'Carol',
            'email'            => 'carol@example.com',
            'phone_number'     => '+33600000003',
            'pickup_address'   => $this->pickup->id,
            'drop_off_address' => $this->dropOff->id,
            'start_date'       => '2026-07-05',
            'end_date'         => '2026-07-01', // before start
            'start_time'       => '09:00',
            'end_time'         => '18:00',
        ])
            ->assertRedirect()
            ->assertSessionHasErrors(['end_date']);
    }

    public function test_store_booking_rejects_missing_required_fields(): void
    {
        $this->post(route('booking.store_request'), [])
            ->assertSessionHasErrors(['vehicle_id', 'name', 'email', 'phone_number']);
    }

    public function test_store_booking_persists_customer_details_when_provided(): void
    {
        $this->post(route('booking.store_request'), [
            'vehicle_id'         => $this->vehicle->id,
            'name'               => 'Fatima Z',
            'email'              => 'fatima@example.com',
            'phone_number'       => '+212600000010',
            'pickup_address'     => $this->pickup->id,
            'drop_off_address'   => $this->dropOff->id,
            'start_date'         => '2026-07-01',
            'end_date'           => '2026-07-04',
            'start_time'         => '09:00',
            'end_time'           => '18:00',
            'age'                => 28,
            'nationality'        => 'Marocaine',
            'driving_experience' => 5,
            'passengers'         => 2,
            'whatsapp'           => '+212600000011',
        ])->assertRedirect();

        $this->assertDatabaseHas('booking_requests', [
            'age'                => 28,
            'nationality'        => 'Marocaine',
            'driving_experience' => 5,
            'passengers'         => 2,
            'whatsapp'           => '+212600000011',
        ]);
    }

    public function test_store_booking_persists_the_chosen_payment_preference(): void
    {
        $this->post(route('booking.store_request'), [
            'vehicle_id'         => $this->vehicle->id,
            'name'               => 'Karim B',
            'email'              => 'karim@example.com',
            'phone_number'       => '+212600000020',
            'pickup_address'     => $this->pickup->id,
            'drop_off_address'   => $this->dropOff->id,
            'start_date'         => '2026-07-01',
            'end_date'           => '2026-07-04',
            'start_time'         => '09:00',
            'end_time'           => '18:00',
            'payment_preference' => 'cmi',
        ])->assertRedirect();

        $this->assertDatabaseHas('booking_requests', ['payment_preference' => 'cmi']);
    }

    public function test_store_booking_rejects_an_unknown_payment_preference(): void
    {
        $this->post(route('booking.store_request'), [
            'vehicle_id'         => $this->vehicle->id,
            'name'               => 'Karim B',
            'email'              => 'karim@example.com',
            'phone_number'       => '+212600000021',
            'pickup_address'     => $this->pickup->id,
            'drop_off_address'   => $this->dropOff->id,
            'start_date'         => '2026-07-01',
            'end_date'           => '2026-07-04',
            'start_time'         => '09:00',
            'end_time'           => '18:00',
            'payment_preference' => 'bitcoin',
        ])->assertSessionHasErrors(['payment_preference']);

        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_store_booking_redirects_to_a_signed_confirmation_url(): void
    {
        $response = $this->post(route('booking.store_request'), [
            'vehicle_id'       => $this->vehicle->id,
            'name'             => 'Greg',
            'email'            => 'greg@example.com',
            'phone_number'     => '+212600000012',
            'pickup_address'   => $this->pickup->id,
            'drop_off_address' => $this->dropOff->id,
            'start_date'       => '2026-07-01',
            'end_date'         => '2026-07-04',
            'start_time'       => '09:00',
            'end_time'         => '18:00',
        ]);

        $response->assertRedirect();
        $bookingRequest = BookingRequest::latest('id')->first();
        $this->assertStringContainsString(
            "/reserve/confirmation/{$bookingRequest->id}",
            $response->headers->get('Location'),
        );
        $this->assertStringContainsString('signature=', $response->headers->get('Location'));
    }

    // ── /reserve wizard ────────────────────────────────────────────────────────

    public function test_reserve_page_renders_vehicles_and_places(): void
    {
        $this->get(route('reserve.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Booking/Index')
                ->has('vehicles')
                ->has('places')
            );
    }

    public function test_reserve_page_shows_every_vehicle_when_no_dates_are_given(): void
    {
        Booking::factory()->create(['vehicle' => $this->vehicle->id]);

        $this->get(route('reserve.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('vehicles', fn ($vehicles) => collect($vehicles)->pluck('id')->contains($this->vehicle->id))
            );
    }

    public function test_reserve_page_excludes_a_vehicle_marked_unavailable_for_rent(): void
    {
        $hidden = Vehicle::factory()->create(['parent_id' => $this->owner->id, 'available_for_rent' => false]);

        $this->get(route('reserve.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('vehicles', fn ($vehicles) => collect($vehicles)->pluck('id')->doesntContain($hidden->id))
            );
    }

    public function test_reserve_page_excludes_a_vehicle_with_an_overlapping_booking(): void
    {
        Booking::factory()->create([
            'vehicle'    => $this->vehicle->id,
            'start_date' => '2026-08-10', 'start_time' => '09:00',
            'end_date'   => '2026-08-15', 'end_time'   => '18:00',
            'status'     => 'yet_to_start',
        ]);

        $this->get(route('reserve.create', [
            'start_date' => '2026-08-12', 'start_time' => '09:00',
            'end_date'   => '2026-08-14', 'end_time'   => '18:00',
        ]))->assertInertia(fn (Assert $page) => $page
            ->where('vehicles', fn ($vehicles) => collect($vehicles)->pluck('id')->doesntContain($this->vehicle->id))
        );
    }

    public function test_reserve_page_keeps_a_vehicle_whose_booking_does_not_overlap(): void
    {
        Booking::factory()->create([
            'vehicle'    => $this->vehicle->id,
            'start_date' => '2026-08-01', 'start_time' => '09:00',
            'end_date'   => '2026-08-05', 'end_time'   => '18:00',
            'status'     => 'yet_to_start',
        ]);

        $this->get(route('reserve.create', [
            'start_date' => '2026-08-12', 'start_time' => '09:00',
            'end_date'   => '2026-08-14', 'end_time'   => '18:00',
        ]))->assertInertia(fn (Assert $page) => $page
            ->where('vehicles', fn ($vehicles) => collect($vehicles)->pluck('id')->contains($this->vehicle->id))
        );
    }

    public function test_reserve_page_ignores_cancelled_bookings_when_checking_availability(): void
    {
        Booking::factory()->cancelled()->create([
            'vehicle'    => $this->vehicle->id,
            'start_date' => '2026-08-10', 'start_time' => '09:00',
            'end_date'   => '2026-08-15', 'end_time'   => '18:00',
        ]);

        $this->get(route('reserve.create', [
            'start_date' => '2026-08-12', 'start_time' => '09:00',
            'end_date'   => '2026-08-14', 'end_time'   => '18:00',
        ]))->assertInertia(fn (Assert $page) => $page
            ->where('vehicles', fn ($vehicles) => collect($vehicles)->pluck('id')->contains($this->vehicle->id))
        );
    }

    public function test_confirmation_page_renders_for_a_valid_signed_url(): void
    {
        $req = $this->makeRequest();

        $this->get(URL::signedRoute('reserve.confirmation', ['bookingRequest' => $req->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/Booking/Confirmation')
                ->where('reference', 'BR-' . str_pad($req->id, 5, '0', STR_PAD_LEFT))
            );
    }

    public function test_confirmation_page_exposes_the_chosen_payment_preference(): void
    {
        $req = $this->makeRequest(['payment_preference' => 'paypal']);

        $this->get(URL::signedRoute('reserve.confirmation', ['bookingRequest' => $req->id]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('paymentPreference', 'paypal')
            );
    }

    public function test_confirmation_page_rejects_an_unsigned_url(): void
    {
        $req = $this->makeRequest();

        $this->get(route('reserve.confirmation', ['bookingRequest' => $req->id]))
            ->assertForbidden();
    }

    // ── RequestBookingController::confirmBooking ──────────────────────────────

    public function test_confirm_booking_converts_request_to_booking(): void
    {
        $guest = Guest::factory()->create();
        $req   = BookingRequest::factory()->create([
            'driver'           => $guest->id,
            'vehicle'          => $this->vehicle->id,
            'pickup_address'   => $this->pickup->id,
            'drop_off_address' => $this->dropOff->id,
            'status'           => 'pending',
            'parent_id'        => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->post(route('booking_requests.approve', $req->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        // BookingRequest status updated to confirmed
        $this->assertDatabaseHas('booking_requests', [
            'id'     => $req->id,
            'status' => 'confirmed',
        ]);

        // A new Booking was created for this vehicle
        $this->assertDatabaseHas('bookings', [
            'vehicle'        => $this->vehicle->id,
            'status'         => 'confirmed',
            'payment_status' => 'impaye',
        ]);
    }

    public function test_confirm_booking_creates_user_from_guest(): void
    {
        $guest = Guest::factory()->create(['email' => 'newcustomer@example.com']);
        $req   = BookingRequest::factory()->create([
            'driver'           => $guest->id,
            'vehicle'          => $this->vehicle->id,
            'pickup_address'   => $this->pickup->id,
            'drop_off_address' => $this->dropOff->id,
            'parent_id'        => $this->owner->id,
        ]);

        $this->actingAs($this->owner)
            ->post(route('booking_requests.approve', $req->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'newcustomer@example.com',
            'type'  => 'customer',
        ]);
    }

    public function test_confirm_booking_flashes_error_for_missing_request(): void
    {
        $this->actingAs($this->owner)
            ->post(route('booking_requests.approve', 99999))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── RequestBookingController::refuseBooking ───────────────────────────────

    public function test_refuse_booking_sets_status_to_refused(): void
    {
        $guest = Guest::factory()->create();
        $req   = BookingRequest::factory()->create([
            'driver'  => $guest->id,
            'vehicle' => $this->vehicle->id,
            'status'  => 'pending',
        ]);

        $this->actingAs($this->owner)
            ->post(route('booking_requests.refuse', $req->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('booking_requests', [
            'id'     => $req->id,
            'status' => 'refused',
        ]);
    }

    public function test_refuse_booking_flashes_error_for_missing_request(): void
    {
        $this->actingAs($this->owner)
            ->post(route('booking_requests.refuse', 99999))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    // ── Inertia component tests ───────────────────────────────────────────────

    public function test_index_renders_inertia_component(): void
    {
        $this->makeRequest();

        $this->actingAs($this->owner)
            ->get(route('booking_requests.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('BookingRequest/Index')
                ->has('bookingRequests')
            );
    }

    public function test_show_renders_inertia_component(): void
    {
        $req = $this->makeRequest();

        $this->actingAs($this->owner)
            ->get(route('booking_requests.show', Crypt::encrypt($req->id)))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('BookingRequest/Show')
                ->has('booking')
                ->where('booking.id', $req->id)
                ->missing('settings')
            );
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeRequest(array $overrides = []): BookingRequest
    {
        $guest = Guest::factory()->create();

        return BookingRequest::factory()->create(array_merge([
            'driver'           => $guest->id,
            'vehicle'          => $this->vehicle->id,
            'pickup_address'   => $this->pickup->id,
            'drop_off_address' => $this->dropOff->id,
            'parent_id'        => $this->owner->id,
        ], $overrides));
    }
}
