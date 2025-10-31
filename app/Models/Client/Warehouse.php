<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'warehouses';

    protected $fillable = [
        'warehouse_code',
        'warehouse_name',
        'name',
        'plant_id',
        'plant_code',
        'address',
        'warehouse_type',
        'status',
        'storage_types',
        'description',
        'capacity',
        'current_utilization',
        'contact_person',
        'contact_phone',
        'contact_email',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'current_utilization' => 'integer',
        'storage_types' => 'array',
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the plant that owns this warehouse
     */
    public function plant()
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    /**
     * Get all storage types for this warehouse
     */
    public function storageTypes()
    {
        return StorageType::whereIn('_id', $this->storage_types ?? [])->get();
    }

    /**
     * Get all sections for this warehouse
     */
    public function sections()
    {
        return Section::where('warehouse_id', $this->_id)->get();
    }

    /**
     * Get all bins for this warehouse
     */
    public function bins()
    {
        return Bin::where('warehouse_id', $this->_id)->get();
    }

    /**
     * Scope for active warehouses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    /**
     * Scope by warehouse type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('warehouse_type', $type);
    }

    /**
     * Scope by plant
     */
    public function scopeByPlant($query, $plantId)
    {
        return $query->where('plant_id', $plantId);
    }
}
