<?php

namespace App\Services;

use App\Models\Master\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class TenantBillingService
{
    /**
     * Create or get a Stripe customer for the tenant.
     */
    public function ensureStripeCustomer(Client $tenant): Client
    {
        $tenant->createOrGetStripeCustomer();
        return $tenant;
    }

    /**
     * Start a new subscription for the tenant on the given internal plan key.
     *
     * @param  Client  $tenant
     * @param  string  $planKey   Internal plan key from config/plans.php
     * @param  string|null  $paymentMethodId
     */
    public function createSubscription(Client $tenant, string $planKey, ?string $paymentMethodId = null): Client
    {
        $plans = config('plans.plans', []);
        $plan = $plans[$planKey] ?? null;

        if (!$plan || empty($plan['stripe_price_id'])) {
            throw new \InvalidArgumentException("Invalid billing plan key: {$planKey}");
        }

        $this->ensureStripeCustomer($tenant);

        // Attach payment method if provided
        if ($paymentMethodId) {
            $tenant->updateDefaultPaymentMethod($paymentMethodId);
        }

        // Create subscription on Stripe via Cashier
        $subscription = $tenant->newSubscription('default', $plan['stripe_price_id'])->create($paymentMethodId, [
            'metadata' => [
                'tenant_client_id' => $tenant->client_id,
                'plan_key' => $planKey,
            ],
        ]);

        $tenant->billing_plan = $planKey;
        $tenant->billing_status = $subscription->stripe_status ?? 'active';
        $tenant->subscription_plan = $planKey;
        $tenant->save();

        return $tenant;
    }

    /**
     * Swap tenant to a different subscription plan.
     */
    public function swapPlan(Client $tenant, string $newPlanKey, bool $invoiceImmediately = true): Client
    {
        $plans = config('plans.plans', []);
        $plan = $plans[$newPlanKey] ?? null;

        if (!$plan || empty($plan['stripe_price_id'])) {
            throw new \InvalidArgumentException("Invalid billing plan key: {$newPlanKey}");
        }

        $subscription = $tenant->subscription('default');

        if (!$subscription) {
            throw new \RuntimeException('Tenant has no active subscription to swap.');
        }

        if ($invoiceImmediately) {
            $subscription->swapAndInvoice($plan['stripe_price_id']);
        } else {
            $subscription->swap($plan['stripe_price_id']);
        }

        $tenant->billing_plan = $newPlanKey;
        $tenant->billing_status = $subscription->stripe_status ?? $tenant->billing_status;
        $tenant->subscription_plan = $newPlanKey;
        $tenant->save();

        return $tenant;
    }

    /**
     * Cancel the tenant subscription.
     */
    public function cancelSubscription(Client $tenant, bool $immediately = false): Client
    {
        $subscription = $tenant->subscription('default');

        if (!$subscription) {
            return $tenant;
        }

        if ($immediately) {
            $subscription->cancelNow();
            $tenant->billing_status = 'canceled';
        } else {
            $subscription->cancel();
            $tenant->billing_status = 'canceled_at_period_end';
        }

        $tenant->save();

        return $tenant;
    }

    /**
     * Helper to read current plan definition for a tenant.
     */
    public function getPlanDefinition(?Client $tenant): ?array
    {
        if (!$tenant || !$tenant->billing_plan) {
            return null;
        }

        $plans = config('plans.plans', []);
        return $plans[$tenant->billing_plan] ?? null;
    }

    /**
     * Update billing status based on Stripe subscription/invoice events.
     */
    public function updateBillingStatus(Client $tenant, string $status): Client
    {
        $tenant->billing_status = $status;
        $tenant->save();

        return $tenant;
    }
}


