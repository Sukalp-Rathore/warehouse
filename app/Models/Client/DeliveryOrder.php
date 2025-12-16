<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'delivery_orders';

    protected $fillable = [
        'do_number',
        'customer_name',
        'customer_code',
        'customer_address',
        'warehouse_id',
        'status',
        'priority',
        'delivery_date',
        'items',
        'picking_strategy',
        'sap_reference',
        'vehicle_number',
        'driver_name',
        'dispatch_date',
        'created_by'
    ];

    protected $casts = [
        'customer_address' => 'array',
        'items' => 'array',
        'delivery_date' => 'datetime',
        'dispatch_date' => 'datetime',
    ];

    protected $dates = [
        'delivery_date',
        'dispatch_date',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the creator
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get picking tasks for this DO
     */
    public function pickingTasks()
    {
        return $this->hasMany(PickingTask::class, 'do_id');
    }

    /**
     * Get shipment for this DO
     */
    public function shipment()
    {
        return $this->hasOne(Shipment::class, 'do_id');
    }

    /**
     * Update status
     */
    public function updateStatus($status)
    {
        $this->update(['status' => $status]);
    }

    /**
     * Check if all items are picked
     */
    public function isFullyPicked()
    {
        foreach ($this->items as $item) {
            if (($item['picked_qty'] ?? 0) < ($item['quantity'] ?? 0)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if all items are packed
     */
    public function isFullyPacked()
    {
        foreach ($this->items as $item) {
            if (($item['packed_qty'] ?? 0) < ($item['quantity'] ?? 0)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
}

