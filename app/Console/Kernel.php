<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string>
     */
    protected $commands = [
        \App\Console\Commands\AddUserEmailToClient::class,
        \App\Console\Commands\MigrateClientUserEmails::class,
        \App\Console\Commands\SeedTenantDefaults::class,
        \App\Console\Commands\TestTenantUserManagement::class,
        \App\Console\Commands\SyncBillingUsage::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Push usage aggregates to billing provider every hour
        $schedule->command('billing:sync-usage')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}


