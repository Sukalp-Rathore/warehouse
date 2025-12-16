<?php

namespace App\Helpers;

use App\Models\Client\User;
use App\Models\Client\Role;
use App\Models\Client\Permission;
use App\Models\Master\Client as MasterClient;
use App\Services\TenantBillingService;
use Illuminate\Support\Str;

class PermissionHelper
{
    /**
     * Check if user has specific permission
     */
    public static function hasPermission($user, $permission)
    {
        if (!$user) {
            return false;
        }

        // Get user permissions
        $userPermissions = $user->permissions ?? [];
        
        // Get role permissions
        $rolePermissions = [];
        if ($user->role) {
            $rolePermissions = $user->role->permissions ?? [];
        }
        
        // Check if permission exists in either user or role permissions
        return in_array($permission, $userPermissions) || 
               in_array($permission, $rolePermissions);
    }

    /**
     * Check if user has access to specific plant
     */
    public static function hasPlantAccess($user, $plantId)
    {
        if (!$user) {
            return false;
        }

        $plantAccess = self::normalizeAccessList($user->plant_access ?? []);
        return in_array($plantId, $plantAccess);
    }

    /**
     * Check if user has access to specific warehouse
     */
    public static function hasWarehouseAccess($user, $warehouseId)
    {
        if (!$user) {
            return false;
        }

        $warehouseAccess = self::normalizeAccessList($user->warehouse_access ?? []);
        return in_array($warehouseId, $warehouseAccess);
    }

