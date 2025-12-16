<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'sections';

    protected $fillable = [
        'section_code',
        'section_name',
        'storage_type_id',
        'warehouse_id',
        'zone_code',
        'description',
        'rows',
        'columns',
        'bin_volume',
        'bin_weight',
        'status',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rows' => 'integer',
        'columns' => 'integer',
        'bin_volume' => 'decimal:2',
        'bin_weight' => 'decimal:2',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the warehouse that owns this section
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the storage type for this section
     */
    public function storageType()
    {
        return $this->belongsTo(StorageType::class, 'storage_type_id');
    }

    /**
     * Get all bins in this section
     */
    public function bins()
    {
        return Bin::where('section_id', $this->_id)->get();
    }

    /**
     * Scope for active sections
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
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
     * Scope by zone
     */
    public function scopeByZone($query, $zoneCode)
    {
        return $query->where('zone_code', $zoneCode);
    }

    /**
     * Get total number of bins in this section (rows * columns)
     */
    public function getTotalBinsAttribute()
    {
        if ($this->rows && $this->columns) {
            return $this->rows * $this->columns;
        }
        return 0;
    }

    /**
     * Get bin at specific grid position
     */
    public function getBinAtPosition($row, $column)
    {
        return Bin::where('section_id', $this->_id)
                  ->where('row_number', $row)
                  ->where('column_number', $column)
                  ->first();
    }
}

