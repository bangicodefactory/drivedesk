<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\BookingRequest;
use App\Models\Place;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;

class RequestBookingController extends Controller
{
    /**
     * The /reserve booking wizard: dates/locations, then a car (filtered to
     * ones actually free for those dates), then customer details. Reuses the
     * same Vehicle/Place/Booking data as the rest of the app — no separate
     * "Car" model, no separate bookings table.
     *
     * Re-invoked via an Inertia partial reload (`only: ['vehicles']`) once the
     * wizard's date step is filled in, so `vehicles` reflects availability for
     * whatever range the query string carries.
     */
    public function create(Request $request)
    {
        $vehiclesQuery = Vehicle::where('available_for_rent', true)
            ->select('id', 'name', 'model', 'daily_rate', 'number_of_seats', 'gearbox', 'fuel_type', 'picture');

        // is_string() rather than a bare query() read: /reserve?start_date[]=x
        // hands back an array, which the concatenation below turns into the
        // literal "Array" and the whereRaw bindings reject outright. Same
        // public-URL-anyone-can-construct shape as the /search 500 (BAN-329).
        $startDate = $this->queryDate($request, 'start_date');
        $endDate   = $this->queryDate($request, 'end_date');
        $startTime = $this->queryTime($request, 'start_time');
        $endTime   = $this->queryTime($request, 'end_time');

        if ($startDate && $endDate) {
            $start = $startDate . ' ' . ($startTime ?: '00:00') . ':00';
            $end   = $endDate . ' ' . ($endTime ?: '23:59') . ':00';

            // Same overlap rule as VehicleController::getAvailableVehicle() (the
            // admin planning screen): two ranges overlap unless one ends before
            // the other starts. Not tenant-scoped — Booking's tenant scope is
            // inert for a guest request (BelongsToTenant::tenantScopeApplies()),
            // matching how landingProps()/showSimilarCars() already read Vehicle
            // for the same unauthenticated storefront.
            $unavailableVehicleIds = Booking::whereNotIn('status', ['completed', 'cancelled'])
                ->whereRaw("CONCAT(start_date, ' ', start_time) <= ?", [$end])
                ->whereRaw("CONCAT(end_date, ' ', end_time) >= ?", [$start])
                ->pluck('vehicle');

            $vehiclesQuery->whereNotIn('id', $unavailableVehicleIds);
        }

        $places = Place::select('id', 'name', 'city')->get();

        // What the landing's search panel filled in, handed forward so the
        // wizard opens on the dates the visitor already chose instead of
        // asking for them a second time (BAN-333). Only values that survive a
        // shape check are echoed back, and `place` only when it names a place
        // this storefront actually offers -- an unknown id would set a Select
        // to a value with no matching option, which renders as blank.
        $place = $request->query('place');
        $placeId = is_scalar($place) && ctype_digit((string) $place) ? (int) $place : null;

        return Inertia::render('Public/Booking/Index', [
            'vehicles'           => $vehiclesQuery->get(),
            'places'             => $places,
            'preselectedVehicle' => $request->query('vehicle'),
            'prefill'            => [
                'place'      => $placeId !== null && $places->contains('id', $placeId) ? (string) $placeId : null,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'start_time' => $startTime,
                'end_time'   => $endTime,
            ],
        ]);
    }

