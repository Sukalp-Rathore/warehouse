<?php

namespace App\Models\Client;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model implements UserContract
{
    use Authenticatable, HasFactory;

    protected $connection = 'tenant';
    protected $collection = 'users';

    protected $fillable = [
        'username',
        'email',
        'password',
        'mobile',
        'role_id',
        'role',
        'permissions',
        'plant_access',
        'warehouse_access',
        'status',
        'client_id',
        'name',
        'created_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get permissions attribute - ensure it's always an array
     */
    public function getPermissionsAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if ($value === null) {
            return [];
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * Set permissions attribute - ensure it's stored as array
     */
    public function setPermissionsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['permissions'] = $value;
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->attributes['permissions'] = is_array($decoded) ? $decoded : [];
        } else {
            $this->attributes['permissions'] = [];
        }
    }

    /**
     * Get plant_access attribute - ensure it's always an array
     */
    public function getPlantAccessAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if ($value === null) {
            return [];
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * Set plant_access attribute - ensure it's stored as array
     */
    public function setPlantAccessAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['plant_access'] = $value;
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->attributes['plant_access'] = is_array($decoded) ? $decoded : [];
        } else {
            $this->attributes['plant_access'] = [];
        }
    }

    /**
     * Get warehouse_access attribute - ensure it's always an array
     */
    public function getWarehouseAccessAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if ($value === null) {
            return [];
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    /**
     * Set warehouse_access attribute - ensure it's stored as array
     */
    public function setWarehouseAccessAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['warehouse_access'] = $value;
        } elseif (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->attributes['warehouse_access'] = is_array($decoded) ? $decoded : [];
        } else {
            $this->attributes['warehouse_access'] = [];
        }
    }

    /**
     * Get the role associated with the user
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get plants the user has access to
     * Note: This is a helper method, not a relationship
     */
    public function getAccessiblePlants()
    {
        $plantIds = $this->plant_access ?? [];
        if (empty($plantIds)) {
            return collect();
        }
        return Plant::whereIn('_id', $plantIds)->get();
    }

    /**
     * Get warehouses the user has access to
     * Note: This is a helper method, not a relationship
     */
    public function getAccessibleWarehouses()
    {
        $warehouseIds = $this->warehouse_access ?? [];
        if (empty($warehouseIds)) {
            return collect();
        }
        return Warehouse::whereIn('_id', $warehouseIds)->get();
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission)
    {
        $userPermissions = $this->permissions ?? [];
        $rolePermissions = $this->role ? $this->role->permissions : [];
        
        return in_array($permission, $userPermissions) || 
               in_array($permission, $rolePermissions);
    }

    /**
     * Check if user has access to specific plant
     */
    public function hasPlantAccess($plantId)
    {
        return in_array($plantId, $this->plant_access ?? []);
    }

    /**
     * Add permission to user
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
     * Remove permission from user
     */
    public function removePermission($permission)
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_diff($permissions, [$permission]);
        $this->permissions = array_values($permissions);
        $this->save();
    }

    /**
     * Add plant access to user
     */
    public function addPlantAccess($plantId)
    {
        $plantAccess = $this->plant_access ?? [];
        if (!in_array($plantId, $plantAccess)) {
            $plantAccess[] = $plantId;
            $this->plant_access = $plantAccess;
            $this->save();
        }
    }

    /**
     * Remove plant access from user
     */
    public function removePlantAccess($plantId)
    {
        $plantAccess = $this->plant_access ?? [];
        $plantAccess = array_diff($plantAccess, [$plantId]);
        $this->plant_access = array_values($plantAccess);
        $this->save();
    }

    /**
     * Check if user has access to specific warehouse
     */
    public function hasWarehouseAccess($warehouseId)
    {
        return in_array($warehouseId, $this->warehouse_access ?? []);
    }

    /**
     * Add warehouse access to user
     */
    public function addWarehouseAccess($warehouseId)
    {
        $warehouseAccess = $this->warehouse_access ?? [];
        if (!in_array($warehouseId, $warehouseAccess)) {
            $warehouseAccess[] = $warehouseId;
            $this->warehouse_access = $warehouseAccess;
            $this->save();
        }
    }

    /**
     * Remove warehouse access from user
     */
    public function removeWarehouseAccess($warehouseId)
    {
        $warehouseAccess = $this->warehouse_access ?? [];
        $warehouseAccess = array_diff($warehouseAccess, [$warehouseId]);
        $this->warehouse_access = array_values($warehouseAccess);
        $this->save();
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for users by role
     */
    public function scopeByRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }
}
