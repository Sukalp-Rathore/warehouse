<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PickingTask extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'picking_tasks';

    protected $fillable = [
        'picking_task_number',
        'do_id',
        'do_number',
        'item_id',
        'item_code',
        'batch_id',
        'from_bin_id',
        'bin_code',
        'quantity',
        'picked_qty',
        'status',
        'assigned_to',
        'picked_by',
        'picked_at',
        'wave_id',
        'zone_code',
        'sequence'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'picked_qty' => 'decimal:2',
        'picked_at' => 'datetime',
    ];

    protected $dates = [
        'picked_at',
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
     * Get the item
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Get the batch
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    /**
     * Get the bin
     */
    public function bin()
    {
        return $this->belongsTo(Bin::class, 'from_bin_id');
    }

    /**
     * Get the assigned operator
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the picker
     */
    public function pickedBy()
    {
        return $this->belongsTo(User::class, 'picked_by');
    }

    /**
     * Get the picking wave
     */
    public function wave()
    {
        return $this->belongsTo(PickingWave::class, 'wave_id');
    }

    /**
     * Mark as completed
     */
    public function complete($userId, $pickedQty)
    {
        $this->update([
            'status' => 'completed',
            'picked_qty' => $pickedQty,
            'picked_by' => $userId,
            'picked_at' => now(),
        ]);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by wave
     */
    public function scopeByWave($query, $waveId)
    {
        return $query->where('wave_id', $waveId);
    }

    /**
     * Scope by zone
     */
    public function scopeByZone($query, $zoneCode)
    {
        return $query->where('zone_code', $zoneCode);
    }
}

