<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Auth\Authenticatable;
use App\Models\Master\AdminUser;
use App\Models\Client\User as ClientUser;
use App\Models\Master\Client;

class MultiTenantUser extends Model implements UserContract
{
    use Authenticatable;

    protected $primaryKey = '_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'email',
        'password',
        'name',
        'role',
        'permissions',
        'plant_access',
        'warehouse_access',
        'client_id',
        'tenant_type',
        'database_name'
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
        'warehouse_access' => 'array',
    ];

    /**
     * Create a MultiTenantUser from an AdminUser
     */
    public static function fromAdminUser(AdminUser $adminUser)
    {
        $user = new self();
        $user->fill([
            'email' => $adminUser->email,
            'password' => $adminUser->password,
            'name' => $adminUser->name,
            'role' => $adminUser->role,
            'tenant_type' => 'master',
            'client_id' => null,
            'database_name' => null
        ]);
        $user->_id = $adminUser->getKey();
        $user->exists = true;
        return $user;
    }

    /**
     * Create a MultiTenantUser from a ClientUser
     */
    public static function fromClientUser(ClientUser $clientUser, $clientId, $databaseName)
    {
        $user = new self();
        
        // Get role name - check direct role field first, then relationship
        $roleName = null;
        
        // Priority 1: Check direct 'role' field (string) from attributes
        if (isset($clientUser->attributes['role']) && !empty($clientUser->attributes['role']) && is_string($clientUser->attributes['role'])) {
            $roleName = $clientUser->attributes['role'];
        }
        // Priority 2: Check direct 'role' property access
        elseif (property_exists($clientUser, 'role') && is_string($clientUser->role)) {
            $roleName = $clientUser->role;
        }
        // Priority 3: Check if role is a relationship object
        elseif ($clientUser->role && is_object($clientUser->role) && isset($clientUser->role->name)) {
            $roleName = $clientUser->role->name;
        }
        // Priority 4: Try to load role relationship if role_id exists
        elseif ($clientUser->role_id) {
            try {
                if (!$clientUser->relationLoaded('role')) {
                    $clientUser->load('role');
                }
                if ($clientUser->role && is_object($clientUser->role) && isset($clientUser->role->name)) {
                    $roleName = $clientUser->role->name;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to load role for user', [
                    'user_id' => $clientUser->getKey(),
                    'role_id' => $clientUser->role_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        \Log::info('MultiTenantUser::fromClientUser', [
            'user_id' => $clientUser->getKey(),
            'role_id' => $clientUser->role_id ?? 'not_set',
            'role_direct' => $clientUser->attributes['role'] ?? 'not_set',
            'role_name' => $roleName,
            'role_loaded' => $clientUser->relationLoaded('role')
        ]);
        
        $user->fill([
            'email' => $clientUser->email,
            'password' => $clientUser->password,
            'name' => $clientUser->name,
            'role' => $roleName,
            'permissions' => $clientUser->permissions,
            'plant_access' => $clientUser->plant_access ?? [],
            'warehouse_access' => $clientUser->warehouse_access ?? [],
            'tenant_type' => 'client',
            'client_id' => $clientId,
            'database_name' => $databaseName
        ]);
        $user->_id = $clientUser->getKey();
        $user->exists = true;
        return $user;
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return '_id';
    }

    /**
     * Get the name of the "remember me" token for the user.
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the value of the model's primary key.
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Get the value of the model's primary key.
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the token value for the "remember me" session.
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }
}
