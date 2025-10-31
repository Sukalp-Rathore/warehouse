<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StorageType extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'storage_types';

    protected $fillable = [
        'code',
        'name',
        'description',
        'warehouse_id',
        'status',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the warehouse that owns this storage type
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get all sections for this storage type
     */
    public function sections()
    {
        return Section::where('storage_type_id', $this->_id)->get();
    }

    /**
     * Get all bins for this storage type
     */
    public function bins()
    {
        return Bin::where('storage_type_id', $this->_id)->get();
    }

    /**
     * Scope for active storage types
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
}

