<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BillingUsageService;

class SyncBillingUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:sync-usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate and sync tenant usage data to the billing provider (Stripe via Cashier).';

    /**
     * Execute the console command.
     */
    public function handle(BillingUsageService $billingUsageService): int
    {
        $this->info('Syncing tenant usage to billing provider...');
        $billingUsageService->syncUsageToStripe();
        $this->info('Usage sync completed.');

        return self::SUCCESS;
    }
}


