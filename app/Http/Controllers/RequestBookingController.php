<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Guest;
use App\Models\BookingRequest;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RequestBookingController extends Controller
{
    /**
     * Display the specific car details and similar cars
     */
    public function show($id)
    {
        // Get the specific car with all relevant details
        $car = Vehicle::where('id', $id)->firstOrFail();
        
        // Get similar cars (same type or similar features)
        $similarCars = Vehicle::where('id', '!=', $id)
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

    $places = Place::all();

    return view('client.tests.car-details', compact('car', 'similarCars', 'places'));    }

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

        $booking->vehicle_name = $vehicle->name;
        $booking->vehicle_model = $vehicle->model;
        $booking->vehicle_type = $vehicle->types->type ?? $vehicle->type;
        $booking->vehicle_fuel_type = $vehicle->fuel_type;
        $booking->vehicle_gearbox = $vehicle->gearbox;
        $booking->vehicle_seats = $vehicle->number_of_seats;
        $booking->vehicle_license_plate = $vehicle->license_plate;
        $booking->vehicle_year = $vehicle->year_of_first_immatriculation;        
        $booking->save();

        DB::commit();

        return redirect()->back()->with('success', 'Booking request submitted successfully! We will contact you soon.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'An error occurred. Please try again. Error: ' . $e->getMessage());
    }
}

}