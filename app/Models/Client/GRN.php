<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GRN extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'grns';

    protected $fillable = [
        'grn_number',
        'vendor_id',
        'warehouse_id',
        'po_reference',
        'status', // pending, in_progress, completed, cancelled
        'items', // array of items with quantities
        'putaway_status', // pending, in_progress, completed
        'received_by',
        'approved_by',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'items' => 'array',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the vendor
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Get the warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get user who received the GRN
     */
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get user who approved the GRN
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Approve GRN
     */
    public function approve($userId)
    {
        $this->update([
            'status' => 'in_progress',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    /**
     * Start putaway process
     */
    public function startPutaway()
    {
        $this->update([
            'putaway_status' => 'in_progress'
        ]);
    }

    /**
     * Complete GRN
     */
    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'putaway_status' => 'completed'
        ]);
    }

    /**
     * Check if all items are putaway
     */
    public function isFullyPutaway()
    {
        // Check if all items in GRN have corresponding inventory records
        foreach ($this->items as $grnItem) {
            $totalPutaway = Inventory::where('item_id', $grnItem['item_id'])
                                   ->where(function($q) {
                                       $q->where('reference_doc', $this->grn_number)
                                         ->orWhere('metadata.grn_id', $this->_id);
                                   })
                                   ->sum('quantity');
            
            if ($totalPutaway < $grnItem['quantity']) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get putaway progress percentage
     */
    public function getPutawayProgress()
    {
        $totalQty = 0;
        $putawayQty = 0;

        foreach ($this->items as $grnItem) {
            $totalQty += $grnItem['quantity'];
            
            $itemPutaway = Inventory::where('item_id', $grnItem['item_id'])
                                  ->where(function($q) {
                                      $q->where('reference_doc', $this->grn_number)
                                        ->orWhere('metadata.grn_id', $this->_id);
                                  })
                                  ->sum('quantity');
            
            $putawayQty += min($itemPutaway, $grnItem['quantity']);
        }

        return $totalQty > 0 ? ($putawayQty / $totalQty) * 100 : 0;
    }

    /**
     * Scope for pending GRNs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for in-progress GRNs
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for completed GRNs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope by warehouse
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope by vendor
     */
    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }
}

