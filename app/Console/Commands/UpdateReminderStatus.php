<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; 

class UpdateReminderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the status of reminders based on the current date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('Starting reminder status update process.');
    // Fetch all reminders that need to be updated
    $reminders = Reminder::where('status', '!=', 'completed')->get();
    Log::info('Found ' . $reminders->count() . ' reminders to update.');
    foreach ($reminders as $reminder) {
        // Parse the reminder_date
        $reminderDate = Carbon::parse($reminder->reminder_date);
        $today = Carbon::now();
        // Calculate the difference in days between today and the reminder_date
        $daysUntilDeadline = $today->diffInDays($reminderDate, false);

        // Determine the status based on the days remaining
        if ($daysUntilDeadline <= 0) {
            $status = 'overdue';
        } elseif ($daysUntilDeadline <= 3) {
            $status = 'urgent';
        } elseif ($daysUntilDeadline <= 7) {
            $status = 'upcoming';
        } else {
            $status = 'pending';
        }

        // Update the reminder status
        $reminder->status = $status;
        $reminder->save();
    }
    Log::info('Reminder status update process completed successfully.');
        $this->info('Reminder statuses updated successfully.');
    }
}
