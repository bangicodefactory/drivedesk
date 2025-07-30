<?php

namespace App\Http\Controllers;

use App\Models\InspectionType;
use Illuminate\Http\Request;

class InspectionTypeController extends Controller
{

    public function index()
    {
        if (\Auth::user()->can('manage inspection type')) {
            $types = InspectionType::where('parent_id', '=', parentId())->get();
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        return view('inspection_type.index', compact('types'));
    }


    public function create()
    {
        return view('inspection_type.create');
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create inspection type')) {
            $validator = \Validator::make(
                $request->all(), [
                    'type' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $inspectionType = new InspectionType();
            $inspectionType->type = $request->type;
            $inspectionType->parent_id = parentId();
            $inspectionType->save();
            return redirect()->route('inspection-type.index')->with('success', __('Inspection type successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function show(InspectionType $inspectionType)
    {
        //
    }


    public function edit(InspectionType $inspectionType)
    {
        return view('inspection_type.edit', compact('inspectionType'));
    }


    public function update(Request $request, InspectionType $inspectionType)
    {
        if (\Auth::user()->can('edit inspection type')) {
            $validator = \Validator::make(
                $request->all(), [
                    'type' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $inspectionType->type = $request->type;
            $inspectionType->save();
            return redirect()->route('inspection-type.index')->with('success', __('Inspection type successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function destroy(InspectionType $inspectionType)
    {
        if (\Auth::user()->can('delete inspection type')) {
            $inspectionType->delete();
            return redirect()->route('inspection-type.index')->with('success', __('Inspection type successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
