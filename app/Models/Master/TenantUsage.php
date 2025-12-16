<?php

namespace App\Models\Master;

use MongoDB\Laravel\Eloquent\Model;

class TenantUsage extends Model
{
    protected $connection = 'master';
    protected $collection = 'tenant_usage';

    protected $fillable = [
        'client_id',
        'metric',
        'quantity',
        'occurred_at',
        'synced_to_stripe_at',
        'stripe_status',
        'stripe_error',
    ];

    protected $casts = [
        'quantity' => 'float',
        'occurred_at' => 'datetime',
        'synced_to_stripe_at' => 'datetime',
    ];
}


