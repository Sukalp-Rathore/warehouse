<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    */
    'currency' => 'inr',

    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | Keyed by internal plan code. Each plan maps to one or more Stripe price IDs.
    | Replace the placeholder price IDs with your real Stripe price IDs.
    |
    */
    'plans' => [
        'basic_monthly' => [
            'name' => 'Basic',
            'stripe_price_id' => env('STRIPE_PRICE_BASIC_MONTHLY', 'price_basic_monthly_placeholder'),
            'interval' => 'month',
            'features' => [
                'max_warehouses' => 1,
                'max_users' => 5,
                'grn_sap_integration' => false,
                'advanced_reporting' => false,
            ],
            'usage_limits' => [
                'storage_gb_month' => 50,
                'transactions_per_month' => 5000,
            ],
        ],

        'pro_monthly' => [
            'name' => 'Pro',
            'stripe_price_id' => env('STRIPE_PRICE_PRO_MONTHLY', 'price_pro_monthly_placeholder'),
            'interval' => 'month',
            'features' => [
                'max_warehouses' => 5,
                'max_users' => 25,
                'grn_sap_integration' => true,
                'advanced_reporting' => true,
            ],
            'usage_limits' => [
                'storage_gb_month' => 200,
                'transactions_per_month' => 50000,
            ],
        ],

        'enterprise_monthly' => [
            'name' => 'Enterprise',
            'stripe_price_id' => env('STRIPE_PRICE_ENTERPRISE_MONTHLY', 'price_enterprise_monthly_placeholder'),
            'interval' => 'month',
            'features' => [
                'max_warehouses' => null, // unlimited
                'max_users' => null,      // unlimited
                'grn_sap_integration' => true,
                'advanced_reporting' => true,
            ],
            'usage_limits' => [
                'storage_gb_month' => null,           // billed as overage
                'transactions_per_month' => null,     // billed as overage
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Usage Metrics (Metered Billing)
    |--------------------------------------------------------------------------
    |
    | Each metric is mapped to a Stripe metered price ID. These are used when
    | sending usage records to Stripe.
    |
    */
    'usage_metrics' => [
        'storage_gb_month' => [
            'stripe_price_id' => env('STRIPE_PRICE_STORAGE_GB_MONTH', 'price_storage_gb_month_placeholder'),
            'description' => 'Aggregated storage (GB) per month across all warehouses.',
        ],
        'transactions_per_month' => [
            'stripe_price_id' => env('STRIPE_PRICE_TRANSACTIONS_MONTH', 'price_transactions_month_placeholder'),
            'description' => 'Number of WMS transactions (putaway, transfer, pick, GRN).',
        ],
        'active_users' => [
            'stripe_price_id' => env('STRIPE_PRICE_ACTIVE_USERS', 'price_active_users_placeholder'),
            'description' => 'Number of active named users in the tenant.',
        ],
    ],
];


