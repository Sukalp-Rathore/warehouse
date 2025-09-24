<?php

/**
 * Appointment Reminders Cron Script
 * 
 * This script checks the visits table for appointments that need reminders
 * (2 hours before appointment time) and sends them.
 * 
 * To set up cron job, add this line to your crontab:
 * */5 * * * * /Applications/XAMPP/xamppfiles/bin/php /Applications/XAMPP/xamppfiles/htdocs/health/run_reminders.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\AppointmentController;

try {
    $controller = new AppointmentController();
    $result = $controller->sendReminders();
    
    echo "[" . date('Y-m-d H:i:s') . "] " . $result->getData()->message . " (Count: " . $result->getData()->count . ")\n";
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n";
}
