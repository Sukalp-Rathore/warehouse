<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemCategory extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'item_categories';

    protected $fillable = [
        'category_code',
        'category_name',
        'description',
        'parent_id',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get parent category
     */
    public function parent()
    {
        return $this->belongsTo(ItemCategory::class, 'parent_id');
    }

    /**
     * Get child categories
     */
    public function children()
    {
        return $this->hasMany(ItemCategory::class, 'parent_id');
    }

    /**
     * Get all items in this category
     */
    public function items()
    {
        return Item::where(function($q) {
            $q->where('category', $this->category_code)
              ->orWhere('category_id', $this->_id);
        })->get();
    }

    /**
     * Check if category is root (no parent)
     */
    public function isRoot()
    {
        return is_null($this->parent_id);
    }

    /**
     * Get full category path (parent > child)
     */
    public function getFullPath()
    {
        $path = [$this->category_name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->category_name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for root categories (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope by parent
     */
    public function scopeByParent($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }
}

