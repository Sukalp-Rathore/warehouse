<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plant extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'plants';

    protected $fillable = [
        'plant_code',
        'plant_name',
        'location',
        'address',
        'contact_person',
        'contact_phone',
        'contact_email',
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
     * Get users with access to this plant
     */
    public function users()
    {
        return User::where('plant_access', $this->_id)->get();
    }

    /**
     * Scope for active plants
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get plant by code
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('plant_code', $code);
    }
}
