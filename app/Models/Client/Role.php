<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'roles';

    protected $fillable = [
        'name',
        'description',
        'permissions',
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
     * Get the permissions attribute
     * Ensures permissions is always an array
     */
    public function getPermissionsAttribute($value)
    {
        // If value is already an array, return it
        if (is_array($value)) {
            return $value;
        }
        
        // If value is null, return empty array
        if ($value === null) {
            return [];
        }
        
        // If value is a string, try to decode it
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        // Default to empty array
        return [];
    }

    /**
     * Set the permissions attribute
     * Ensures permissions is always stored as array
     */
    public function setPermissionsAttribute($value)
    {
        // If it's already an array, store it directly
        if (is_array($value)) {
            $this->attributes['permissions'] = $value;
        } elseif (is_string($value)) {
            // If it's a string, try to decode it
            $decoded = json_decode($value, true);
            $this->attributes['permissions'] = is_array($decoded) ? $decoded : [];
        } else {
            // Default to empty array
            $this->attributes['permissions'] = [];
        }
    }

    /**
     * Get users with this role
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Check if role has specific permission
     */
    public function hasPermission($permission)
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Add permission to role
     */
    public function addPermission($permission)
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
    }

    /**
     * Remove permission from role
     */
    public function removePermission($permission)
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_diff($permissions, [$permission]);
        $this->permissions = array_values($permissions);
        $this->save();
    }

    /**
     * Scope for active roles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
