<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\ReminderType;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class ReminderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (\Auth::user()->can('manage reminder')) {
            $reminders = Reminder::where('parent_id', '=', parentId())->orderBy('reminder_date', 'desc')->get();
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
        $vehicles = Vehicle::where('parent_id', parentId())->orderBy('created_at', 'desc')->get();

        // $vehicles = Vehicle::where('parent_id', parentId())->orderBy('created_at', 'desc')->get()->pluck('name', 'id');
        // $vehicles->prepend(__('Select Vehicle'), '');

        $types = ReminderType::where('parent_id', parentId())->get()->pluck('type', 'id');
        $types->prepend(__('Select Type'), '');
        return view('reminder.create', compact('vehicles', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create reminder')) {
            $validator = \Validator::make(
                $request->all(),
                [
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

            // Calculate the status based on reminder_date
            $reminderDate = Carbon::parse($request->reminder_date);
            $today = Carbon::now();
            $daysUntilDeadline = $today->diffInDays($reminderDate, false);

            // Determine status based on the days remaining
            if ($daysUntilDeadline <= 0) {
                $status = 'overdue';
            } elseif ($daysUntilDeadline <= 3) {
                $status = 'urgent';
            } elseif ($daysUntilDeadline <= 7) {
                $status = 'upcoming';
            } else {
                $status = 'pending';
            }

            $reminder = new Reminder();
            $reminder->name = $request->name;
            $reminder->reminder_type_id = $request->type;
            $reminder->id_vehicle = !empty($request->vehicle) ? $request->vehicle : 0;
            $reminder->reminder_date = $request->reminder_date;
            $reminder->note = $request->note;
            $reminder->status = $status;
            $reminder->parent_id = parentId();

            $reminder->save();

            //send email notification
            // if($reminder->save()){
            //     // $user=User::find($request->driver);
            //     $userath = \Auth::user();
            //     $module = 'booking_status';
            //     $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            //     $setting = settings();
            //     $bokid = 20;
            //     $errorMessage = '';
            //     if (!empty($notification) && $notification->enabled_email == 1) {
            //         $notification_responce = MessageReplace($notification, $bokid);
            //         $data['subject'] = $notification_responce['subject'];
            //         $data['message'] = $notification_responce['message'];
            //         $data['module'] = $module;
            //         $data['logo'] = $setting['company_logo'];
            //         $to = $userath->email;

            //         $response = commonEmailSend($to, $data);
            //         if ($response['status'] == 'error') {
            //             $errorMessage=$response['message'];
            //         }
            //     }
            // }    


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
    public function edit(Reminder $reminder)
    {
        $vehicles = Vehicle::where('parent_id', parentId())->get()->pluck('name', 'id');
        $vehicleName = $reminder->id_vehicle ? Vehicle::find($reminder->id_vehicle)->name : '';


        $type = ReminderType::where('parent_id', parentId())->get()->pluck('type', 'id');
        // $type->prepend(__('Select Type'),'');
        return view('reminder.edit', compact('vehicles', 'reminder', 'type', 'vehicleName'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reminder $reminder)
    {
        if (\Auth::user()->can('edit reminder')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'type' => 'required',
                    'reminder_date' => 'required',
                    // 'vehicle' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            // Calculate the status based on reminder_date
            $reminderDate = Carbon::parse($request->reminder_date);
            $today = Carbon::now();
            $daysUntilDeadline = $today->diffInDays($reminderDate, false);

            // Determine status based on the days remaining
            if ($daysUntilDeadline <= 0) {
                $status = 'overdue';
            } elseif ($daysUntilDeadline <= 3) {
                $status = 'urgent';
            } elseif ($daysUntilDeadline <= 7) {
                $status = 'upcoming';
            } else {
                $status = 'pending';
            }

            $reminder->name = $request->name;
            $reminder->reminder_type_id = $request->type;
            // $reminder->id_vehicle = $request->vehicle;
            $reminder->reminder_date = $request->reminder_date;
            $reminder->status = $status;
            $reminder->note = $request->note;
            // $reminber->parent_id = parentId();
            $reminder->save();

            return redirect()->route('reminder.index')->with('success', __('Reminber successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reminder $reminder)
    {
        //
        if (\Auth::user()->can('delete reminder')) {
            $reminder->delete();
            return redirect()->route('reminder.index')->with('success', __('Reminder successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function getDaysRemaining(Reminder $reminder)
    {
        $today = now();
        $reminderDate = Carbon::parse($reminder->reminder_date);
        $diffDays = $today->diffInDays($reminderDate, false);

        $message = '';
        if ($diffDays > 0) {
            $message = __('Il reste ') . ' ' . $diffDays . ' ' . __('jours avant ce rappel..');
        } else if ($diffDays < 0) {
            $message = __('Ce rappel est en retard de') . ' ' . abs($diffDays) . ' ' . __('jours.');
        } else {
            $message = __('Ce rappel est dû aujourd hui!');
        }

        return view('reminder.days_remaining', compact('message'));
    }
}
