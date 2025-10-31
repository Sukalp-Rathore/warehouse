<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'items';

    protected $fillable = [
        'item_code',
        'description',
        'short_description',
        'category',
        'category_id',
        'subcategory',
        'uom',
        'weight',
        'dimensions',
        'volume',
        'preferred_storage_type_id',
        'preferred_bin_id',
        'vendor_id',
        'alternate_vendors',
        'minimum_order_quantity',
        'reorder_level',
        'reorder_quantity',
        'unit_price',
        'currency',
        'barcode',
        'image_url',
        'specifications',
        'is_active',
        'is_hazmat',
        'hazmat_class',
        'temperature_controlled',
        'created_by'
    ];

    protected $casts = [
        'dimensions' => 'array',
        'alternate_vendors' => 'array',
        'specifications' => 'array',
        'weight' => 'decimal:2',
        'volume' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'minimum_order_quantity' => 'integer',
        'reorder_level' => 'integer',
        'reorder_quantity' => 'integer',
        'is_active' => 'boolean',
        'is_hazmat' => 'boolean',
        'temperature_controlled' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the primary vendor for this item
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Get alternate vendors
     */
    public function alternateVendors()
    {
        if (!$this->alternate_vendors) {
            return collect();
        }
        return Vendor::whereIn('_id', $this->alternate_vendors)->get();
    }

    /**
     * Get preferred storage type
     */
    public function preferredStorageType()
    {
        return $this->belongsTo(StorageType::class, 'preferred_storage_type_id');
    }

    /**
     * Get preferred bin
     */
    public function preferredBin()
    {
        return $this->belongsTo(Bin::class, 'preferred_bin_id');
    }

    /**
     * Get category model
     */
    public function categoryModel()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    /**
     * Check if item needs reordering
     */
    public function needsReorder($currentStock)
    {
        return $currentStock <= $this->reorder_level;
    }

    /**
     * Generate barcode if not exists
     */
    public function generateBarcode()
    {
        if (!$this->barcode) {
            // Generate based on item code or random
            $this->barcode = $this->item_code . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $this->save();
        }
        return $this->barcode;
    }

    /**
     * Scope for active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by vendor
     */
    public function scopeByVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope for hazmat items
     */
    public function scopeHazmat($query)
    {
        return $query->where('is_hazmat', true);
    }

    /**
     * Scope for temperature controlled items
     */
    public function scopeTemperatureControlled($query)
    {
        return $query->where('temperature_controlled', true);
    }

    /**
     * Search scope
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('item_code', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('short_description', 'like', "%{$term}%")
              ->orWhere('barcode', 'like', "%{$term}%");
        });
    }
}

