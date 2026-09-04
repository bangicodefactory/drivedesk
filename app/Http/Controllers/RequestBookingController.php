<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Guest;
use App\Models\Booking;
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
        $vehiclesQuery = Vehicle::select('id', 'name', 'model', 'daily_rate', 'number_of_seats', 'gearbox', 'fuel_type', 'picture');

        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        if ($startDate && $endDate) {
            $start = $startDate . ' ' . ($request->query('start_time') ?: '00:00') . ':00';
            $end   = $endDate . ' ' . ($request->query('end_time') ?: '23:59') . ':00';

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

        return Inertia::render('Public/Booking/Index', [
            'vehicles'           => $vehiclesQuery->get(),
            'places'             => $places,
            'preselectedVehicle' => $request->query('vehicle'),
        ]);
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
             // Collected by the /reserve wizard's customer-details step.
             // Nullable: CarDetails.jsx's simpler form doesn't send these.
             'age'                => 'nullable|integer|min:18|max:100',
             'nationality'        => 'nullable|string|max:80',
             'driving_experience' => 'nullable|integer|min:0|max:80',
             'passengers'         => 'nullable|integer|min:1|max:9',
             'whatsapp'           => 'nullable|string|max:30',
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
             $booking->age = $request->age;
             $booking->nationality = $request->nationality;
             $booking->driving_experience = $request->driving_experience;
             $booking->passengers = $request->passengers;
             $booking->whatsapp = $request->whatsapp;

             $booking->vehicle_details = json_encode([
                'name'          => $vehicle->name,
                'model'         => $vehicle->model,
                'type'          => $vehicle->types->type ?? $vehicle->type,
                'fuel_type'     => $vehicle->fuel_type,
                'gearbox'       => $vehicle->gearbox,
                'seats'         => $vehicle->number_of_seats,
                'license_plate' => $vehicle->license_plate,
                'year'          => $vehicle->year_of_first_immatriculation,
            ]);
            
             $booking->save();

             DB::commit();

             // Signed so a guest can't view another booking request's details by
             // guessing/incrementing the id — booking_requests carries name/email/
             // phone and isn't otherwise tenant- or auth-scoped for a guest.
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
        ]);
    }

    /**
     * Display a listing of the booking requests.
     */
    public function index()
    {
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
            ],
        ]);
    }

    public function bookingNumber()
    {
        $latest = BookingRequest::where('parent_id', parentId())->latest()->first();
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
            $booking->parent_id = parentId();
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
