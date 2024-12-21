<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\InspectionType;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class InspectionController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage inspection')) {
            $inspections = Inspection::where('parent_id', '=', parentId())->get();
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        return view('inspection.index', compact('inspections'));
    }


    public function create()
    {
        if (\Auth::user()->can('create inspection')) {
            $vehicles = Vehicle::where('parent_id', parentId())->get()->pluck('name', 'id');
            $vehicles->prepend(__('Select Vehicle'),'');

            $status=Inspection::$status;
            $repairStatus=Inspection::$repairStatus;
            $fuelLevel=Inspection::$fuelLevel;

            $types = InspectionType::where('parent_id', parentId())->get();
            return view('inspection.create', compact('vehicles','status','repairStatus','fuelLevel','types'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function store(Request $request)
    {

        if (\Auth::user()->can('create inspection')) {
            $validator = \Validator::make(
                $request->all(), [
                    'vehicle' => 'required',
                    'inspector' => 'required',
                    'inspection_date' => 'required',
                    'incoming_date' => 'required',
                    'meter_reading_incoming' => 'required',
                    'status' => 'required',
                    'repair_status' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $inspection = new Inspection();
            $inspection->inspector = $request->inspector;
            $inspection->inspection_date = $request->inspection_date;
            $inspection->vehicle = $request->vehicle;
            $inspection->meter_reading_incoming = !empty($request->meter_reading_incoming)?$request->meter_reading_incoming:0;
            $inspection->incoming_date = !empty($request->incoming_date)?$request->incoming_date:null;
            $inspection->details = !empty($request->types)?json_encode($request->types):'';
            $inspection->notes = $request->notes;
            $inspection->status = $request->status;
            $inspection->amount = $request->amount;
            $inspection->repair_status = $request->repair_status;
            $inspection->parent_id = parentId();
            if (!empty($request->receipt)) {
                $expenseFilenameWithExt = $request->file('receipt')->getClientOriginalName();
                $expenseFilename = pathinfo($expenseFilenameWithExt, PATHINFO_FILENAME);
                $expenseExtension = $request->file('receipt')->getClientOriginalExtension();
                $expenseFileName = $expenseFilename . '_' . time() . '.' . $expenseExtension;
                $dir = storage_path('upload/expense');
                $image_path = $dir . $expenseFilenameWithExt;
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('receipt')->storeAs('upload/expense/', $expenseFileName);
                $inspection->receipt = $expenseFileName;
            }
            $inspection->save();

            return redirect()->route('inspection.index')->with('success', __('Inspection successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show($id)
    {
        $inspection=Inspection::find(Crypt::decrypt($id));
        $checklists=!empty($inspection->details)?json_decode($inspection->details):[];

        $details=[];
        foreach ($checklists as $k=>$checklist){
            $type=InspectionType::find($k);
            $details[$k]['type']=$type->type;
            $details[$k]['status']=isset($checklist->type)?$checklist->type:'';
            $details[$k]['note']=isset($checklist->note)?$checklist->note:'';
        }

        return view('inspection.show', compact('inspection','details'));
    }


    public function edit($id)
    {
        $inspection=Inspection::find(Crypt::decrypt($id));
        if (\Auth::user()->can('edit inspection')) {
            $vehicles = Vehicle::where('parent_id', parentId())->get()->pluck('name', 'id');
            $vehicles->prepend(__('Select Vehicle'),'');
            $status=Inspection::$status;
            $repairStatus=Inspection::$repairStatus;
            $fuelLevel=Inspection::$fuelLevel;
            $types = InspectionType::where('parent_id', parentId())->get();

            $checklists=!empty($inspection->details)?json_decode($inspection->details):[];

            $details=[];
            foreach ($checklists as $k=>$checklist){
                $details[$k]['type']=isset($checklist->type)?$checklist->type:'';
                $details[$k]['note']=isset($checklist->note)?$checklist->note:'';
            }

            return view('inspection.edit', compact('inspection', 'vehicles','status','repairStatus','fuelLevel','types','details'));

        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function update(Request $request, Inspection $inspection)
    {
        if (\Auth::user()->can('edit inspection')) {
            $validator = \Validator::make(
                $request->all(), [
                    'vehicle' => 'required',
                    'inspector' => 'required',
                    'inspection_date' => 'required',
                    'incoming_date' => 'required',
                    'meter_reading_incoming' => 'required',
                    'status' => 'required',
                    'repair_status' => 'required',
                    'amount' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $inspection->inspector = $request->inspector;
            $inspection->inspection_date = $request->inspection_date;
            $inspection->vehicle = $request->vehicle;
            $inspection->meter_reading_incoming = !empty($request->meter_reading_incoming)?$request->meter_reading_incoming:0;
            $inspection->incoming_date = !empty($request->incoming_date)?$request->incoming_date:null;
            $inspection->details = !empty($request->types)?json_encode($request->types):'';
            $inspection->notes = $request->notes;
            $inspection->status = $request->status;
            $inspection->amount = $request->amount;
            $inspection->repair_status = $request->repair_status;
            if (!empty($request->receipt)) {
                $expenseFilenameWithExt = $request->file('receipt')->getClientOriginalName();
                $expenseFilename = pathinfo($expenseFilenameWithExt, PATHINFO_FILENAME);
                $expenseExtension = $request->file('receipt')->getClientOriginalExtension();
                $expenseFileName = $expenseFilename . '_' . time() . '.' . $expenseExtension;
                $dir = storage_path('upload/expense');
                $image_path = $dir . $expenseFilenameWithExt;
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('receipt')->storeAs('upload/expense/', $expenseFileName);
                $inspection->receipt = $expenseFileName;
            }

            $inspection->save();

            return redirect()->route('inspection.index')->with('success', __('Inspection successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(Inspection $inspection)
    {
        if (\Auth::user()->can('delete inspection')) {
            $inspection->delete();
            return redirect()->route('inspection.index')->with('success', __('Inspection successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
