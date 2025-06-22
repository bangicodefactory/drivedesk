<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\ReminderType;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
    // public function store(Request $request)
    // {
    //     if (\Auth::user()->can('create reminder')) {
    //         $validator = \Validator::make(
    //             $request->all(),
    //             [
    //                 'name' => 'required',
    //                 'type' => 'required',
    //                 'reminder_date' => 'required',
    //                 'vehicle' => 'required',
    //             ]
    //         );
    //         if ($validator->fails()) {
    //             $messages = $validator->getMessageBag();
    //             return redirect()->back()->with('error', $messages->first());
    //         }

    //         // Calculate the status based on reminder_date
    //         $reminderDate = Carbon::parse($request->reminder_date);
    //         $today = Carbon::now();
    //         $daysUntilDeadline = $today->diffInDays($reminderDate, false);

    //         // Determine status based on the days remaining
    //         if ($daysUntilDeadline <= 0) {
    //             $status = 'overdue';
    //         } elseif ($daysUntilDeadline <= 3) {
    //             $status = 'urgent';
    //         } elseif ($daysUntilDeadline <= 7) {
    //             $status = 'upcoming';
    //         } else {
    //             $status = 'pending';
    //         }

    //         $reminder = new Reminder();
    //         $reminder->name = $request->name;
    //         $reminder->reminder_type_id = $request->type;
    //         $reminder->id_vehicle = !empty($request->vehicle) ? $request->vehicle : 0;
    //         $reminder->reminder_date = $request->reminder_date;
    //         $reminder->note = $request->note;
    //         $reminder->status = $status;
    //         $reminder->parent_id = parentId();

    //         $reminder->save();

    //         //send email notification
    //         // if($reminder->save()){
    //         //     // $user=User::find($request->driver);
    //         //     $userath = \Auth::user();
    //         //     $module = 'booking_status';
    //         //     $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
    //         //     $setting = settings();
    //         //     $bokid = 20;
    //         //     $errorMessage = '';
    //         //     if (!empty($notification) && $notification->enabled_email == 1) {
    //         //         $notification_responce = MessageReplace($notification, $bokid);
    //         //         $data['subject'] = $notification_responce['subject'];
    //         //         $data['message'] = $notification_responce['message'];
    //         //         $data['module'] = $module;
    //         //         $data['logo'] = $setting['company_logo'];
    //         //         $to = $userath->email;

    //         //         $response = commonEmailSend($to, $data);
    //         //         if ($response['status'] == 'error') {
    //         //             $errorMessage=$response['message'];
    //         //         }
    //         //     }
    //         // }    


    //         return redirect()->route('reminder.index')->with('success', __('Reminber successfully created.'));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission Denied.'));
    //     }
    // }
    public function store(Request $request)
    {
        if (\Auth::user()->can('create reminder')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'type' => 'required',
                    'reminder_date' => 'required|date|after:today',
                    'vehicle' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $reminder = new Reminder();
            $reminder->name = $request->name;
            $reminder->reminder_type_id = $request->type;
            $reminder->id_vehicle = !empty($request->vehicle) ? $request->vehicle : 0;
            $reminder->reminder_date = $request->reminder_date;
            $reminder->note = $request->note;
            $reminder->status = $this->calculateReminderStatus($request->reminder_date);
            $reminder->parent_id = parentId();
            $reminder->save();

            return redirect()->route('reminder.index')->with('success', __('Rappel créé avec succès.'));
        } else {
            return redirect()->back()->with('error', __('Permission refusée.'));
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
    // public function update(Request $request, Reminder $reminder)
    // {
    //     if (\Auth::user()->can('edit reminder')) {
    //         $validator = \Validator::make(
    //             $request->all(),
    //             [
    //                 'name' => 'required',
    //                 'type' => 'required',
    //                 'reminder_date' => 'required',
    //                 // 'vehicle' => 'required',
    //             ]
    //         );
    //         if ($validator->fails()) {
    //             $messages = $validator->getMessageBag();
    //             return redirect()->back()->with('error', $messages->first());
    //         }
    //         // Calculate the status based on reminder_date
    //         $reminderDate = Carbon::parse($request->reminder_date);
    //         $today = Carbon::now();
    //         $daysUntilDeadline = $today->diffInDays($reminderDate, false);

    //         // Determine status based on the days remaining
    //         if ($daysUntilDeadline <= 0) {
    //             $status = 'overdue';
    //         } elseif ($daysUntilDeadline <= 3) {
    //             $status = 'urgent';
    //         } elseif ($daysUntilDeadline <= 7) {
    //             $status = 'upcoming';
    //         } else {
    //             $status = 'pending';
    //         }

    //         $reminder->name = $request->name;
    //         $reminder->reminder_type_id = $request->type;
    //         // $reminder->id_vehicle = $request->vehicle;
    //         $reminder->reminder_date = $request->reminder_date;
    //         $reminder->status = $status;
    //         $reminder->note = $request->note;
    //         // $reminber->parent_id = parentId();
    //         $reminder->save();

    //         return redirect()->route('reminder.index')->with('success', __('Reminber successfully updated.'));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission Denied.'));
    //     }
    // }
    public function update(Request $request, Reminder $reminder)
    {
        if (\Auth::user()->can('edit reminder')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'type' => 'required',
                    'reminder_date' => 'required|date',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $reminder->name = $request->name;
            $reminder->reminder_type_id = $request->type;
            $reminder->reminder_date = $request->reminder_date;
            $reminder->status = $this->calculateReminderStatus($request->reminder_date);
            $reminder->note = $request->note;
            $reminder->save();

            return redirect()->route('reminder.index')->with('success', __('Rappel mis à jour avec succès.'));
        } else {
            return redirect()->back()->with('error', __('Permission refusée.'));
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

    public function updateReminderStatuses()
    {
        try {
            $reminders = Reminder::where('status', '!=', 'completed')->get();
            $updatedCount = 0;

            foreach ($reminders as $reminder) {
                $oldStatus = $reminder->status;
                $newStatus = $this->calculateReminderStatus($reminder->reminder_date);

                if ($oldStatus !== $newStatus) {
                    $reminder->status = $newStatus;
                    $reminder->save();
                    $updatedCount++;

                    // Send notification if status changed to urgent or overdue
                    if (in_array($newStatus, ['urgent', 'overdue'])) {
                        $this->sendReminderNotification($reminder);
                    }
                }
            }

            Log::info("Updated {$updatedCount} reminder statuses");
            return response()->json(['success' => true, 'updated' => $updatedCount]);
        } catch (\Exception $e) {
            Log::error('Error updating reminder statuses: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Calculate reminder status based on date
     */
    private function calculateReminderStatus($reminderDate)
    {
        $reminderDate = Carbon::parse($reminderDate);
        $today = Carbon::now();
        $daysUntilDeadline = $today->diffInDays($reminderDate, false);

        if ($daysUntilDeadline <= 0) {
            return 'overdue';
        } elseif ($daysUntilDeadline <= 3) {
            return 'urgent';
        } elseif ($daysUntilDeadline <= 7) {
            return 'upcoming';
        } else {
            return 'pending';
        }
    }

    /**
     * Get all urgent and overdue reminders
     */
    public function getUrgentReminders()
    {
        $urgentReminders = Reminder::with(['vehicle', 'reminderType'])
            ->where('parent_id', parentId())
            ->whereIn('status', ['urgent', 'overdue'])
            ->orderBy('reminder_date', 'asc')
            ->get();

        return response()->json($urgentReminders);
    }

    /**
     * Get dashboard data for reminders
     */
    public function getDashboardData()
    {
        $parentId = parentId();
        $today = Carbon::now();

        $stats = [
            'overdue' => Reminder::where('parent_id', $parentId)->where('status', 'overdue')->count(),
            'urgent' => Reminder::where('parent_id', $parentId)->where('status', 'urgent')->count(),
            'upcoming' => Reminder::where('parent_id', $parentId)->where('status', 'upcoming')->count(),
            'pending' => Reminder::where('parent_id', $parentId)->where('status', 'pending')->count(),
        ];

        $upcomingReminders = Reminder::with(['vehicle', 'reminderType'])
            ->where('parent_id', $parentId)
            ->whereIn('status', ['overdue', 'urgent', 'upcoming'])
            ->orderBy('reminder_date', 'asc')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => $stats,
            'upcoming' => $upcomingReminders
        ]);
    }

    /**
     * Send notification for urgent/overdue reminders
     */
    private function sendReminderNotification(Reminder $reminder)
    {
        try {
            $users = User::where('parent_id', $reminder->parent_id)
                ->where('type', 'company') // Adjust based on your user types
                ->get();

            $vehicle = Vehicle::find($reminder->id_vehicle);
            $reminderType = ReminderType::find($reminder->reminder_type_id);

            $message = $this->buildNotificationMessage($reminder, $vehicle, $reminderType);

            foreach ($users as $user) {
                // Send email notification
                $this->sendEmailNotification($user, $reminder, $message);

                // You can also add SMS or push notifications here
                // $this->sendSMSNotification($user, $message);
            }
        } catch (\Exception $e) {
            Log::error('Error sending reminder notification: ' . $e->getMessage());
        }
    }
    /**
     * Build notification message
     */
    private function buildNotificationMessage(Reminder $reminder, $vehicle, $reminderType)
    {
        $vehicleName = $vehicle ? $vehicle->name : 'N/A';
        $typeName = $reminderType ? $reminderType->type : 'Maintenance';
        $daysOverdue = Carbon::now()->diffInDays(Carbon::parse($reminder->reminder_date), false);

        if ($reminder->status === 'overdue') {
            return "URGENT: Le rappel '{$reminder->name}' pour le véhicule '{$vehicleName}' ({$typeName}) est en retard de " . abs($daysOverdue) . " jour(s).";
        } else {
            return "ATTENTION: Le rappel '{$reminder->name}' pour le véhicule '{$vehicleName}' ({$typeName}) arrive à échéance dans {$daysOverdue} jour(s).";
        }
    }

    /**
     * Send email notification
     */
    private function sendEmailNotification($user, $reminder, $message)
    {
        try {
            $data = [
                'user' => $user,
                'reminder' => $reminder,
                'message' => $message,
                'subject' => 'Rappel de Maintenance Véhicule - ' . $reminder->name
            ];

            Mail::send('emails.reminder_notification', $data, function ($mail) use ($user, $data) {
                $mail->to($user->email, $user->name)
                    ->subject($data['subject']);
            });
        } catch (\Exception $e) {
            Log::error('Error sending email notification: ' . $e->getMessage());
        }
    }

    /**
     * Get reminders for a specific vehicle
     */
    public function getVehicleReminders($vehicleId)
    {
        $reminders = Reminder::with('reminderType')
            ->where('parent_id', parentId())
            ->where('id_vehicle', $vehicleId)
            ->orderBy('reminder_date', 'asc')
            ->get();

        return response()->json($reminders);
    }

    /**
     * Mark reminder as completed
     */
    public function markAsCompleted(Reminder $reminder)
    {
        if (\Auth::user()->can('edit reminder')) {
            $reminder->status = 'completed';
            $reminder->save();

            return redirect()->back()->with('success', __('Rappel marqué comme terminé.'));
        } else {
            return redirect()->back()->with('error', __('Permission refusée.'));
        }
    }


    /**
     * Snooze reminder (extend date by specified days)
     */
    public function snoozeReminder(Request $request, Reminder $reminder)
    {
        if (\Auth::user()->can('edit reminder')) {
            $validator = \Validator::make($request->all(), [
                'days' => 'required|integer|min:1|max:365'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->with('error', $validator->errors()->first());
            }

            $newDate = Carbon::parse($reminder->reminder_date)->addDays($request->days);
            $reminder->reminder_date = $newDate;
            $reminder->status = $this->calculateReminderStatus($newDate);
            $reminder->save();

            return redirect()->back()->with('success', __('Rappel reporté de ' . $request->days . ' jour(s).'));
        } else {
            return redirect()->back()->with('error', __('Permission refusée.'));
        }
    }


    /**
     * Get reminder statistics for charts/reports
     */
    public function getReminderStatistics()
    {
        $parentId = parentId();
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        $stats = [
            'current_month' => [
                'total' => Reminder::where('parent_id', $parentId)
                    ->whereMonth('reminder_date', $currentMonth->month)
                    ->whereYear('reminder_date', $currentMonth->year)
                    ->count(),
                'completed' => Reminder::where('parent_id', $parentId)
                    ->whereMonth('reminder_date', $currentMonth->month)
                    ->whereYear('reminder_date', $currentMonth->year)
                    ->where('status', 'completed')
                    ->count(),
                'overdue' => Reminder::where('parent_id', $parentId)
                    ->whereMonth('reminder_date', $currentMonth->month)
                    ->whereYear('reminder_date', $currentMonth->year)
                    ->where('status', 'overdue')
                    ->count(),
            ],
            'by_vehicle' => Reminder::selectRaw('id_vehicle, vehicles.name as vehicle_name, COUNT(*) as total')
                ->join('vehicles', 'reminders.id_vehicle', '=', 'vehicles.id')
                ->where('reminders.parent_id', $parentId)
                ->groupBy('id_vehicle', 'vehicles.name')
                ->get(),
            'by_type' => Reminder::selectRaw('reminder_type_id, reminder_types.type as type_name, COUNT(*) as total')
                ->join('reminder_types', 'reminders.reminder_type_id', '=', 'reminder_types.id')
                ->where('reminders.parent_id', $parentId)
                ->groupBy('reminder_type_id', 'reminder_types.type')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Auto-create recurring reminders (for regular maintenance)
     */
    public function createRecurringReminders()
    {
        try {
            $completedReminders = Reminder::where('status', 'completed')
                ->where('parent_id', parentId())
                ->get();

            $createdCount = 0;

            foreach ($completedReminders as $reminder) {
                // Check if this reminder should recur (you can add a recurring field to your table)
                Log::info("Processing Reminder ID: {$reminder->id}, type_id: {$reminder->reminder_type_id}");

                $reminderType = ReminderType::find($reminder->reminder_type_id);

                if (!$reminderType) {
                    Log::warning("Reminder type not found for reminder ID {$reminder->id}");
                    continue; // Skip this reminder
                } else{
                    // Example: Create new reminder 6 months after completion for certain types
                    if ($this->shouldCreateRecurringReminder($reminderType)) {
                        $newReminder = new Reminder();
                        $newReminder->name = $reminder->name;
                        $newReminder->reminder_type_id = $reminder->reminder_type_id;
                        $newReminder->id_vehicle = $reminder->id_vehicle;
                        $newReminder->reminder_date = Carbon::parse($reminder->reminder_date)->addMonths(6);
                        $newReminder->note = $reminder->note . ' (Rappel automatique)';
                        $newReminder->status = $this->calculateReminderStatus($newReminder->reminder_date);
                        $newReminder->parent_id = $reminder->parent_id;
                        $newReminder->save();

                        $createdCount++;
                    }
            }
        }

            Log::info("Created {$createdCount} recurring reminders");
            return response()->json(['success' => true, 'created' => $createdCount]);
        } catch (\Exception $e) {
            Log::error('Error creating recurring reminders: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Check if reminder type should recur
     */
    private function shouldCreateRecurringReminder($reminderType)
    {
        if (!$reminderType) return false;

        // Define which types should recur automatically
        $recurringTypes = ['la visite', 'Normal'];

        return in_array(strtolower($reminderType->type), $recurringTypes);
    }

    public function sendDailyReminderSummary()
    {
        try {
            $companies = User::where('type', 'company')->get();

            foreach ($companies as $company) {
                $urgentReminders = Reminder::with(['vehicle', 'reminderType'])
                    ->where('parent_id', $company->id)
                    ->whereIn('status', ['overdue', 'urgent'])
                    ->get();

                if ($urgentReminders->count() > 0) {
                    $data = [
                        'user' => $company,
                        'reminders' => $urgentReminders,
                        'subject' => 'Résumé quotidien des rappels de maintenance - ' . $urgentReminders->count() . ' rappel(s) urgent(s)'
                    ];

                    Mail::send('emails.daily_reminder_summary', $data, function ($mail) use ($company, $data) {
                        $mail->to($company->email, $company->name)
                            ->subject($data['subject']);
                    });
                }
            }

            Log::info('Daily reminder summaries sent successfully');
        } catch (\Exception $e) {
            Log::error('Error sending daily reminder summary: ' . $e->getMessage());
        }
    }
}
