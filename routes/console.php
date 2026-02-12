<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\OperatingAccountController;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Auto-process pending collections at 12:00 AM (midnight) daily
 */
Schedule::call(function () {
    $controller = new OperatingAccountController();
    $controller->autoProcessPendingCollections();
})->dailyAt('00:00');