    /**
     * Get all plants user has access to
     */
    public static function getUserAccessiblePlants($user)
    {
        if (!$user) {
            return collect();
        }

        $plantIds = self::normalizeAccessList($user->plant_access ?? []);
        if (empty($plantIds)) {
            return collect();
        }

        return \App\Models\Client\Plant::whereIn('_id', $plantIds)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get all warehouses user has access to
     */
    public static function getUserAccessibleWarehouses($user)
    {
        if (!$user) {
            return collect();
        }

        $warehouseIds = self::normalizeAccessList($user->warehouse_access ?? []);
        if (empty($warehouseIds)) {
            return collect();
        }

        return \App\Models\Client\Warehouse::whereIn('_id', $warehouseIds)
            ->where('is_active', true)
            ->where('status', 'active')
            ->get();
    }

    /**
     * Check if user has any of the specified permissions
     */
    public static function hasAnyPermission($user, $permissions)
    {
        if (!$user || !is_array($permissions)) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (self::hasPermission($user, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the specified permissions
     */
    public static function hasAllPermissions($user, $permissions)
    {
        if (!$user || !is_array($permissions)) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!self::hasPermission($user, $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all permissions for a user (user + role permissions)
     */
    public static function getAllPermissions($user)
    {
        if (!$user) {
            return [];
        }

        $userPermissions = $user->permissions ?? [];
        $rolePermissions = [];
        
        if ($user->role) {
            $rolePermissions = $user->role->permissions ?? [];
        }

        return array_unique(array_merge($userPermissions, $rolePermissions));
    }

    /**
     * Check if user can access a specific resource based on permissions
     */
    public static function canAccess($user, $resource, $action = 'view')
    {
        $permission = $resource . '.' . $action;
        return self::hasPermission($user, $permission);
    }

    /**
     * Static cache for permissions
     */
    private static $permissionCache = null;
    private static $cacheCleared = false;

    /**
     * Get available permissions list
     */
    public static function getAvailablePermissions($forceRefresh = false)
    {
        // If cache was cleared or force refresh, reset cache
        if ($forceRefresh || self::$cacheCleared || is_null(self::$permissionCache)) {
            try {
                $permissions = Permission::active()->orderBy('label')->get();
                if ($permissions->isNotEmpty()) {
                    self::$permissionCache = $permissions->mapWithKeys(function ($permission) {
                        $label = $permission->label ?: \Illuminate\Support\Str::title(str_replace('.', ' ', $permission->code));
                        return [$permission->code => $label];
                    })->toArray();
                } else {
                    self::$permissionCache = self::getDefaultPermissionList();
                }
                self::$cacheCleared = false; // Reset the cleared flag
            } catch (\Throwable $e) {
                \Log::warning('PermissionHelper: unable to load permissions', ['error' => $e->getMessage()]);
                self::$permissionCache = self::getDefaultPermissionList();
                self::$cacheCleared = false;
            }
        }

        return self::$permissionCache;
    }

    /**
     * Clear the permission cache
     */
    public static function clearPermissionCache()
    {
        self::$permissionCache = null;
        self::$cacheCleared = true;
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin($user)
    {
        if (!$user) {
            return false;
        }

        return ($user->role && $user->role->name === 'Admin') ||
               (is_string($user->role ?? null) && strtolower($user->role) === 'admin');
    }

    /**
     * Check if user can manage other users
     */
    public static function canManageUsers($user)
    {
        return self::hasAnyPermission($user, ['users.create', 'users.edit', 'users.delete']);
    }

    /**
     * Check if user can manage warehouse
     */
    public static function canManageWarehouse($user)
    {
        return self::hasAnyPermission($user, ['warehouse.create', 'warehouse.edit', 'warehouse.delete']);
    }

    /**
     * Check if user can manage inventory
     */
    public static function canManageInventory($user)
    {
        return self::hasAnyPermission($user, ['inventory.create', 'inventory.edit', 'inventory.delete']);
    }
    /**
     * Normalize access list values (handles stringified JSON, Mongo arrays, etc.)
     */
    private static function normalizeAccessList($access)
    {
        if (empty($access)) {
            return [];
        }

        // If already array, return
        if (is_array($access)) {
            return $access;
        }

        // If it's a string, attempt JSON decode
        if (is_string($access)) {
            $decoded = json_decode($access, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // If comma-separated string
            if (str_contains($access, ',')) {
                return array_filter(array_map('trim', explode(',', $access)));
            }

            // Single value string
            return [$access];
        }

        // If it's MongoDB BSON array or other traversable
        if ($access instanceof \MongoDB\Model\BSONArray) {
            return $access->getArrayCopy();
        }

        if ($access instanceof \Traversable) {
            return iterator_to_array($access);
        }

        return (array) $access;
    }

    /**
     * Default permission list to seed / fallback.
     */
    private static function getDefaultPermissionList()
    {
        return [
            'warehouse.view' => 'View Warehouse',
            'warehouse.create' => 'Create Warehouse',
            'warehouse.edit' => 'Edit Warehouse',
            'warehouse.delete' => 'Delete Warehouse',
            'inventory.view' => 'View Inventory',
            'inventory.create' => 'Create Inventory',
            'inventory.edit' => 'Edit Inventory',
            'inventory.delete' => 'Delete Inventory',
            'reports.view' => 'View Reports',
            'reports.export' => 'Export Reports',
            'users.view' => 'View Users',
            'users.create' => 'Create Users',
            'users.edit' => 'Edit Users',
            'users.delete' => 'Delete Users',
            'settings.view' => 'View Settings',
            'settings.edit' => 'Edit Settings',
        ];
    }

    /**
     * Get the current tenant (master client record) from session.
     */
    public static function getCurrentTenant(): ?MasterClient
    {
        $clientId = session('client_id');

        if (!$clientId) {
            return null;
        }

        return MasterClient::where('client_id', $clientId)
            ->where(function ($q) {
                $q->whereNull('record_type')->orWhere('record_type', 'tenant');
            })
            ->first();
    }

    /**
     * Check if current tenant has a specific plan feature enabled.
     *
     * Features are defined per plan in config/plans.php.
     */
    public static function tenantHasFeature(string $feature): bool
    {
        $tenant = self::getCurrentTenant();

        if (!$tenant) {
            // No tenant context – do not block access
            return true;
        }

        /** @var TenantBillingService $billing */
        $billing = app(TenantBillingService::class);
        $plan = $billing->getPlanDefinition($tenant);

        if (!$plan || !isset($plan['features'][$feature])) {
            // Feature not explicitly defined → allow by default
            return true;
        }

        $value = $plan['features'][$feature];

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value > 0;
        }

        return (bool) $value;
    }

    /**
     * Check if tenant billing status allows using the system.
     */
    public static function isTenantBillingActive(): bool
    {
        $tenant = self::getCurrentTenant();

        if (!$tenant) {
            return true;
        }

        // Treat canceled or permanently disabled tenants as inactive.
        return !in_array($tenant->billing_status, ['canceled', 'canceled_at_period_end', 'disabled'], true);
    }
}

