<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Batch extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'batches';

    protected $fillable = [
        'batch_id',
        'item_id',
        'item_code',
        'manufacturing_date',
        'expiry_date',
        'production_lot_number',
        'vendor_batch_number',
        'quantity_received',
        'status', // active, expired, consumed
        'created_by'
    ];

    protected $casts = [
        'manufacturing_date' => 'datetime',
        'expiry_date' => 'datetime',
        'quantity_received' => 'decimal:2',
    ];

    protected $dates = [
        'manufacturing_date',
        'expiry_date',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the item
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Get all inventory records for this batch
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class, 'batch_id');
    }

    /**
     * Check if batch is expired
     */
    public function isExpired()
    {
        if (!$this->expiry_date) {
            return false;
        }
        return Carbon::now()->greaterThan($this->expiry_date);
    }

    /**
     * Get remaining quantity across all inventory locations
     */
    public function getRemainingQuantity()
    {
        return Inventory::where('batch_id', $this->_id)
                       ->where('status', '!=', 'consumed')
                       ->sum('available_qty');
    }

    /**
     * Get days until expiry (negative if expired)
     */
    public function getDaysUntilExpiry()
    {
        if (!$this->expiry_date) {
            return null;
        }
        return Carbon::now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Scope for expired batches
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now())
                    ->where('status', '!=', 'consumed');
    }

    /**
     * Scope for active batches
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', now());
        })->where('status', 'active');
    }

    /**
     * Scope by item
     */
    public function scopeByItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope expiring soon (within X days)
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        $threshold = now()->addDays($days);
        return $query->where('expiry_date', '>=', now())
                    ->where('expiry_date', '<=', $threshold)
                    ->where('status', 'active');
    }
}

