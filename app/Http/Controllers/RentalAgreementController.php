<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Notification;
use App\Models\Place;
use App\Models\RentalAgreement;
use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\Signature;
use Illuminate\Support\Facades\Storage;

class RentalAgreementController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage rental agreement')) {
            $agreements = RentalAgreement::where('parent_id', parentId())->orderBy('created_at', 'desc')->get();
            return view('rental_agreement.index', compact('agreements'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create rental agreement')) {
            $vehicles = Vehicle::where('parent_id', parentId())->orderBy('created_at', 'desc')->get();

            $drivers = User::where('parent_id', parentId())
            ->where('type', 'driver')
            ->orderBy('created_at', 'desc')
            ->get();            
             $driversDropdown = ['' => __('Select Driver')] + $drivers->pluck('name', 'id')->toArray();


            $status = RentalAgreement::$status;
            return view('rental_agreement.create', compact('vehicles', 'driversDropdown', 'status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }

    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create rental agreement')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'vehicle' => 'required',
                    'rental_start_date' => 'required',
                    'rental_end_date' => 'required',
                    'rental_duration' => 'required',
                    'rental_start_time' => 'required',
                    'rental_end_time' => 'required',
                    'driver' => 'required',
                    'create_booking' => 'required|boolean',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

                    // Combine date and time
        $start_datetime = $request->rental_start_date . ' ' . $request->rental_start_time;
        $end_datetime = $request->rental_end_date . ' ' . $request->rental_end_time;

            $rentalAgreement = new RentalAgreement();
            $rentalAgreement->agreement_id = $this->agreementNumber();
            $rentalAgreement->date = date('Y-m-d');
            $rentalAgreement->rental_start_date = $start_datetime;
            $rentalAgreement->rental_end_date = $end_datetime;
            // $rentalAgreement->rental_start_date = $request->rental_start_date;
            // $rentalAgreement->rental_end_date = $request->rental_end_date;
            $rentalAgreement->rental_duration = $request->rental_duration;
            $rentalAgreement->vehicle = $request->vehicle;
            $rentalAgreement->driver = $request->driver;
            // Add driver 2 
            $rentalAgreement->driver2 = $request->driver2 ?? null;
            $rentalAgreement->terms_condition = $request->terms_condition;
            $rentalAgreement->description = $request->description;
            $rentalAgreement->status = $request->status;
            $rentalAgreement->parent_id = parentId();
            $rentalAgreement->save();

            $user = User::find($request->driver);
            $module = 'new_agreement';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $setting = settings();
            $errorMessage = '';
            if (!empty($notification) && $notification->enabled_email == 1) {
                $notification_responce = MessageReplace($notification, $rentalAgreement->id);
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

            // Create booking if not exists (check by boolean field in rental agreement)
            if ($request->create_booking == 1) {
                $booking = new Booking();
                $booking->booking_id = (new BookingController())->bookingNumber();
                $booking->vehicle = $request->vehicle;
                $booking->driver = $request->driver;

                // Form sends separate date/time fields; use them directly (no explode)
                if (!empty($request->rental_start_date)) {
                    $booking->start_date = $request->rental_start_date;
                    $booking->start_time = $request->rental_start_time ?? null;
                }
                if (!empty($request->rental_end_date)) {
                    $booking->end_date = $request->rental_end_date;
                    $booking->end_time = $request->rental_end_time ?? null;
                }

                // Use an existing place (e.g. Tetouan) or first place for parent; fallback 0
                $defaultPlaceId = Place::where('parent_id', parentId())
                    ->where(function ($q) {
                        $q->where('name', 'Tetouan')->orWhere('city', 'Tetouan');
                    })
                    ->value('id')
                    ?? Place::where('parent_id', parentId())->value('id')
                    ?? 0;
                $booking->pickup_address = $defaultPlaceId;
                $booking->drop_off_address = $defaultPlaceId;
                $booking->status = 'Yet to Start';
                $booking->payment_status = 'impaye';
                $booking->notes = null;

                // rental_duration = daily price (from form); considerDays = computed from start/end date
                $dailyPrice = (float) $request->rental_duration;
                $start_datetime = $request->rental_start_date . ' ' . ($request->rental_start_time ?? '00:00');
                $end_datetime = $request->rental_end_date . ' ' . ($request->rental_end_time ?? '00:00');
                $rateData = vehicleRateCalculation($dailyPrice, $start_datetime, $end_datetime);
                $considerDays = (int) $rateData['considerDays'];
                $totalRate = (float) $rateData['totalRate'];

                $booking->details = json_encode([
                    'considerDays' => $considerDays,
                    'totalRate'    => $totalRate,
                ]);
                $booking->amount = (int) round($totalRate);

                $vehicle = Vehicle::find($request->vehicle);
                $booking->vehicle_details = [
                    'id' => $vehicle->id,
                    'name' => $vehicle->name,
                    'license_plate' => $vehicle->license_plate,
                ];
                $booking->parent_id = parentId();
                $booking->daily_price_final = $dailyPrice;
                $booking->save();
            }





            return redirect()->route('rental-agreement.index')->with('success', __('Rental agreement successfully created.') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show($ids)
    {
        if (\Auth::user()->can('show rental agreement')) {
            $id = Crypt::decrypt($ids);
            $rentalAgreement = RentalAgreement::find($id);
            $user_1 = User::find($rentalAgreement->driver);
            $driver_2 = $rentalAgreement->driver2 ? Driver::where('user_id', $rentalAgreement->driver2)->first() : null;
            $user_2 = $rentalAgreement->driver2 ? User::where('id', $rentalAgreement->driver2)->first() : null;
            $settings = settings();

            // display Terms and conditions 
            $terms = str_replace('\n', "\n", config('default_terms.rental_agreement'));
            $terms = nl2br($terms);

            //display Signature
            $driver1Signature = $this->getUserSignature($rentalAgreement->driver);
            $driver2Signature = $this->getUserSignature($rentalAgreement->driver2);


            return view('rental_agreement.show', compact('rentalAgreement', 'settings', 'driver_2', 'user_2', 'user_1' , 'terms','driver1Signature','driver2Signature'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function edit(RentalAgreement $rentalAgreement)
    {
        if (\Auth::user()->can('edit rental agreement')) {
            $vehicles = Vehicle::where('parent_id', parentId())->get();

            $drivers = User::where('parent_id', parentId())->where('type', 'driver')->get()->pluck('name', 'id');
            $drivers->prepend(__('Select Driver'), '');

            $status = RentalAgreement::$status;

            $driver2 = $rentalAgreement->driver2;

            return view('rental_agreement.edit', compact('vehicles', 'drivers', 'rentalAgreement', 'status', 'driver2'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function update(Request $request, RentalAgreement $rentalAgreement)
    {
        if (\Auth::user()->can('edit rental agreement')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'vehicle' => 'required',
                    'rental_start_date' => 'required',
                    'rental_end_date' => 'required',
                    'rental_duration' => 'required',
                    'rental_start_time' => 'required',
                    'rental_end_time' => 'required',
                    'driver' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

                    // Combine date and time
        $start_datetime = $request->rental_start_date . ' ' . $request->rental_start_time;
        $end_datetime = $request->rental_end_date . ' ' . $request->rental_end_time;


            $agreementStatus = $rentalAgreement->status != $request->status;

            $rentalAgreement->rental_start_date = $start_datetime;
            $rentalAgreement->rental_end_date = $end_datetime;
            $rentalAgreement->rental_duration = $request->rental_duration;
            $rentalAgreement->vehicle = $request->vehicle;
            $rentalAgreement->driver = $request->driver;
            $rentalAgreement->driver2 = $request->driver2; // Update driver2
            $rentalAgreement->terms_condition = $request->terms_condition;
            $rentalAgreement->description = $request->description;
            $rentalAgreement->status = $request->status;
            $rentalAgreement->save();

            if ($agreementStatus) {
                $user = User::find($request->driver);
                $module = 'agreement_status';
                $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
                $setting = settings();
                $errorMessage = '';
                if (!empty($notification) && $notification->enabled_email == 1) {
                    $notification_responce = MessageReplace($notification, $rentalAgreement->id);
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
            $errorMessage=!empty($errorMessage)?$errorMessage:'';


            return redirect()->route('rental-agreement.index')->with('success', __('Rental agreement successfully updated.').'</br>'.$errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(RentalAgreement $rentalAgreement)
    {
        if (\Auth::user()->can('delete rental agreement')) {
            $rentalAgreement->delete();
            return redirect()->route('rental-agreement.index')->with('success', __('Rental agreement successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function agreementNumber()
    {
        $latest = RentalAgreement::where('parent_id', parentId())->latest()->first();
        if (!$latest) {
            return 1;
        }
        return $latest->agreement_id + 1;
    }

    /**
 * Get the latest signature for a user
 * 
 * @param int $userId
 * @return string|null Path to signature file
 */
private function getUserSignature($userId)
{
    // $signature = Signature::where('user_id', $userId)
    //                ->latest()
    //                ->first();
    
    // if ($signature && Storage::disk('public')->exists($signature->signature_path)) {
    //     // return Storage::disk('public')->path($signature->signature_path);
    //     return Storage::disk('public')->url( $signature->signature_path);
    // }
    
    // return null;

    $signature = Signature::where('user_id', $userId)
                 ->latest()
                 ->first();
    
    if ($signature && Storage::disk('public')->exists($signature->signature_path)) {
        // Convert to base64 for reliable printing
        $imagePath = Storage::disk('public')->path($signature->signature_path);
        $imageData = file_get_contents($imagePath);
        $base64 = base64_encode($imageData);
        return 'data:image/png;base64,' . $base64;
    }
    
    return null;
}



}
