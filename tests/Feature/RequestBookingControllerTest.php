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

        // `manage booking` is what gates the Booking Requests sidebar link and,
        // since BAN-322, the index/show routes themselves.
        $perms = ['manage booking', 'create booking', 'delete booking'];
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

    /**
     * BAN-322. booking_requests.index was registered via Route::resource()
     * outside every auth group, and index() itself checked nothing: a plain
     * GET /booking_requests from anyone on the internet rendered every booking
     * request in the database, each with the guest's name. This file used to
     * carry a comment saying so and no test.
     */
    public function test_index_requires_auth(): void
    {
        $this->makeRequest();

        $this->get(route('booking_requests.index'))->assertRedirect(route('login'));
    }

    public function test_show_requires_auth(): void
    {
        $req = $this->makeRequest();

        $this->get(route('booking_requests.show', Crypt::encrypt($req->id)))
            ->assertRedirect(route('login'));
    }

    /**
     * Authenticated is not enough. The sidebar has always gated the link on
     * `manage booking`, so a driver or customer account -- which has no reason
     * to see anyone's contact details -- must not reach it by typing the URL.
     */
    public function test_index_requires_the_manage_booking_permission(): void
    {
        $this->makeRequest();

        $outsider = User::factory()->create(['type' => 'driver', 'parent_id' => $this->owner->id]);

        $this->actingAs($outsider)
            ->get(route('booking_requests.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }

    public function test_show_requires_the_manage_booking_permission(): void
    {
        $req = $this->makeRequest();

        $outsider = User::factory()->create(['type' => 'driver', 'parent_id' => $this->owner->id]);

        $this->actingAs($outsider)
            ->get(route('booking_requests.show', Crypt::encrypt($req->id)))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }

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

    // ── optional customer details ───────────────────────────────

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

    public function test_store_booking_rejects_paypal_as_a_payment_preference(): void
    {
        // PayPal is inert in this codebase -- no package, no route, no webhook
        // -- so recording it as an intent would send staff chasing a method the
        // business cannot take.
        $this->post(route('booking.store_request'), $this->publicPayload([
            'payment_preference' => 'paypal',
        ]))->assertSessionHasErrors(['payment_preference']);

        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_store_booking_rejects_a_driver_under_eighteen(): void
    {
        $this->post(route('booking.store_request'), $this->publicPayload(['age' => 17]))
            ->assertSessionHasErrors(['age']);

        $this->assertDatabaseCount('booking_requests', 0);
    }

    /**
     * The cap is the car's seat count, not a fixed number: a 15-seat minibus
     * has to take a 12-passenger booking, and a 4-seater must not take 9.
     */
    public function test_store_booking_rejects_more_passengers_than_the_car_seats(): void
    {
        $small = Vehicle::factory()->create([
            'parent_id'       => $this->owner->id,
            'number_of_seats' => 4,
        ]);

        $this->post(route('booking.store_request'), $this->publicPayload([
            'vehicle_id' => $small->id,
            'passengers' => 5,
        ]))->assertSessionHasErrors(['passengers']);

        $this->assertDatabaseCount('booking_requests', 0);
    }

    public function test_store_booking_accepts_passengers_up_to_the_seat_count(): void
    {
        $minibus = Vehicle::factory()->create([
            'parent_id'       => $this->owner->id,
            'number_of_seats' => 15,
        ]);

        $this->post(route('booking.store_request'), $this->publicPayload([
            'vehicle_id' => $minibus->id,
            'passengers' => 12,
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('booking_requests', ['passengers' => 12]);
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

    // ── tenancy ──────────────────────────────────────────────

    /**
     * BAN-327. storeBooking() never assigned parent_id, so every request a real
     * deployment has ever taken sits at the column default of 0 -- belonging to
     * no tenant at all. The submitter is a guest, so the tenant has to come from
     * the requested vehicle, exactly as the place validation above already does.
     */
    public function test_a_public_request_carries_the_vehicles_tenant(): void
    {
        $this->post(route('booking.store_request'), $this->publicPayload([
            'email' => 'tenant-check@example.com',
        ]))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('booking_requests', [
            'vehicle'   => $this->vehicle->id,
            'parent_id' => $this->owner->id,
        ]);
        $this->assertDatabaseMissing('booking_requests', ['parent_id' => 0]);
    }

    /**
     * The knock-on defect. confirmBooking() numbers the Booking it creates with
     * RequestBookingController::bookingNumber(), which reads the highest
     * `booking_id` from *booking_requests* -- a column nothing ever writes,
     * filtered by a parent_id nothing ever set. It therefore returned 1 every
     * single time, so every booking approved from a request was numbered 1,
     * colliding with each other and with the agency's real sequence.
     */
    public function test_approving_a_request_continues_the_tenants_booking_numbering(): void
    {
        Booking::factory()->create([
            'parent_id'  => $this->owner->id,
            'vehicle'    => $this->vehicle->id,
            'booking_id' => 7,
        ]);

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
            ->assertSessionHas('success');

        $created = Booking::where('parent_id', $this->owner->id)
            ->where('status', 'confirmed')
            ->latest('id')
            ->first();

        $this->assertNotNull($created);
        $this->assertSame(8, (int) $created->booking_id, 'approved request restarted the booking numbering');
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

    /**
     * The five actions Route::resource() used to register with no method behind
     * them. They could only ever raise BadMethodCallException; nothing should
     * bring them back.
     */
    public function test_only_index_and_show_are_registered_for_the_resource(): void
    {
        foreach (['create', 'store', 'edit', 'update', 'destroy'] as $action) {
            $this->assertFalse(
                \Illuminate\Support\Facades\Route::has("booking_requests.{$action}"),
                "booking_requests.{$action} is registered but the controller has no method for it"
            );
        }

        $this->assertTrue(\Illuminate\Support\Facades\Route::has('booking_requests.index'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('booking_requests.show'));
    }

    /**
     * BAN-321: a vehicle withdrawn from the storefront must not reappear as a
     * "similar car" suggestion on another vehicle's page -- that route is the
     * one place a guest could still be offered it.
     */
    // ── the registration year, and the ligature in its column name ──────

    /**
     * BAN-333. `vehicles` has a column spelled with a U+FB01 LATIN SMALL
     * year_of_ﬁrst_immatriculation FI. Every PHP caller that typed a plain "fi" read null, so the
     * detail page printed "N/A" for the year of a car whose year is right
     * there in the row.
     */
    public function test_car_details_exposes_the_registration_year(): void
    {
        $vehicle = Vehicle::factory()->create([
            'parent_id' => $this->owner->id,
            'year_of_ﬁrst_immatriculation' => '2019',
        ]);

        $this->get(route('client.details', $vehicle->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/CarDetails')
                ->where('car.first_registration_year', '2019')
            );
    }

    /**
     * The same misspelling in storeBooking()'s vehicle_details snapshot, which
     * matters more: that JSON is what a booking request keeps about the car it
     * was made for, and it has been recording "year": null since it was added.
     */
    public function test_a_booking_request_snapshot_records_the_registration_year(): void
    {
        $vehicle = Vehicle::factory()->create([
            'parent_id' => $this->owner->id,
            'year_of_ﬁrst_immatriculation' => '2019',
        ]);

        $this->post(route('booking.store_request'), [
            'vehicle_id'       => $vehicle->id,
            'name'             => 'Yassine Berrada',
            'email'            => 'yassine@example.com',
            'phone_number'     => '+212661223344',
            'pickup_address'   => $this->pickup->id,
            'drop_off_address' => $this->dropOff->id,
            'start_date'       => '2026-10-05',
            'start_time'       => '09:00',
            'end_date'         => '2026-10-09',
            'end_time'         => '18:00',
        ])->assertRedirect();

        $details = json_decode(BookingRequest::latest('id')->first()->vehicle_details, true);

        $this->assertSame('2019', $details['year']);
    }

    // ── /reserve prefill, handed over by the landing search panel ─────────

    /**
     * BAN-333. The redesigned landing makes the search panel the page's primary
     * action, and it submits to /reserve. If the wizard did not read those
     * values back, the visitor would fill in a location and two dates and then
     * be asked for the same three things again on step 2 -- which is precisely
     * the friction the redesign exists to remove.
     *
     * The flag is forced rather than inherited from acme (CLAUDE.md §10.2
     * rule 6): this is about the handover, not about which clients ship the
     * storefront.
     */
    public function test_reserve_prefills_from_the_landing_search_panel(): void
    {
        config(['client.features.public_storefront' => true]);

        $this->get(route('reserve.create', [
            'place'      => $this->pickup->id,
            'start_date' => '2026-10-05',
            'end_date'   => '2026-10-09',
            'start_time' => '09:00',
            'end_time'   => '18:00',
        ]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Public/Booking/Index')
            ->where('prefill.place', (string) $this->pickup->id)
            ->where('prefill.start_date', '2026-10-05')
            ->where('prefill.end_date', '2026-10-09')
            ->where('prefill.start_time', '09:00')
            ->where('prefill.end_time', '18:00')
        );
    }

    /**
     * A place id that this storefront does not offer must not be echoed back.
     * The wizard binds it to a <Select>, and a value with no matching option
     * renders as an empty control -- the visitor sees a blank "Pick-up
     * location" that they cannot correct by re-picking the same entry.
     */
    public function test_reserve_drops_a_place_it_does_not_offer(): void
    {
        config(['client.features.public_storefront' => true]);

        $this->get(route('reserve.create', ['place' => 999999]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('prefill.place', null));
    }

    /**
     * Anything that is not a Y-m-d / H:i is dropped rather than passed through.
     * These values reach a whereRaw() comparison and a date input, and the
     * query string is public.
     */
    public function test_reserve_drops_malformed_dates_and_times(): void
    {
        config(['client.features.public_storefront' => true]);

        $this->get(route('reserve.create', [
            'start_date' => 'not-a-date',
            'end_date'   => '2026-13-45',
            'start_time' => '25:00',
        ]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('prefill.start_date', null)
            ->where('prefill.end_date', null)
            ->where('prefill.start_time', null)
        );
    }

    /**
     * The /search 500 (BAN-329) again, on a different route: a query array
     * reaches string concatenation and whereRaw bindings. Anyone can construct
     * the URL and /reserve is linked from a crawlable page.
     */
    public function test_reserve_survives_array_query_parameters(): void
    {
        config(['client.features.public_storefront' => true]);

        $this->get('/reserve?start_date[]=x&end_date[]=y&place[]=z&start_time[]=w')
            ->assertOk();
    }

    /**
     * The prefilled range still has to be a real availability query, not just
     * echoed text: a car already booked across those dates must not come back
     * in the list the visitor is about to choose from.
     */
    public function test_reserve_prefill_dates_still_filter_out_a_booked_car(): void
    {
        config(['client.features.public_storefront' => true]);

        Booking::factory()->create([
            'vehicle'    => $this->vehicle->id,
            'start_date' => '2026-10-06',
            'start_time' => '09:00',
            'end_date'   => '2026-10-08',
            'end_time'   => '18:00',
            'status'     => 'confirmed',
            'parent_id'  => $this->owner->id,
        ]);

        $this->get(route('reserve.create', [
            'start_date' => '2026-10-05',
            'end_date'   => '2026-10-09',
        ]))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('vehicles', fn ($vehicles) => collect($vehicles)->pluck('id')->doesntContain($this->vehicle->id))
        );
    }

    public function test_car_details_excludes_similar_cars_marked_unavailable_for_rent(): void
    {
        $hidden = Vehicle::factory()->create([
            'parent_id'          => $this->owner->id,
            'type'               => $this->vehicle->type,
            'available_for_rent' => false,
        ]);

        $this->get(route('client.details', $this->vehicle->id))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Public/CarDetails')
                ->where('similarCars', fn ($cars) => collect($cars)->pluck('id')->doesntContain($hidden->id))
            );
    }

    // ── the /reserve wizard ────────────────────────────────────────

    public function test_store_booking_redirects_to_a_signed_confirmation_url(): void
    {
        // Forced, not inherited from the client fixture (CLAUDE.md 10.2 rule 6).
        config(['client.features.public_storefront' => true]);

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

    public function test_reserve_page_renders_vehicles_and_places(): void
    {
        // Forced, not inherited from the client fixture (CLAUDE.md 10.2 rule 6).
        config(['client.features.public_storefront' => true]);

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
        // Forced, not inherited from the client fixture (CLAUDE.md 10.2 rule 6).
        config(['client.features.public_storefront' => true]);

        Booking::factory()->create(['vehicle' => $this->vehicle->id]);

        $this->get(route('reserve.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('vehicles', fn ($vehicles) => collect($vehicles)->pluck('id')->contains($this->vehicle->id))
            );
    }

    public function test_reserve_page_excludes_a_vehicle_marked_unavailable_for_rent(): void
    {
        // Forced, not inherited from the client fixture (CLAUDE.md 10.2 rule 6).
        config(['client.features.public_storefront' => true]);

        $hidden = Vehicle::factory()->create(['parent_id' => $this->owner->id, 'available_for_rent' => false]);

        $this->get(route('reserve.create'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('vehicles', fn ($vehicles) => collect($vehicles)->pluck('id')->doesntContain($hidden->id))
            );
    }

    public function test_reserve_page_excludes_a_vehicle_with_an_overlapping_booking(): void
    {
        // Forced, not inherited from the client fixture (CLAUDE.md 10.2 rule 6).
        config(['client.features.public_storefront' => true]);

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
        // Forced, not inherited from the client fixture (CLAUDE.md 10.2 rule 6).
        config(['client.features.public_storefront' => true]);

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
        // Forced, not inherited from the client fixture (CLAUDE.md 10.2 rule 6).
        config(['client.features.public_storefront' => true]);

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
        // Forced, not inherited from the client fixture (CLAUDE.md 10.2 rule 6).
        config(['client.features.public_storefront' => true]);

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
        // Forced, not inherited from the client fixture (CLAUDE.md 10.2 rule 6).
        config(['client.features.public_storefront' => true]);

        $req = $this->makeRequest(['payment_preference' => 'paypal']);

        $this->get(URL::signedRoute('reserve.confirmation', ['bookingRequest' => $req->id]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('paymentPreference', 'paypal')
            );
    }

    public function test_confirmation_page_rejects_an_unsigned_url(): void
    {
        // Forced, not inherited from the client fixture (CLAUDE.md 10.2 rule 6).
        config(['client.features.public_storefront' => true]);

        $req = $this->makeRequest();

        $this->get(route('reserve.confirmation', ['bookingRequest' => $req->id]))
            ->assertForbidden();
    }

    /**
     * The flag itself. A client whose public face is the B2B demo gateway --
     * drivedesk -- must 404 the wizard rather than serve a full B2C booking
     * flow to the audience it sells the platform to (BAN-261, CLAUDE.md 10.2
     * rule 3).
     */
    public function test_the_wizard_404s_when_the_storefront_is_off(): void
    {
        config(['client.features.public_storefront' => false]);

        $this->get(route('reserve.create'))->assertNotFound();
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    /**
     * A valid public booking submission. Overrides carry the one field a test
     * is actually about, so a failure cannot be a missing-required-field
     * accident somewhere else in the form.
     */
    private function publicPayload(array $overrides = []): array
    {
        return array_merge([
            'vehicle_id'       => $this->vehicle->id,
            'name'             => 'Karim B',
            'email'            => 'karim@example.com',
            'phone_number'     => '+212600000030',
            'pickup_address'   => $this->pickup->id,
            'drop_off_address' => $this->dropOff->id,
            'start_date'       => '2026-07-01',
            'end_date'         => '2026-07-04',
            'start_time'       => '09:00',
            'end_time'         => '18:00',
        ], $overrides);
    }

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
