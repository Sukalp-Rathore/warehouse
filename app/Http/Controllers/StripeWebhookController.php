<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Illuminate\Http\Request;
use App\Models\Master\Client;
use App\Services\TenantBillingService;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends CashierWebhookController
{
    /**
     * Handle successful invoice payment.
     */
    public function handleInvoicePaymentSucceeded(array $payload)
    {
        $customerId = $payload['data']['object']['customer'] ?? null;

        if ($customerId) {
            $tenant = Client::where('stripe_id', $customerId)->first();
            if ($tenant) {
                app(TenantBillingService::class)->updateBillingStatus($tenant, 'active');
            }
        }

        return parent::handleInvoicePaymentSucceeded($payload);
    }

    /**
     * Handle failed invoice payment.
     */
    public function handleInvoicePaymentFailed(array $payload)
    {
        $customerId = $payload['data']['object']['customer'] ?? null;

        if ($customerId) {
            $tenant = Client::where('stripe_id', $customerId)->first();
            if ($tenant) {
                app(TenantBillingService::class)->updateBillingStatus($tenant, 'past_due');
            }
        }

        return parent::handleInvoicePaymentFailed($payload);
    }

    /**
     * Handle subscription updated event.
     */
    public function handleCustomerSubscriptionUpdated(array $payload)
    {
        $subscription = $payload['data']['object'] ?? null;
        if ($subscription) {
            $customerId = $subscription['customer'] ?? null;
            $status = $subscription['status'] ?? null;

            if ($customerId && $status) {
                $tenant = Client::where('stripe_id', $customerId)->first();
                if ($tenant) {
                    app(TenantBillingService::class)->updateBillingStatus($tenant, $status);
                }
            }
        }

        return parent::handleCustomerSubscriptionUpdated($payload);
    }
}


