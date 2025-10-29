<?php

namespace App\Helpers;

use App\Models\Client\User;
use App\Models\Client\Role;

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

        $plantAccess = $user->plant_access ?? [];
        return in_array($plantId, $plantAccess);
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
     * Get available permissions list
     */
    public static function getAvailablePermissions()
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
     * Check if user is admin
     */
    public static function isAdmin($user)
    {
        if (!$user) {
            return false;
        }

        return $user->role && $user->role->name === 'Admin';
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
}
