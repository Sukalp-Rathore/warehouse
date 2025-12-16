<?php

namespace App\Services;

use App\Models\Master\Client;
use App\Models\Master\TenantUsage;
use Illuminate\Support\Facades\Log;

class BillingUsageService
{
    /**
     * Record a generic usage event for the current tenant.
     *
     * @param  string  $metric  Metric key from config/plans.php (usage_metrics section)
     * @param  float   $amount  Quantity to record
     */
    public function recordUsage(string $metric, float $amount = 1.0): void
    {
        $clientId = session('client_id');

        if (!$clientId) {
            return;
        }

        $tenant = Client::where('client_id', $clientId)
            ->where(function ($q) {
                $q->whereNull('record_type')->orWhere('record_type', 'tenant');
            })
            ->first();

        if (!$tenant) {
            Log::warning('BillingUsageService: tenant not found for usage recording', [
                'client_id' => $clientId,
                'metric' => $metric,
                'amount' => $amount,
            ]);
            return;
        }

        TenantUsage::create([
            'client_id' => $tenant->client_id,
            'metric' => $metric,
            'quantity' => $amount,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Convenience helpers for common metrics.
     */
    public function recordTransactionEvent(float $count = 1.0): void
    {
        $this->recordUsage('transactions_per_month', $count);
    }

    public function recordStorageChange(float $deltaGb): void
    {
        // Positive for increase, negative for decrease in storage
        if ($deltaGb == 0.0) {
            return;
        }

        $this->recordUsage('storage_gb_month', $deltaGb);
    }

    /**
     * Aggregate unsynced usage records and (optionally) sync to Stripe.
     *
     * NOTE: This implementation currently aggregates and marks records as synced.
     * Hook into Stripe metered billing here when subscription items are defined.
     */
    public function syncUsageToStripe(): void
    {
        $unsynced = TenantUsage::whereNull('synced_to_stripe_at')->get();

        if ($unsynced->isEmpty()) {
            return;
        }

        $grouped = $unsynced->groupBy(function ($record) {
            return $record->client_id . '|' . $record->metric;
        });

        foreach ($grouped as $key => $records) {
            [$clientId, $metric] = explode('|', $key, 2);
            $totalQuantity = (float) $records->sum('quantity');

            try {
                // Placeholder: integrate with Stripe Usage Records API here.
                Log::info('BillingUsageService syncing usage (placeholder)', [
                    'client_id' => $clientId,
                    'metric' => $metric,
                    'quantity' => $totalQuantity,
                ]);

                TenantUsage::whereIn('_id', $records->pluck('_id'))
                    ->update([
                        'synced_to_stripe_at' => now(),
                        'stripe_status' => 'synced',
                        'stripe_error' => null,
                    ]);
            } catch (\Throwable $e) {
                Log::error('BillingUsageService failed to sync usage', [
                    'client_id' => $clientId,
                    'metric' => $metric,
                    'quantity' => $totalQuantity,
                    'error' => $e->getMessage(),
                ]);

                TenantUsage::whereIn('_id', $records->pluck('_id'))
                    ->update([
                        'synced_to_stripe_at' => now(),
                        'stripe_status' => 'error',
                        'stripe_error' => $e->getMessage(),
                    ]);
            }
        }
    }
}