    /** A Y-m-d query value, or null if absent or not that shape. */
    private function queryDate(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        if (! is_string($value) || $value === '') {
            return null;
        }

        $date = DateTime::createFromFormat('!Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value ? $value : null;
    }

    /** An H:i query value, or null if absent or not that shape. */
    private function queryTime(Request $request, string $key): ?string
    {
        $value = $request->query($key);

        if (! is_string($value) || $value === '') {
            return null;
        }

        $time = DateTime::createFromFormat('!H:i', $value);

        return $time && $time->format('H:i') === $value ? $value : null;
    }

    /**
     * Display the specific car details and similar cars
     */
    public function showSimilarCars($id)
    {
        $car = Vehicle::with('types')->where('id', $id)->firstOrFail();

        // BAN-297: the storefront belongs to the car's tenant. Everything this
        // page offers -- similar cars, pickup/drop-off places -- has to come
        // from that same tenant, or the booking form hands a visitor ids that
        // storeBooking() must then reject. The visitor is normally a guest, so
        // the global tenant scope is inert here and parent_id is applied by hand.
        $similarCars = Vehicle::with('types')->where('id', '!=', $id)
            ->where('parent_id', $car->parent_id)
            // A vehicle withdrawn from the storefront must not come back as a
            // suggestion -- that is the whole point of the flag.
            ->where('available_for_rent', true)
            ->where(function ($query) use ($car) {
                $query->where('type', $car->type)
                    ->orWhere('fuel_type', $car->fuel_type)
                    ->orWhereBetween('daily_rate', [
                        $car->daily_rate * 0.7,
                        $car->daily_rate * 1.3,
                    ]);
            })
            ->inRandomOrder()
            ->limit(3)
            ->get();

        $places = Place::where('parent_id', $car->parent_id)->get(['id', 'name', 'city']);

        // Appended per instance rather than on the model: the detail page shows
        // the registration year, and the column it comes from is spelled with a
        // ligature that no JS caller should have to reproduce.
        $car->append('first_registration_year');

        return Inertia::render('Public/CarDetails', compact('car', 'similarCars', 'places'));
    }

    /**
     * Process the booking request
     */

     public function storeBooking(Request $request)
     {
         // BAN-297: the tenant comes from the requested vehicle, not from Auth.
         // This is the public storefront form and its submitter is normally a
         // guest, for whom tenantExistsRule() is deliberately inert -- scoping
         // the places on Auth would close nothing on the only path this endpoint
         // actually serves, while rejecting a signed-in visitor who is browsing
         // another tenant's storefront. The vehicle picks the tenant, so both
         // places must belong to it.
         //
         // Null when vehicle_id is missing or does not resolve; the vehicle_id
         // rule below fails the request in that case, so the places fall back to
         // a bare exists and the outcome is the same.
         $vehicleTenantId = Vehicle::whereKey($request->input('vehicle_id'))->value('parent_id');

         $placeRule = function () use ($vehicleTenantId) {
             $rule = \Illuminate\Validation\Rule::exists('places', 'id');

             if ($vehicleTenantId !== null) {
                 $rule->where('parent_id', $vehicleTenantId);
             }

             return $rule;
         };

         // Validate the request
         $validator = Validator::make($request->all(), [
             'vehicle_id'       => ['required', tenantExistsRule('vehicles')], // BAN-294
             'name'             => 'required|string|max:255',
             'email'            => 'required|email',
             'phone_number'     => 'required|string|max:20',
             'pickup_address'   => ['required', $placeRule()],
             'drop_off_address' => ['required', $placeRule()],
             'start_date'       => 'required|date',
             'end_date'         => 'required|date|after:start_date',
             'start_time'       => 'required',
             'end_time'         => 'required',
             'driver'           => 'nullable|boolean',
             'notes'            => 'nullable|string',
             'company_name'     => 'nullable|string',
             'city'             => 'nullable|string',
             // Optional customer details. The existing storefront form
             // (CarDetails.jsx) sends none of them, so every rule is nullable
             // and that path is unaffected -- the columns exist for the fuller
             // booking flow that collects them.
             'age'                => 'nullable|integer|min:18|max:100',
             'nationality'        => 'nullable|string|max:80',
             'driving_experience' => 'nullable|integer|min:0|max:80',
             // Bounded by the actual vehicle: a 15-seat minibus should take a
             // 12-passenger booking, and a 2-seater should not take 9. Falls
             // back to a permissive ceiling when the vehicle does not resolve
             // -- the vehicle_id rule fails that request anyway.
             'passengers'         => 'nullable|integer|min:1|max:'
                 . (Vehicle::whereKey($request->input('vehicle_id'))->value('number_of_seats') ?: 60),
             'whatsapp'           => 'nullable|string|max:30',
             // What the customer said they intend to pay with. Nothing is
             // charged: no gateway is integrated anywhere in this codebase.
             // It records the intent so staff know how to follow up.
             //
             // No 'paypal'. PayPal is inert here -- no package, no route, no
             // webhook -- and it is not a method a Moroccan agency's customers
             // reach for; offering it as an intent would have staff following
             // up on a method the business cannot take. CMI is the real card
             // gateway and stays, as a stated intent only, until its callback
             // exists behind feature('booking_payment').
             //
             // And the accepted set follows that flag. It used to be a fixed
             // in:cash,cmi, so a deployment that had deliberately turned card
             // payment off still accepted a hand-crafted or replayed POST with
             // payment_preference=cmi -- and then rendered the guest a
             // confirmation promising a follow-up about an online payment the
             // business had switched off. The wizard never sends it in that
             // state; nothing else stopped it either (BAN-334).
             'payment_preference' => 'nullable|in:'.(feature('booking_payment') ? 'cash,cmi' : 'cash'),
         ]);

         if ($validator->fails()) {
             return redirect()->back()
                 ->withErrors($validator)
                 ->withInput();
         }

         try {
             DB::beginTransaction();

             // Always create a new guest record (don't check for existing)
             $guest = new Guest();
             $guest->name = $request->name;
             $guest->email = $request->email;
             $guest->phone_number = $request->phone_number;
             $guest->type = 'customer';
             $guest->password = Hash::make(Str::random(12));
             $guest->is_active = true;
             $guest->lang = app()->getLocale();

             // Set optional fields if provided
             if ($request->has('company_name')) {
                 $guest->company_name = $request->company_name;
             }

             if ($request->has('city')) {
                 $guest->city = $request->city;
             }

             $guest->save();

             $vehicle = Vehicle::with('types')->findOrFail($request->vehicle_id);

             $start = new \DateTime($request->start_date);
             $end = new \DateTime($request->end_date);
             $days = $end->diff($start)->days;
             $amount = $days * $vehicle->daily_rate;

             // Create booking request
             $booking = new BookingRequest();
             $booking->driver = $guest->id;
             $booking->vehicle = $vehicle->id;
             // $booking->driver = $request->driver ?? false;
             $booking->start_date = $request->start_date;
             $booking->start_time = $request->start_time;
             $booking->end_date = $request->end_date;
             $booking->end_time = $request->end_time;
             $booking->pickup_address = $request->pickup_address;
             $booking->drop_off_address = $request->drop_off_address;
             $booking->status = 'pending';
             $booking->amount = $amount;
             $booking->payment_status = 'pending';
             $booking->notes = $request->notes;
             // The tenant, taken from the vehicle rather than from Auth -- the
             // submitter is a guest. Same source the place validation above
             // uses (BAN-297), so a request cannot straddle two tenants. Left
             // unset, this stayed at the column default of 0 and the row
             // belonged to nobody: invisible to any scoped query, and the
             // reason booking-request numbering never worked.
             $booking->parent_id = (int) $vehicleTenantId;
             $booking->age = $request->age;
             $booking->nationality = $request->nationality;
             $booking->driving_experience = $request->driving_experience;
             $booking->passengers = $request->passengers;
             $booking->whatsapp = $request->whatsapp;
             $booking->payment_preference = $request->payment_preference;

             $booking->vehicle_details = json_encode([
                'name'          => $vehicle->name,
                'model'         => $vehicle->model,
                'type'          => $vehicle->types->type ?? $vehicle->type,
                'fuel_type'     => $vehicle->fuel_type,
                'gearbox'       => $vehicle->gearbox,
                'seats'         => $vehicle->number_of_seats,
                'license_plate' => $vehicle->license_plate,
                // Plain "fi": the column carries a U+FB01 ligature, so this read
                // null and every snapshot written so far says "year": null.
                // Vehicle::getFirstRegistrationYearAttribute() spells it right.
                'year'          => $vehicle->first_registration_year,
            ]);
            
             $booking->save();

             DB::commit();

             // Signed so a guest cannot open another request's confirmation by
             // guessing an id -- booking_requests carries a name, email and
             // phone and is neither tenant- nor auth-scoped for a guest.
             return redirect()->to(URL::signedRoute('reserve.confirmation', ['bookingRequest' => $booking->id]))
                 ->with('success', 'Booking request submitted successfully! We will contact you soon.');
         } catch (\Exception $e) {
             DB::rollBack();
             return redirect()->back()->with('error', 'An error occurred. Please try again. Error: ' . $e->getMessage());
         }
     }

    /**
     * The confirmation page a guest lands on right after submitting /reserve
     * (or CarDetails.jsx's booking form). Signed URL only — see the note above
     * storeBooking()'s redirect.
     */
    public function confirmation(BookingRequest $bookingRequest)
    {
        $bookingRequest->load(['car', 'pickupPlace', 'dropOffPlace']);

        $start = new DateTime($bookingRequest->start_date);
        $end   = new DateTime($bookingRequest->end_date);
        $days  = max(1, $end->diff($start)->days);

        return Inertia::render('Public/Booking/Confirmation', [
            'reference'    => 'BR-' . str_pad($bookingRequest->id, 5, '0', STR_PAD_LEFT),
            'car'          => [
                'name'    => $bookingRequest->car?->name,
                'model'   => $bookingRequest->car?->model,
                'picture' => $bookingRequest->car?->picture,
            ],
            'pickupPlace'  => $bookingRequest->pickupPlace?->name,
            'dropOffPlace' => $bookingRequest->dropOffPlace?->name,
            'startDate'    => $bookingRequest->start_date,
            'startTime'    => $bookingRequest->start_time,
            'endDate'      => $bookingRequest->end_date,
            'endTime'      => $bookingRequest->end_time,
            'days'         => $days,
            'amount'       => $bookingRequest->amount,
            'paymentPreference' => $bookingRequest->payment_preference,
        ]);
    }

    /**
     * Display a listing of the booking requests.
     */
    public function index()
    {
        // dashboard, not back(): url()->previous() prefers the Referer, and for
        // a request coming *from* this page -- an Inertia partial reload after
        // the account's permission is revoked mid-session -- that is this page,
        // so back() 302s to itself until the browser gives up. The two guarded
        // routes can also ping-pong off each other's stored previous URL.
        if (! \Auth::user()->can('manage booking')) {
            return redirect()->route('dashboard')->with('error', __('Permission Denied.'));
        }

        $bookingRequests = BookingRequest::with(['guest', 'car'])->latest()->get();

        return Inertia::render('BookingRequest/Index', [
            'bookingRequests' => $bookingRequests->map(fn($br) => [
                'id'           => $br->id,
                'encrypted_id' => Crypt::encrypt($br->id),
                'guest_name'   => $br->guest?->name,
                'car_name'     => $br->car?->name,
                'start_date'   => $br->start_date,
                'end_date'     => $br->end_date,
                'status'       => $br->status ?? 'pending',
            ]),
        ]);
    }

    public function show($id)
    {
        // See the note in index(): back() on a GET guard can redirect to itself.
        if (! \Auth::user()->can('manage booking')) {
            return redirect()->route('dashboard')->with('error', __('Permission Denied.'));
        }

        $bookingId = is_string($id) ? Crypt::decrypt($id) : $id;
        $booking = BookingRequest::with(['guest', 'car', 'pickupPlace', 'dropOffPlace'])->findOrFail($bookingId);

        return Inertia::render('BookingRequest/Show', [
            'booking' => [
                'id'           => $booking->id,
                'status'       => $booking->status ?? 'pending',
                'guest_name'   => $booking->guest?->name,
                'guest_email'  => $booking->guest?->email,
                'guest_phone'  => $booking->guest?->phone_number,
                'car_name'     => $booking->car?->name,
                'daily_rate'   => $booking->car?->daily_rate,
                'start_date'   => $booking->start_date,
                'start_time'   => $booking->start_time,
                'end_date'     => $booking->end_date,
                'end_time'     => $booking->end_time,
                'pickup_place' => $booking->pickupPlace?->name,
                'dropoff_place'=> $booking->dropOffPlace?->name,
                'notes'        => $booking->notes,
                // Optional details the storefront may have collected. Null for
                // every request taken before they existed, and for the simpler
                // form that does not ask -- the page renders a dash.
                'age'                => $booking->age,
                'nationality'        => $booking->nationality,
                'driving_experience' => $booking->driving_experience,
                'passengers'         => $booking->passengers,
                'whatsapp'           => $booking->whatsapp,
                'payment_preference' => $booking->payment_preference,
            ],
        ]);
    }

    /**
     * The next booking number for this tenant.
     *
     * Reads `bookings`, not `booking_requests`. It used to read the latter --
     * whose `booking_id` column nothing writes, under a parent_id nothing set
     * -- so it returned 1 unconditionally and every booking approved from a
     * request was numbered 1. Mirrors BookingController::bookingNumber(),
     * which is what numbers a booking created by hand.
     */
    public function bookingNumber()
    {
        // Ordered by booking_id, not by latest(). created_at is second-precision
        // with no unique index on booking_id, and the Excel import creates many
        // bookings inside one second -- among those the tie-break is arbitrary,
        // so latest() can return a row that is not the highest-numbered and the
        // next approval reuses a number. BookingController::bookingNumber() has
        // the same shape and the same flaw; it is left for its own ticket
        // rather than bundled into a change about booking requests.
        //
        // This does not make the read safe under concurrency: two staff
        // approving at the same moment still read the same maximum. Closing
        // that needs a lock at both call sites.
        $latest = Booking::where('parent_id', tenantKey())->orderByDesc('booking_id')->first();
        if (!$latest) {
            return 1;
        }
        return $latest->booking_id + 1;
    }
    public function confirmBooking($id)
    {
        Log::info("ConfirmBooking started for booking_request_id: {$id}");

        if (!\Auth::user()->can('create booking')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        try {
            DB::beginTransaction();
            Log::info("Transaction started for booking_request_id: {$id}");

            // Fetch booking request with guest and car
            $bookingRequest = BookingRequest::with(['guest', 'car'])->find($id);

            if (!$bookingRequest) {
                Log::error("BookingRequest not found: ID {$id}");
                return redirect()->back()->with('error', __('Booking request not found.'));
            }

            $car = $bookingRequest->car;
            if (!$car) {
                Log::error("BookingRequest {$id} has no associated car.");
                return redirect()->back()->with('error', __('Vehicle associated with this booking request not found.'));
            }

            $guest = $bookingRequest->guest;
            if (!$guest) {
                Log::error("BookingRequest {$id} has no associated guest.");
                return redirect()->back()->with('error', __('Guest associated with this booking request not found.'));
            }

            // Store guest as user if not exists
            $user = \App\Models\User::firstOrCreate(
                ['email' => $guest->email],
                [
                    'name' => $guest->name,
                    'phone_number' => $guest->phone_number,
                    'password' => Hash::make(Str::random(8)),
                    'type' => 'customer',
                    'lang' => app()->getLocale(),
                    'email_verified_at' => now(),
                ]
            );
            Log::info("User confirmed: {$user->email}");

            // Calculate booking amount
            $start = new \DateTime("{$bookingRequest->start_date} {$bookingRequest->start_time}");
            $end = new \DateTime("{$bookingRequest->end_date} {$bookingRequest->end_time}");
            $days = max(1, $end->diff($start)->days);
            $amount = $days * ($car->daily_rate ?? 0);

            // Create booking
            $booking = new \App\Models\Booking();
            $booking->vehicle = $car->id;
            $booking->booking_id = $this->bookingNumber();
            $booking->parent_id = tenantKey();
            $booking->driver = $user->id;
            $booking->start_date = $bookingRequest->start_date;
            $booking->start_time = $bookingRequest->start_time;
            $booking->end_date = $bookingRequest->end_date;
            $booking->end_time = $bookingRequest->end_time;
            $booking->pickup_address = $bookingRequest->pickup_address;
            $booking->drop_off_address = $bookingRequest->drop_off_address;
            $booking->status = 'confirmed';
            $booking->amount = $amount;
            $booking->payment_status = 'impaye';
            $booking->notes = $bookingRequest->notes;
            $booking->details = null;
            $booking->vehicle_details = [
                'id' => $car->id,
                'name' => $car->name ?? 'unknown',
                'license_plate' => $car->license_plate ?? 'unknown',
                'type' => $car->type ?? 'unknown',
            ];
            $booking->daily_price_final = $car->daily_rate ?? 0;
            $booking->save();

            // Update booking request status
            $bookingRequest->status = 'confirmed';
            $bookingRequest->save();

            DB::commit();
            Log::info("Booking confirmed with ID: {$booking->id}");

            // Redirect to booking details page
            return redirect()->route('booking.show', Crypt::encrypt($booking->id))
                ->with('success', __('Booking confirmed successfully!'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error confirming booking request {$id}: {$e->getMessage()}");
            return redirect()->back()->with('error', 'Error confirming booking: ' . $e->getMessage());
        }
    }
    public function refuseBooking($id)
    {
        if (!\Auth::user()->can('delete booking')) {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

        $bookingRequest = BookingRequest::find($id);

        if (!$bookingRequest) {
            return redirect()->back()->with('error', __('Booking request not found.'));
        }

        $bookingRequest->status = 'refused';
        $bookingRequest->save();

        return redirect()->back()->with('success', __('Booking request refused successfully.'));
    }




}
