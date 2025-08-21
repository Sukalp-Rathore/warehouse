<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the command
Artisan::command('schedule', function (Schedule $schedule) {
    $schedule->command('send:event-reminder-emails')->dailyAt('00:00');
});