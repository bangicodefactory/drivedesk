<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReminderType;
use Inertia\Inertia;

class ReminderTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (\Auth::user()->can('manage reminder')) {
            $types = ReminderType::where('parent_id', parentId())->get();
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
        if (config('app.inertia_enabled')) {
            return Inertia::render('ReminderType/Index', compact('types'));
        }
        return view('reminder_type.index', compact('types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (config('app.inertia_enabled')) {
            return Inertia::render('ReminderType/Create');
        }
        return view('reminder_type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        if (\Auth::user()->can('create reminder')) {
            $validator = \Validator::make(
                $request->all(), [
                    'type' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $remindertype = new ReminderType();
            $remindertype->type = $request->type;
            $remindertype->parent_id = parentId();
            $remindertype->save();
            return redirect()->route('reminder-type.index')->with('success', __('Reminder type successfully created.'));
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
    public function edit(ReminderType $reminderType)
    {
        if (config('app.inertia_enabled')) {
            return Inertia::render('ReminderType/Edit', compact('reminderType'));
        }
        return view('reminder_type.edit', compact('reminderType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReminderType $reminderType)
    {
        if (\Auth::user()->can('edit reminder')) {
            $validator = \Validator::make(
                $request->all(), [
                    'type' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $reminderType->type = $request->type;
            $reminderType->save();
            return redirect()->route('reminder-type.index')->with('success', __('Reminder type successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReminderType $reminderType)
    {
        //
        if (\Auth::user()->can('delete reminder')) {
            $reminderType->delete();
            return redirect()->route('reminder-type.index')->with('success', __('Reminder type successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
