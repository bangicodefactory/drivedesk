<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\ReminderType;
use Illuminate\Http\Request;
use App\Models\Vehicle;

class ReminderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (\Auth::user()->can('manage reminder')) {
            $reminders = Reminder::where('parent_id', '=', parentId())->get();
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        return view('reminder.index', compact('reminders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vehicles = Vehicle::where('parent_id', parentId())->get()->pluck('name', 'id');
        $vehicles->prepend(__('Select Vehicle'),'');

        $types = ReminderType::where('parent_id', parentId())->get()->pluck('type', 'id');
        $types->prepend(__('Select Type'),'');
        return view('reminder.create', compact('vehicles','types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create reminder')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'type' => 'required',
                    'reminder_date' => 'required',
                    'vehicle' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                 return redirect()->back()->with('error', $messages->first());
            }

            $reminber = new Reminder();
            $reminber->name = $request->name;
            $reminber->reminder_type_id = $request->type;
            $reminber->id_vehicle = !empty($request->vehicle)?$request->vehicle:0;
            $reminber->reminder_date = $request->reminder_date;
            $reminber->note = $request->note;
            $reminber->status = 'pending';
            $reminber->parent_id = parentId();

            $reminber->save();

            return redirect()->route('reminder.index')->with('success', __('Reminber successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $vehicles = Vehicle::where('parent_id', parentId())->get()->pluck('name', 'id');
        $vehicles->prepend(__('Select Vehicle'),'');

        $types = ReminderType::where('parent_id', parentId())->get()->pluck('title', 'id');
        $types->prepend(__('Select Type'),'');
        return view('reminder.edit', compact('vehicles','reminder','types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reminder $reminber)
    {
        if (\Auth::user()->can('edit reminber')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'type' => 'required',
                    'reminder_date' => 'required',
                    'vehicle' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $reminber->name = $request->name;
            $reminber->reminder_type_id = $request->type;
            $reminber->id_vehicle = !empty($request->vehicle)?$request->vehicle:0;
            $reminber->reminder_date = $request->reminder_date;
            $reminber->status = $request->status;
            $reminber->note = $request->note;
            // $reminber->parent_id = parentId();
            $reminber->save();

            return redirect()->route('reminber.index')->with('success', __('Reminber successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
