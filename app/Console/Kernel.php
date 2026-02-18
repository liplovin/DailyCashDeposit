<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Auto-process pending collections at 12:00 AM (midnight)
        $schedule->call(function () {
            $controller = new \App\Http\Controllers\OperatingAccountController();
            $controller->autoProcessPendingCollections();
        })->dailyAt('00:00');

        // Auto-process pending disbursements at 12:00 AM (midnight)
        $schedule->call(function () {
            $controller = new \App\Http\Controllers\DisbursementController();
            $controller->autoProcessPendingDisbursements();
        })->dailyAt('00:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
