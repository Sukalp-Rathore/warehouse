<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class Vendor extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'vendors';

    protected $fillable = [
        'vendor_code',
        'vendor_name',
        'contact_person',
        'contact_phone',
        'contact_email',
        'address',
        'vendor_type',
        'payment_terms',
        'tax_id',
        'allow_prebarcode',
        'vendor_portal_enabled',
        'portal_login',
        'portal_password',
        'rating',
        'status',
        'notes',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'address' => 'array',
        'allow_prebarcode' => 'boolean',
        'vendor_portal_enabled' => 'boolean',
        'rating' => 'decimal:1',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'portal_password',
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get all items supplied by this vendor
     */
    public function items()
    {
        return Item::where('vendor_id', $this->_id)->get();
    }

    /**
     * Set portal password with hashing
     */
    public function setPortalPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['portal_password'] = Hash::make($value);
        }
    }

    /**
     * Scope for active vendors
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    /**
     * Scope by vendor type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('vendor_type', $type);
    }

    /**
     * Scope for pre-barcode vendors
     */
    public function scopePrebarcodeEnabled($query)
    {
        return $query->where('allow_prebarcode', true);
    }

    /**
     * Scope for portal enabled vendors
     */
    public function scopePortalEnabled($query)
    {
        return $query->where('vendor_portal_enabled', true);
    }
}

