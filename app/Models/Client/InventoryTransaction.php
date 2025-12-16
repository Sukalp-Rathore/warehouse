<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'inventory_transactions';

    protected $fillable = [
        'transaction_type', // PUTAWAY, TRANSFER, PICK, ADJUSTMENT, QC_HOLD, QC_RELEASE
        'item_id',
        'item_code',
        'batch_id',
        'from_bin_id',
        'to_bin_id',
        'quantity',
        'uom',
        'reference_doc', // GRN number, Transfer ID, etc.
        'user_id',
        'timestamp',
        'notes',
        'metadata'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'timestamp' => 'datetime',
        'metadata' => 'array',
    ];

    protected $dates = [
        'timestamp',
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
     * Get the batch
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    /**
     * Get the user who performed the transaction
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get source bin
     */
    public function fromBin()
    {
        return $this->belongsTo(Bin::class, 'from_bin_id');
    }

    /**
     * Get destination bin
     */
    public function toBin()
    {
        return $this->belongsTo(Bin::class, 'to_bin_id');
    }

    /**
     * Create a new transaction record
     */
    public static function createTransaction($data)
    {
        $transaction = self::create(array_merge($data, [
            'timestamp' => now(),
            'user_id' => session('user._id'),
        ]));
        
        return $transaction;
    }

    /**
     * Scope by transaction type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    /**
     * Scope by item
     */
    public function scopeByItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope by batch
     */
    public function scopeByBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Scope recent transactions
     */
    public function scopeRecent($query, $limit = 50)
    {
        return $query->orderBy('timestamp', 'desc')->limit($limit);
    }

    /**
     * Scope by reference document
     */
    public function scopeByReference($query, $referenceDoc)
    {
        return $query->where('reference_doc', $referenceDoc);
    }
}

