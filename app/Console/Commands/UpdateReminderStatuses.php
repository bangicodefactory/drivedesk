<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ReminderController;

class UpdateReminderStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'app:update-reminder-statuses';
    protected $signature = 'reminders:update-statuses';
    protected $description = 'Update reminder statuses automatically';
    /**
     * The console command description.
     *
     * @var string
     */
    // protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new ReminderController();
        $result = $controller->updateReminderStatuses();
        
        if ($result->getData()->success) {
            $this->info('Reminder statuses updated successfully. Updated: ' . $result->getData()->updated);
        } else {
            $this->error('Failed to update reminder statuses: ' . $result->getData()->error);
        }
    }
}
