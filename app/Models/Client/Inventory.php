<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'inventory';

    protected $fillable = [
        'item_id',
        'item_code',
        'warehouse_id',
        'storage_type_id',
        'section_id',
        'bin_id',
        'batch_id',
        'quantity',
        'available_qty',
        'blocked_qty',
        'reserved_qty',
        'status', // available, reserved, blocked, qc_hold
        'barcode',
        'qr_code',
        'rfid_tag',
        'expiry_date',
        'last_movement_at',
        'created_by'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'available_qty' => 'decimal:2',
        'blocked_qty' => 'decimal:2',
        'reserved_qty' => 'decimal:2',
        'expiry_date' => 'datetime',
        'last_movement_at' => 'datetime',
    ];

    protected $dates = [
        'expiry_date',
        'last_movement_at',
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
     * Get the warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the storage type
     */
    public function storageType()
    {
        return $this->belongsTo(StorageType::class, 'storage_type_id');
    }

    /**
     * Get the section
     */
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Get the bin
     */
    public function bin()
    {
        return $this->belongsTo(Bin::class, 'bin_id');
    }

    /**
     * Get the batch
     */
    public function batch()
    {
        return $this->belongsTo(Batch::class, 'batch_id');
    }

    /**
     * Get available quantity
     */
    public function getAvailableQuantity()
    {
        return $this->available_qty ?? 0;
    }

    /**
     * Block quantity (move from available to blocked)
     */
    public function blockQuantity($qty)
    {
        if ($this->available_qty >= $qty) {
            $this->available_qty -= $qty;
            $this->blocked_qty += $qty;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Release blocked quantity
     */
    public function releaseQuantity($qty)
    {
        if ($this->blocked_qty >= $qty) {
            $this->blocked_qty -= $qty;
            $this->available_qty += $qty;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Reserve quantity
     */
    public function reserveQuantity($qty)
    {
        if ($this->available_qty >= $qty) {
            $this->available_qty -= $qty;
            $this->reserved_qty += $qty;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Scope for available inventory
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
                    ->where('available_qty', '>', 0);
    }

    /**
     * Scope for reserved inventory
     */
    public function scopeReserved($query)
    {
        return $query->where('status', 'reserved');
    }

    /**
     * Scope for blocked inventory
     */
    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    /**
     * Scope for QC hold inventory
     */
    public function scopeQcHold($query)
    {
        return $query->where('status', 'qc_hold');
    }

    /**
     * Scope by warehouse
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope by item
     */
    public function scopeByItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope by bin
     */
    public function scopeByBin($query, $binId)
    {
        return $query->where('bin_id', $binId);
    }

    /**
     * Scope by batch
     */
    public function scopeByBatch($query, $batchId)
    {
        return $query->where('batch_id', $batchId);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get full location path
     */
    public function getLocationPath()
    {
        $path = [];
        
        if ($this->warehouse) {
            $path[] = $this->warehouse->warehouse_name;
            if ($this->warehouse->plant) {
                array_unshift($path, $this->warehouse->plant->plant_name);
            }
        }
        
        if ($this->storageType) {
            $path[] = $this->storageType->name;
        }
        
        if ($this->section) {
            $path[] = $this->section->section_name;
        }
        
        if ($this->bin) {
            $path[] = $this->bin->bin_code;
        }
        
        return implode(' → ', $path);
    }

    /**
     * Boot method to add validation before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($inventory) {
            // Validate barcode uniqueness (excluding current record)
            if ($inventory->barcode) {
                $barcodeExists = static::where('barcode', $inventory->barcode)
                    ->where('_id', '!=', $inventory->_id)
                    ->exists();
                if ($barcodeExists) {
                    throw new \Exception('Barcode must be unique. The barcode "' . $inventory->barcode . '" already exists.');
                }
            }

            // Validate QR code uniqueness (excluding current record)
            if ($inventory->qr_code) {
                $qrCodeExists = static::where('qr_code', $inventory->qr_code)
                    ->where('_id', '!=', $inventory->_id)
                    ->exists();
                if ($qrCodeExists) {
                    throw new \Exception('QR code must be unique. The QR code "' . $inventory->qr_code . '" already exists.');
                }
            }

            // Validate RFID tag uniqueness (excluding current record)
            if ($inventory->rfid_tag) {
                $rfidTagExists = static::where('rfid_tag', $inventory->rfid_tag)
                    ->where('_id', '!=', $inventory->_id)
                    ->exists();
                if ($rfidTagExists) {
                    throw new \Exception('RFID tag must be unique. The RFID tag "' . $inventory->rfid_tag . '" already exists.');
                }
            }
        });
    }

    /**
     * Check if a barcode is unique
     */
    public static function isBarcodeUnique($barcode, $excludeId = null)
    {
        $query = static::where('barcode', $barcode);
        if ($excludeId) {
            $query->where('_id', '!=', $excludeId);
        }
        return !$query->exists();
    }

    /**
     * Check if a QR code is unique
     */
    public static function isQRCodeUnique($qrCode, $excludeId = null)
    {
        $query = static::where('qr_code', $qrCode);
        if ($excludeId) {
            $query->where('_id', '!=', $excludeId);
        }
        return !$query->exists();
    }

    /**
     * Check if an RFID tag is unique
     */
    public static function isRFIDTagUnique($rfidTag, $excludeId = null)
    {
        $query = static::where('rfid_tag', $rfidTag);
        if ($excludeId) {
            $query->where('_id', '!=', $excludeId);
        }
        return !$query->exists();
    }
}
