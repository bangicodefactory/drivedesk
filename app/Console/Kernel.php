<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // $schedule->command('reminders:update-status')->daily();

        // Update reminder statuses every hour
        $schedule->command('reminders:update-statuses')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();

        // Create recurring reminders daily at 6 AM
        $schedule->command('reminders:create-recurring')
            ->dailyAt('06:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Send daily reminder summary at 8 AM
        $schedule->call(function () {
            $controller = new \App\Http\Controllers\ReminderController();
            $controller->sendDailyReminderSummary();
        })->dailyAt('08:00');

        // F-19 (perf-audit): prune old activity-log rows nightly so
        // logged_histories stays bounded (retention via config/audit.php).
        $schedule->command('model:prune', ['--model' => [\App\Models\LoggedHistory::class]])
            ->dailyAt('02:30')
            ->withoutOverlapping()
            ->runInBackground();

        // Refresh the demo sandbox nightly so its time-relative data stays
        // anchored to "today" and each day starts pristine. --if-demo makes this
        // a silent no-op on real clients, so it's safe to schedule everywhere
        // (BAN-248). demo:seed is idempotent; it only acts when feature
        // 'demo_gateway' is on (today: drivedesk).
        $schedule->command('demo:seed --if-demo')
            ->dailyAt('03:30')
            ->withoutOverlapping()
            ->runInBackground();
    }
    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
