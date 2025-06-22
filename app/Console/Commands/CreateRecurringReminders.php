<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ReminderController;


class CreateRecurringReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:create-recurring';
    protected $description = 'Create recurring reminders automatically';

    /**
     * The console command description.
     *
     * @var string
     */
    

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new ReminderController();
        $result = $controller->createRecurringReminders();
        
        if ($result->getData()->success) {
            $this->info('Recurring reminders created successfully. Created: ' . $result->getData()->created);
        } else {
            $this->error('Failed to create recurring reminders: ' . $result->getData()->error);
        }
    }
}
