<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Driver;
use App\Models\Notification;
use App\Models\Place;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Tva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage booking')) {
            $bookings = Booking::where('parent_id', '=', parentId())->orderBy('created_at', 'desc')->get();
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        return view('booking.index', compact('bookings'));
    }


    public function create()
    {
        if (\Auth::user()->can('create booking')) {
            $vehicles = Vehicle::where('parent_id', parentId())->get();

            $drivers = User::where('parent_id', parentId())
                ->where('type', 'driver')
                ->orderBy('created_at', 'desc')
                ->get();
            $driversDropdown = ['' => __('Select Driver')] + $drivers->pluck('name', 'id')->toArray();


            $status = Booking::$status;
            $paymentStatus = Booking::$paymentStatus;

            $places = Place::where('parent_id', parentId())->get();
            $addon = Addon::where('parent_id', parentId())->get()->pluck('name', 'id');

            return view('booking.create', compact('vehicles', 'driversDropdown', 'status', 'paymentStatus', 'places', 'addon'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create booking')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'vehicle' => 'required',
                    'start_date_time' => 'required',
                    'end_date_time' => 'required',
                    'driver' => 'required',
                    'pickup_address' => 'required',
                    'drop_off_address' => 'required',
                    'status' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $vehicle_detail = Vehicle::find($request->vehicle);
            $booking = new Booking();
            $booking->booking_id = $this->bookingNumber();
            $booking->vehicle = $request->vehicle;
            $booking->driver = $request->driver;
            if (!empty($request->start_date_time)) {
                $startDateTime = explode(' ', $request->start_date_time);
                $booking->start_date = $startDateTime[0];
                $booking->start_time = $startDateTime[1];
            }
            if (!empty($request->end_date_time)) {
                $endDateTime = explode(' ', $request->end_date_time);
                $booking->end_date = $endDateTime[0];
                $booking->end_time = $endDateTime[1];
            }
            $booking->pickup_address = $request->pickup_address;
            $booking->drop_off_address = $request->drop_off_address;
            $booking->addon = !empty($request->addon) ? implode(',', $request->addon) : null;
            $booking->status = $request->status;
            $booking->notes = $request->notes;
            $booking->amount = $request->amount;
            $booking->payment_status = 'impaye';
            $booking->payment_notes = null;
            $booking->details = $request->details;
            $booking->vehicle_details = json_encode($vehicle_detail);
            $booking->parent_id = parentId();
            $booking->daily_price_final = !empty($request->daily_price) ? $request->daily_price : 0;
            $booking->save();


            $user = User::find($request->driver);
            $module = 'new_booking';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $setting = settings();
            $errorMessage = '';
            if (!empty($notification) && $notification->enabled_email == 1) {
                $notification_responce = MessageReplace($notification, $booking->id);
                $data['subject'] = $notification_responce['subject'];
                $data['message'] = $notification_responce['message'];
                $data['module'] = $module;
                $data['logo'] = $setting['company_logo'];
                $to = $user->email;

                $response = commonEmailSend($to, $data);
                if ($response['status'] == 'error') {
                    $errorMessage = $response['message'];
                }
            }

            //get address from drivers table
            $driver1 = Driver::where('user_id', $request->driver)->first();

            //calcul TOTAL HT and PUHT
            $totalht = round($booking->amount - ($booking->amount * 0.2), 2);
            $tva = round($booking->amount * 0.2, 2);
            //return consider Days
            $vehicleDetails = json_decode($booking->vehicle_details, true);
            $vehicle_name = $vehicleDetails['name'] ?? '';
            $vehicle_license_plate = $vehicleDetails['license_plate'] ?? '';
            // Calculate total days between start and end date
            $startDate = Carbon::parse($booking->start_date);
            $endDate = Carbon::parse($booking->end_date);
            $totalDays = $startDate->diffInDays($endDate);
            //store tva
            $tva = new Tva();
            $tva->facture_number = $booking->booking_id;
            $tva->facture_date = $booking->created_at;
            $tva->client_name = $user->name;
            $tva->client_address = $driver1 ? $driver1->address : '';
            $tva->company_name = $setting['company_name'];
            $tva->company_address = $setting['company_address'];
            $tva->designation = $vehicle_name . '-' . $vehicle_license_plate;
            $tva->quantity = $totalDays ?? 1; //days of booking
            $tva->total_ht = round($booking->getTotalAmount() * 0.8, 2);
            $tva->tva = round($booking->getTotalAmount() * 0.2, 2);
            // $tva->unit_price_ht = round($tva->total_ht / $tva->quantity, 2);
            $tva->unit_price_ht = $tva->quantity > 0 ? round($tva->total_ht / $tva->quantity, 2) : 0;
            $tva->montant_ttc = $booking->amount;
            $tva->ice_number = $setting['ice'];
            $tva->rc_number = $setting['rc'];
            // // $tva->tp_number = $setting['tp_number'];
            $tva->nif_number = $setting['if'];
            $tva->parent_id = parentId();
            $tva->booking_id = $booking->id;
            $tva->generated_date = now();
            $tva->save();



            return redirect()->route('booking.show', Crypt::encrypt($booking->id))->with('success', __('Booking successfully created.') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show($id)
    {
        if (\Auth::user()->can('show booking')) {
            $booking = Booking::find(Crypt::decrypt($id));
            $settings = settings();
            return view('booking.show', compact('booking', 'settings'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function edit($id)
    {
        if (\Auth::user()->can('edit booking')) {
            $booking = Booking::find(Crypt::decrypt($id));
            $booking->start_date_time = date('Y/m/d H:i', strtotime($booking->start_date . ' ' . $booking->start_time));
            $booking->end_date_time = date('Y/m/d H:i', strtotime($booking->end_date . ' ' . $booking->end_time));

            $drivers = User::where('parent_id', parentId())->where('type', 'driver')->get()->pluck('name', 'id');
            $drivers->prepend(__('Select Driver'), '');

            $status = Booking::$status;
            $paymentStatus = Booking::$paymentStatus;
            $places = Place::where('parent_id', parentId())->get();

            $addon = Addon::where('parent_id', parentId())->get()->pluck('name', 'id');

            $startDateTime = Carbon::createFromFormat('Y/m/d H:i', date('Y/m/d H:i', strtotime($booking->start_date_time)));
            $endDateTime = Carbon::createFromFormat('Y/m/d H:i', date('Y/m/d H:i', strtotime($booking->end_date_time)));

            $startDateTimeStr = $startDateTime->format('Y-m-d H:i:s');
            $endDateTimeStr = $endDateTime->format('Y-m-d H:i:s');

            $booked = Booking::where('id', '!=', $booking->id)->whereNotIn('status', ['completed', 'cancelled'])
                ->where(function ($query) use ($startDateTimeStr, $endDateTimeStr) {
                    $query->where(function ($query) use ($startDateTimeStr, $endDateTimeStr) {
                        $query->where(DB::raw('CONCAT(start_date, " ", start_time)'), '>=', $startDateTimeStr)->where(DB::raw('CONCAT(start_date, " ", start_time)'), '<=', $endDateTimeStr);
                    })->orWhere(function ($query) use ($startDateTimeStr, $endDateTimeStr) {
                        $query->where(DB::raw('CONCAT(end_date, " ", end_time)'), '>=', $startDateTimeStr)->where(DB::raw('CONCAT(end_date, " ", end_time)'), '<=', $endDateTimeStr);
                    })->orWhere(function ($query) use ($startDateTimeStr, $endDateTimeStr) {
                        $query->where(DB::raw('CONCAT(start_date, " ", start_time)'), '<=', $startDateTimeStr)->where(DB::raw('CONCAT(end_date, " ", end_time)'), '>=', $endDateTimeStr);
                    });
                })->distinct()->pluck('vehicle')->toArray();

            $vehicles = Vehicle::where('parent_id', parentId())->whereNotIn('id', $booked)->get();

            return view('booking.edit', compact('vehicles', 'drivers', 'status', 'booking', 'paymentStatus', 'places', 'addon'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function update(Request $request, Booking $booking)
    {
        if (\Auth::user()->can('create booking')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'vehicle' => 'required',
                    'start_date_time' => 'required',
                    'end_date_time' => 'required',
                    'driver' => 'required',
                    'pickup_address' => 'required',
                    'drop_off_address' => 'required',
                    'status' => 'required',
                    'amount' => 'required',
                    'daily_price' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $bookingStatus = $booking->status != $request->status;


            $vehicle_detail = Vehicle::find($request->vehicle);
            $booking->vehicle = $request->vehicle;
            $booking->driver = $request->driver;
            if (!empty($request->start_date_time)) {
                $startDateTime = explode(' ', $request->start_date_time);
                $booking->start_date = $startDateTime[0];
                $booking->start_time = $startDateTime[1];
            }
            if (!empty($request->end_date_time)) {
                $endDateTime = explode(' ', $request->end_date_time);
                $booking->end_date = $endDateTime[0];
                $booking->end_time = $endDateTime[1];
            }
            $booking->pickup_address = $request->pickup_address;
            $booking->drop_off_address = $request->drop_off_address;
            if (!empty($request->status)) {
                $booking->status = $request->status;
            }
            $booking->addon = !empty($request->addon) ? implode(',', $request->addon) : null;
            $booking->amount = $request->amount;
            $booking->payment_notes = null;
            $booking->details = $request->details;
            $booking->vehicle_details = json_encode($vehicle_detail);
            $booking->daily_price_final = $request->daily_price;
            $booking->save();

            //update dynamic with tva section
            $tva = Tva::where('booking_id', $booking->id)->first();
            if ($tva) {
                // Get totalDays from details object (now automatically cast from JSON)
                $details = $booking->details;
                // If it's a string (from request), decode it
                if (is_string($details)) {
                    $details = json_decode($details);
                }
             
                $quantity = isset($details->totalDays) ? $details->totalDays : 1;
                $unit_price_ht = $booking->daily_price_final;
                $total_ht = $unit_price_ht * $quantity;
                $tva_rate = 0.20; // 20% 
                $tva_amount = $total_ht * $tva_rate;
                $montant_ttc = $total_ht + $tva_amount;

                // $tva->designation = json_decode($booking->vehicle_details)->name ?? 'N/A'; 
                $tva->quantity = $quantity;
                $tva->unit_price_ht = $unit_price_ht;
                $tva->total_ht = $total_ht;
                $tva->tva = $tva_amount;
                $tva->montant_ttc = $montant_ttc;
                $tva->total_amount = $montant_ttc;
                $tva->tva_amount = $tva_amount;
                $tva->updated_at = now();
                $tva->save();
            }


            if ($bookingStatus) {
                $user = User::find($request->driver);
                $module = 'booking_status';
                $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
                $setting = settings();
                $errorMessage = '';
                if (!empty($notification) && $notification->enabled_email == 1) {
                    $notification_responce = MessageReplace($notification, $booking->id);
                    $data['subject'] = $notification_responce['subject'];
                    $data['message'] = $notification_responce['message'];
                    $data['module'] = $module;
                    $data['logo'] = $setting['company_logo'];
                    $to = $user->email;

                    $response = commonEmailSend($to, $data);
                    if ($response['status'] == 'error') {
                        $errorMessage = $response['message'];
                    }
                }
            }
            $errorMessage = !empty($errorMessage) ? $errorMessage : '';
            return redirect()->route('booking.index')->with('success', __('Booking successfully updated.') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }



    public function destroy(Booking $booking)
    {
        if (\Auth::user()->can('delete booking')) {
            // Delete associated TVA record first
            Tva::where('booking_id', $booking->id)->delete();

            // Then delete the booking
            $booking->delete();
            return redirect()->route('booking.index')->with('success', __('Booking successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function bookingNumber()
    {
        $latest = Booking::where('parent_id', parentId())->latest()->first();
        if (!$latest) {
            return 1;
        }
        return $latest->booking_id + 1;
    }

    public function paymentCreate($id)
    {
        $booking = Booking::find($id);
        $paymentMethod = BookingPayment::$paymentMethod;
        return view('booking.payment', compact('booking', 'paymentMethod'));
    }

    public function paymentStore(Request $request, $id)
    {
        if (\Auth::user()->can('create booking payment')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'amount' => 'required',
                    'date' => 'required',
                    'payment_method' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $payment = new BookingPayment();
            $payment->booking_id = $id;
            $payment->amount = $request->amount;
            $payment->date = $request->date;
            $payment->payment_method = $request->payment_method;
            $payment->notes = $request->notes;
            $payment->parent_id = parentId();
            $payment->save();
            $booking = Booking::find($id);
            if ($booking->getTotalDueAmount() <= 0) {
                $status = 'paye';
            } else {
                $status = 'partiellement_paye';
            }
            Booking::statusChange($booking->id, $status);
            return redirect()->back()->with('success', __('Booking payment successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function paymentDestroy($booking_id, $id)
    {
        if (\Auth::user()->can('delete booking payment')) {
            $payment = BookingPayment::find($id);
            $payment->delete();

            $bookinmg = Booking::find($booking_id);
            if ($bookinmg->getTotalDueAmount() <= 0) {
                $status = 'paye';
            } elseif ($bookinmg->getTotalDueAmount() == $bookinmg->getTotalAmount()) {
                $status = 'impaye';
            } else {
                $status = 'partiellement_paye';
            }
            Booking::statusChange($bookinmg->id, $status);
            return redirect()->back()->with('success', __('Booking payment successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    // public function planning()
    // {
    //     // Skip auth check for testing - replace with proper auth later
    //     // if (\Auth::user()->can('manage planning')) {

    //         // Temporarily hardcode parent_id to test (should use parentId() when authenticated properly)
    //         $parentId = 2;
    //         $bookings = Booking::where('parent_id', $parentId)->get();
    //         $vehicles = Vehicle::where('parent_id', $parentId)->get();

    //         // Simple vehicle data - one row per vehicle
    //         $vehicleData = [];
    //         foreach ($vehicles as $vehicle) {
    //             $vehicleArr = [
    //                 'id' => (string)$vehicle->id, // Ensure it's a string
    //                 'title' => $vehicle->name . ' - ' . $vehicle->license_plate,
    //             ];
    //             $vehicleData[] = $vehicleArr;
    //         }

    //         // Simple booking data - each booking on its vehicle's row
    //         $bookingData = [];
    //         foreach ($bookings as $booking) {
    //             $driver = !empty($booking->drivers) ? $booking->drivers->name : '';

    //             // Use hardcoded prefix instead of function for testing
    //             $prefix = 'BOK-'; // Replace with bookingPrefix() later

    //             $booked = [
    //                 'id' => $booking->id,
    //                 'resourceId' => (string)$booking->vehicle, // Ensure it's a string and matches vehicle ID
    //                 'title' => $prefix . sprintf('%04d', $booking->booking_id) . ' - ' . $driver,
    //                 'start' => $booking->start_date . 'T' . $booking->start_time,
    //                 'end'   => $booking->end_date . 'T' . $booking->end_time,
    //                 'url' => route('booking.show', Crypt::encrypt($booking->id)),
    //             ];
    //             $bookingData[] = $booked;
    //         }

    //         return view('booking.planning', compact('bookingData', 'vehicleData'));
    //     // } else {
    //     //     return redirect()->back()->with('error', __('Permission Denied.'));
    //     // }
    // }

    public function planning()
    {
        // Temporarily disable auth for testing
        // if (\Auth::user()->can('manage planning')) {
        $parentId = 2; // Use hardcoded parentId for testing
        $bookings = Booking::where('parent_id', $parentId)->get();
        $vehicles = Vehicle::where('parent_id', $parentId)->get();

        $vehicleData = [];
        foreach ($vehicles as $vehicle) {
            $vehicleArr = [
                'id' => (string)$vehicle->id, // Ensure string type
                'title' => $vehicle->name . ' - ' . $vehicle->license_plate,
            ];
            $vehicleData[] = $vehicleArr;
        }

        $bookingData = [];
        foreach ($bookings as $booking) {
            $driver = !empty($booking->drivers) ? $booking->drivers->name : '';
            $booked = [
                'id' => $booking->id,
                'resourceId' => (string)$booking->vehicle, // Ensure string type to match vehicle ID
                'title' => 'BOK-' . sprintf('%04d', $booking->booking_id) . ' - ' . $driver,
                'start' => $booking->start_date . 'T' . $booking->start_time,
                'end'   => $booking->end_date . 'T' . $booking->end_time,
                'url' => route('booking.show', Crypt::encrypt($booking->id)),
            ];
            $bookingData[] = $booked;
        }

        return view('booking.planning', compact('bookingData', 'vehicleData'));
        // } else {
        //     return redirect()->back()->with('error', __('Permission Denied.'));
        // }
    }
    public function testPlanning()
    {
        try {
            // Test planning method without authentication
            $parentId = 2;
            $bookings = Booking::where('parent_id', $parentId)->get();
            $vehicles = Vehicle::where('parent_id', $parentId)->get();

            // Debug: Check what we got
            $debug = [
                'bookings_count' => $bookings->count(),
                'vehicles_count' => $vehicles->count(),
                'bookings_sample' => $bookings->take(2)->map(function ($b) {
                    return [
                        'id' => $b->id,
                        'booking_id' => $b->booking_id,
                        'vehicle' => $b->vehicle,
                        'start_date' => $b->start_date,
                        'end_date' => $b->end_date
                    ];
                }),
                'vehicles_sample' => $vehicles->take(2)->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'name' => $v->name,
                        'license_plate' => $v->license_plate
                    ];
                })
            ];

            return response()->json($debug);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
