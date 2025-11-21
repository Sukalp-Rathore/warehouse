<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bin extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'bins';

    protected $fillable = [
        'bin_code',
        'bin_name',
        'warehouse_id',
        'storage_type_id',
        'section_id',
        'zone_code',
        'coordinates',
        'max_volume',
        'max_weight',
        'current_volume',
        'current_weight',
        'reorder_level',
        'reorder_quantity',
        'status',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'coordinates' => 'array',
        'max_volume' => 'decimal:2',
        'max_weight' => 'decimal:2',
        'current_volume' => 'decimal:2',
        'current_weight' => 'decimal:2',
        'reorder_level' => 'decimal:2',
        'reorder_quantity' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the warehouse that owns this bin
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the storage type for this bin
     */
    public function storageType()
    {
        return $this->belongsTo(StorageType::class, 'storage_type_id');
    }

    /**
     * Get the section for this bin
     */
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Check if bin is available
     */
    public function isAvailable()
    {
        return $this->status === 'available' && $this->is_active;
    }

    /**
     * Check if bin is occupied
     */
    public function isOccupied()
    {
        return $this->status === 'occupied';
    }

    /**
     * Get capacity utilization percentage
     */
    public function getCapacityUtilization()
    {
        if ($this->max_volume > 0) {
            return ($this->current_volume / $this->max_volume) * 100;
        }
        return 0;
    }

    /**
     * Scope for available bins
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('is_active', true);
    }

    /**
     * Scope by warehouse
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope by storage type
     */
    public function scopeByStorageType($query, $storageTypeId)
    {
        return $query->where('storage_type_id', $storageTypeId);
    }

    /**
     * Scope by section
     */
    public function scopeBySection($query, $sectionId)
    {
        return $query->where('section_id', $sectionId);
    }

    /**
     * Scope by zone
     */
    public function scopeByZone($query, $zoneCode)
    {
        return $query->where('zone_code', $zoneCode);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}

