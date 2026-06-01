<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
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
use Inertia\Inertia;

class RequestBookingController extends Controller
{
    /**
     * Display the specific car details and similar cars
     */
    public function showSimilarCars($id)
    {
        $car = Vehicle::with('types')->where('id', $id)->firstOrFail();

        $similarCars = Vehicle::with('types')->where('id', '!=', $id)
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

        $places = Place::all(['id', 'name', 'city']);

        if (config('app.inertia_enabled')) {
            return Inertia::render('Public/CarDetails', compact('car', 'similarCars', 'places'));
        }

        return view('client.tests.car-details', compact('car', 'similarCars', 'places'));
    }

    /**
     * Process the booking request
     */

     public function storeBooking(Request $request)
     {
         // Validate the request
         $validator = Validator::make($request->all(), [
             'vehicle_id'       => 'required|exists:vehicles,id',
             'name'             => 'required|string|max:255',
             'email'            => 'required|email',
             'phone_number'     => 'required|string|max:20',
             'pickup_address'   => 'required|exists:places,id',
             'drop_off_address' => 'required|exists:places,id',
             'start_date'       => 'required|date',
             'end_date'         => 'required|date|after:start_date',
             'start_time'       => 'required',
             'end_time'         => 'required',
             'driver'           => 'nullable|boolean',
             'notes'            => 'nullable|string',
             'company_name'     => 'nullable|string',
             'city'             => 'nullable|string',
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
             $guest->subscription = 0;

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

             return redirect()->back()->with('success', 'Booking request submitted successfully! We will contact you soon.');
         } catch (\Exception $e) {
             DB::rollBack();
             return redirect()->back()->with('error', 'An error occurred. Please try again. Error: ' . $e->getMessage());
         }
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
            'settings' => settings(),
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
                    'subscription' => 0,
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
