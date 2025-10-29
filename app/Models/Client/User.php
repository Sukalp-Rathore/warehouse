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
        'permissions',
        'plant_access',
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
        'permissions' => 'array',
        'plant_access' => 'array',
    ];

    /**
     * Get the role associated with the user
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get plants the user has access to
     */
    public function plants()
    {
        return Plant::whereIn('_id', $this->plant_access ?? [])->get();
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
