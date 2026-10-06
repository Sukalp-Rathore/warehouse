# Stripe Billing Setup - Laravel Cashier

## Issue Resolved
✅ **Fixed:** "Trait Laravel\Cashier\Billable not found" error when creating tenants

## What Was Done

### 1. Installed Laravel Cashier
```bash
composer require laravel/cashier
```

### 2. Published Cashier Configuration
```bash
php artisan vendor:publish --tag=cashier-config
```

### 3. Added Stripe Environment Variables
Added to `.env` file:
```env
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
CASHIER_CURRENCY=usd
CASHIER_CURRENCY_LOCALE=en
CASHIER_LOGGER=
```

### 4. Cleared Cache
```bash
php artisan config:clear
php artisan cache:clear
```

---

## Next Steps for Billing Integration

### Option 1: Use Without Stripe (Development/Testing)
If you don't need billing functionality right now, the system will work without Stripe credentials. The `Billable` trait is now available, so tenant creation will work.

### Option 2: Set Up Stripe (Production)

#### Step 1: Create Stripe Account
1. Go to https://stripe.com
2. Sign up for an account
3. Get your API keys from Dashboard > Developers > API keys

#### Step 2: Configure Environment Variables
Update `.env` with your Stripe keys:
```env
STRIPE_KEY=pk_test_your_publishable_key_here
STRIPE_SECRET=sk_test_your_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
```

**Note:** Use test keys for development (pk_test_* and sk_test_*)

#### Step 3: Set Up Webhook (Optional)
If you want to handle subscription webhooks:

1. In Stripe Dashboard, go to Developers > Webhooks
2. Add endpoint: `https://yourdomain.com/stripe/webhook`
3. Select events to listen for:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`
4. Copy the webhook signing secret to `STRIPE_WEBHOOK_SECRET`

---

## How Billing Works in Your System

### Client Model (`@/Applications/XAMPP/xamppfiles/htdocs/wms/app/Models/Master/Client.php`)
The `Client` model uses the `Billable` trait from Laravel Cashier, which provides:

**Billing Fields:**
- `stripe_id` - Stripe customer ID
- `pm_type` - Payment method type
- `pm_last_four` - Last 4 digits of payment method
- `trial_ends_at` - Trial period end date
- `billing_status` - Current billing status
- `billing_plan` - Internal plan identifier
- `billing_metadata` - Additional billing data

**Subscription Plans:**
- Defined in your application configuration
- Can include limits like `max_warehouses`, `max_users`

---

## Testing Tenant Creation

You can now create tenants through the master admin interface:

1. Login as master admin: `admin@wms.com` / `password123`
2. Navigate to tenant onboarding: `/tenant/onboard`
3. Create a new tenant

The system will work without Stripe credentials, but billing features will be limited.

---

## Cashier Features Available

With Laravel Cashier installed, you can:

### Subscription Management
```php
// Create subscription
$client->newSubscription('default', 'price_xxxxx')->create($paymentMethod);

// Check subscription status
$client->subscribed('default');

// Cancel subscription
$client->subscription('default')->cancel();

// Resume subscription
$client->subscription('default')->resume();
```

### Payment Methods
```php
// Add payment method
$client->updateDefaultPaymentMethod($paymentMethod);

// Get payment methods
$client->paymentMethods();
```

### Invoices
```php
// Get invoices
$client->invoices();

// Download invoice
$client->downloadInvoice($invoiceId);
```

---

## Configuration File

Cashier configuration is located at: `config/cashier.php`

Key settings:
- `currency` - Default currency (USD)
- `currency_locale` - Locale for currency formatting
- `logger` - Log channel for Cashier events

---

## Webhook Route

The webhook route is already configured in `routes/web.php`:
```php
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);
```

---

## Important Notes

⚠️ **For Development:**
- You can leave Stripe credentials empty
- System will function without billing features
- Use test mode keys when testing billing

⚠️ **For Production:**
- Always use live mode keys (pk_live_* and sk_live_*)
- Set up proper webhook handling
- Implement proper error handling for failed payments
- Test subscription flows thoroughly

---

## Troubleshooting

### Issue: Stripe API errors
**Solution:** Verify your API keys are correct and have proper permissions

### Issue: Webhook not working
**Solution:** 
1. Check webhook URL is accessible
2. Verify webhook secret matches Stripe dashboard
3. Check logs for webhook errors

### Issue: Currency mismatch
**Solution:** Ensure `CASHIER_CURRENCY` matches your Stripe account currency

---

## Resources

- Laravel Cashier Documentation: https://laravel.com/docs/billing
- Stripe API Documentation: https://stripe.com/docs/api
- Stripe Testing: https://stripe.com/docs/testing

---

## Summary

✅ Laravel Cashier is now installed and configured  
✅ Tenant creation will work without errors  
✅ Billing features are available when Stripe is configured  
✅ System can operate without Stripe credentials for development  

You can now create tenants through the master admin interface!
