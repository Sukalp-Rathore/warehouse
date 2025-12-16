<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipment extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'shipments';

    protected $fillable = [
        'shipment_number',
        'do_id',
        'vehicle_number',
        'driver_name',
        'driver_phone',
        'dispatch_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'manifest'
    ];

    protected $casts = [
        'manifest' => 'array',
        'dispatch_date' => 'datetime',
        'expected_delivery_date' => 'datetime',
        'actual_delivery_date' => 'datetime',
    ];

    protected $dates = [
        'dispatch_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the delivery order
     */
    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class, 'do_id');
    }

    /**
     * Update status
     */
    public function updateStatus($status)
    {
        $this->update(['status' => $status]);
    }

    /**
     * Mark as delivered
     */
    public function markDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'actual_delivery_date' => now(),
        ]);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}

