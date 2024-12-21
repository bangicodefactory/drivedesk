<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Option;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage vehicle')) {
            $vehicles = Vehicle::where('parent_id', '=', parentId())->get();
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        return view('vehicle.index', compact('vehicles'));
    }


    public function create()
    {
        $types = VehicleType::where('parent_id', parentId())->get()->pluck('type', 'id');
        $types->prepend(__('Select Type'), '');
        $gearbox = Vehicle::$gearbox;
        $fuelType = Vehicle::$fuelType;
        $option = Option::where('parent_id', parentId())->get()->pluck('name', 'id');
        return view('vehicle.create', compact('types', 'fuelType', 'gearbox', 'option'));
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create vehicle')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'type' => 'required',
                    'name' => 'required',
                    'model' => 'required',
                    'engine_type' => 'required',
                    'engine_no' => 'required',
                    'license_plate' => 'required',
                    'registration_expiry_date' => 'required',
                    'document' => 'required',
                    'daily_rate' => 'required',
                    'year_of_ﬁrst_immatriculation' => 'required',
                    'gearbox' => 'required',
                    'fuel_type' => 'required',
                    'number_of_seats' => 'required',
                    'kilometers' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $vehicle = new Vehicle();
            $vehicle->vehicle_id = $this->vehicleNumber();
            $vehicle->type = $request->type;
            $vehicle->name = $request->name;
            $vehicle->model = $request->model;
            $vehicle->engine_type = $request->engine_type;
            $vehicle->engine_no = !empty($request->engine_no) ? $request->engine_no : null;
            $vehicle->registration_expiry_date = !empty($request->registration_expiry_date) ? $request->registration_expiry_date : null;
            $vehicle->license_plate = $request->license_plate;
            $vehicle->document = $request->document;
            $vehicle->daily_rate = $request->daily_rate;
            $vehicle->year_of_ﬁrst_immatriculation = !empty($request->year_of_ﬁrst_immatriculation) ? $request->year_of_ﬁrst_immatriculation : 0;
            $vehicle->gearbox = $request->gearbox;
            $vehicle->fuel_type = $request->fuel_type;
            $vehicle->number_of_seats = $request->number_of_seats;
            $vehicle->kilometers = $request->kilometers;
            $vehicle->option = !empty($request->option) ? implode(',', $request->option) : null;
            $vehicle->notes = !empty($request->notes) ? $request->notes : null;
            $vehicle->parent_id = parentId();
            if (!empty($request->document)) {
                $documentFilenameWithExt = $request->file('document')->getClientOriginalName();
                $documentFilename = pathinfo($documentFilenameWithExt, PATHINFO_FILENAME);
                $documentExtension = $request->file('document')->getClientOriginalExtension();
                $documentFileName = $documentFilename . '_' . time() . '.' . $documentExtension;
                $dir = storage_path('upload/document');
                $image_path = $dir . $documentFilenameWithExt;
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('document')->storeAs('upload/document/', $documentFileName);
                $vehicle->document = $documentFileName;
            }
            $vehicle->save();


            return redirect()->route('vehicle.index')->with('success', __('Vehicle successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(Vehicle $vehicle)
    {
        return view('vehicle.show', compact('vehicle'));
    }


    public function edit(Vehicle $vehicle)
    {
        $gearbox = Vehicle::$gearbox;
        $fuelType = Vehicle::$fuelType;
        $types = VehicleType::where('parent_id', parentId())->get()->pluck('type', 'id');
        $types->prepend(__('Select Type'), '');
        $option = Option::where('parent_id', parentId())->get()->pluck('name', 'id');
        return view('vehicle.edit', compact('types', 'vehicle', 'gearbox', 'fuelType', 'option'));
    }


    public function update(Request $request, Vehicle $vehicle)
    {
        if (\Auth::user()->can('edit vehicle')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'type' => 'required',
                    'name' => 'required',
                    'model' => 'required',
                    'engine_type' => 'required',
                    'license_plate' => 'required',
                    'daily_rate' => 'required',
                    'gearbox' => 'required',
                    'fuel_type' => 'required',
                    'number_of_seats' => 'required',
                    'kilometers' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $vehicle->type = $request->type;
            $vehicle->name = $request->name;
            $vehicle->model = $request->model;
            $vehicle->engine_type = $request->engine_type;
            $vehicle->engine_no = !empty($request->engine_no) ? $request->engine_no : null;
            $vehicle->registration_expiry_date = !empty($request->registration_expiry_date) ? $request->registration_expiry_date : null;
            $vehicle->license_plate = $request->license_plate;
            $vehicle->daily_rate = $request->daily_rate;
            $vehicle->year_of_ﬁrst_immatriculation = !empty($request->year_of_ﬁrst_immatriculation) ? $request->year_of_ﬁrst_immatriculation : 0;
            $vehicle->gearbox = $request->gearbox;
            $vehicle->fuel_type = $request->fuel_type;
            $vehicle->number_of_seats = $request->number_of_seats;
            $vehicle->kilometers = $request->kilometers;
            $vehicle->option = !empty($request->option) ? implode(',', $request->option) : null;
            $vehicle->notes = $request->notes;
            if (!empty($request->document)) {
                $documentFilenameWithExt = $request->file('document')->getClientOriginalName();
                $documentFilename = pathinfo($documentFilenameWithExt, PATHINFO_FILENAME);
                $documentExtension = $request->file('document')->getClientOriginalExtension();
                $documentFileName = $documentFilename . '_' . time() . '.' . $documentExtension;
                $dir = storage_path('upload/document');
                $image_path = $dir . $documentFilenameWithExt;
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('document')->storeAs('upload/document/', $documentFileName);
                $vehicle->document = $documentFileName;
            }

            $vehicle->save();

            return redirect()->route('vehicle.index')->with('success', __('Vehicle successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(Vehicle $vehicle)
    {
        if (\Auth::user()->can('delete vehicle')) {
            $vehicle->delete();
            return redirect()->route('vehicle.index')->with('success', __('Vehicle successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function vehicleNumber()
    {
        $latest = Vehicle::where('parent_id', parentId())->latest()->first();
        if (!$latest) {
            return 1;
        }
        return $latest->vehicle_id + 1;
    }

    public function getVehicleRateCalculation(Request $request)
    {
        $vehicle = Vehicle::find($request->vahicle_id);
        $start_date_time = $request->start_date_time;
        $end_date_time = $request->end_date_time;
        $addons = $request->addons;

        $pickup_place = $request->pickup_place;
        $drop_off_place = $request->drop_off_place;

        if (!empty($pickup_place)) {
            $pickupPlaceAmount = placesRateCalculation($pickup_place);
        } else {
            $pickupPlaceAmount = 0;
        }

        if (!empty($drop_off_place)) {
            $dropPlaceAmount = placesRateCalculation($drop_off_place);
        } else {
            $dropPlaceAmount = 0;
        }
        $placeAmount = $pickupPlaceAmount + $dropPlaceAmount;
        if (!empty($vehicle) && !empty($start_date_time) && !empty($end_date_time)) {
            $daily_rate = !empty($vehicle->daily_rate) && ($vehicle->daily_rate > 0) ? $vehicle->daily_rate : 0;
            $data = vehicleRateCalculation($daily_rate, $start_date_time, $end_date_time);

            $addonAmount = 0;
            if (!empty($addons)) {
                $addonAmount = addonsRateCalculation($request->addons, $data['considerDays']);
                $specificAddonCalculation = specificAddonCalculation($request->addons, $data['considerDays']);
                $specificAddonString = '';
                foreach ($specificAddonCalculation as $key => $value) {
                    $specificAddonString .= "<tr><td>" . $value['addon'] . "</td><td>" . $value['final_price'] . "</td></tr>";
                }
                $data['specificAddonCalculation'] = $specificAddonString;
            }

            $data['addonAmount'] = $addonAmount;
            $data['placeAmount'] = $placeAmount;
            $data['duration'] = $data['considerDays'] . ' * ' . $daily_rate . ' = ' . priceFormat($data['totalRate']);

            return json_encode($data);
        }
    }

    public function getAvailableVehicle(Request $request)
    {
        $start_date_time = $request->start_date_time;
        $end_date_time = $request->end_date_time;
        if (!empty($start_date_time) && !empty($end_date_time)) {
            $startDateTime = Carbon::createFromFormat('Y/m/d H:i', $start_date_time);
            $endDateTime = Carbon::createFromFormat('Y/m/d H:i', $end_date_time);

            $startDateTimeStr = $startDateTime->format('Y-m-d H:i:s');
            $endDateTimeStr = $endDateTime->format('Y-m-d H:i:s');

            $booking = Booking::whereNotIn('status', ['completed', 'cancelled']);
            if (isset($request->booking_id) && !empty($request->booking_id)) {
                $booking->where('id', '!=', $request->booking_id);
            }
            $booking = $booking->where(function ($query) use ($startDateTimeStr, $endDateTimeStr) {
                $query->where(function ($query) use ($startDateTimeStr, $endDateTimeStr) {
                    $query->where(DB::raw('CONCAT(start_date, " ", start_time)'), '>=', $startDateTimeStr)->where(DB::raw('CONCAT(start_date, " ", start_time)'), '<=', $endDateTimeStr);
                })->orWhere(function ($query) use ($startDateTimeStr, $endDateTimeStr) {
                    $query->where(DB::raw('CONCAT(end_date, " ", end_time)'), '>=', $startDateTimeStr)->where(DB::raw('CONCAT(end_date, " ", end_time)'), '<=', $endDateTimeStr);
                })->orWhere(function ($query) use ($startDateTimeStr, $endDateTimeStr) {
                    $query->where(DB::raw('CONCAT(start_date, " ", start_time)'), '<=', $startDateTimeStr)->where(DB::raw('CONCAT(end_date, " ", end_time)'), '>=', $endDateTimeStr);
                });
            })->distinct()->pluck('vehicle')->toArray();

            $vehicles = Vehicle::where('parent_id', parentId())->whereNotIn('id', $booking)->get();
            $data = [];


            foreach ($vehicles as $vehicle) {
                $data[$vehicle->id] = $vehicle->name . ' - ' . $vehicle->license_plate;
            }

            return json_encode($data);
        }
    }
}
