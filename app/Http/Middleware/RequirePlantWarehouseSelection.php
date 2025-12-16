<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TenantService;
use App\Helpers\PermissionHelper;

class RequirePlantWarehouseSelection
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip for master admin users
        if (session('tenant_type') === 'master') {
            return $next($request);
        }

        // Skip if not authenticated
        if (!session('authenticated')) {
            return redirect()->route('login');
        }

        // Skip if not a client user
        if (session('tenant_type') !== 'client') {
            return $next($request);
        }

        $user = session('user');
        $userRole = strtolower(trim($user['role'] ?? ''));
        $isTenantAdmin = $user && in_array($userRole, ['admin', 'administrator']);
        
        \Log::info('RequirePlantWarehouseSelection middleware check', [
            'user_role' => $user['role'] ?? 'not_set',
            'userRole_lowercase' => $userRole,
            'isTenantAdmin' => $isTenantAdmin,
            'user_data' => $user
        ]);

        if ($isTenantAdmin) {
            \Log::info('Tenant admin detected in middleware - allowing access');
            // Tenant admins can access without selecting plant/warehouse
            return $next($request);
        }

        // Check if plant and warehouse are already selected
        if (session('selected_plant_id') && session('selected_warehouse_id')) {
            // Validate that the selected plant and warehouse still exist and user has access
            $this->tenantService->setTenantConnection(session('database_name'));
            
            if ($user) {
                $currentUserId = $user['_id'] ?? session('user_id');
                $currentUser = $currentUserId ? \App\Models\Client\User::find($currentUserId) : null;
                $plantId = session('selected_plant_id');
                $warehouseId = session('selected_warehouse_id');
                
                // Check if plant and warehouse still exist
                $plant = \App\Models\Client\Plant::find($plantId);
                $warehouse = \App\Models\Client\Warehouse::find($warehouseId);
                
                // If plant or warehouse was deleted, clear selection and redirect
                if (!$plant || !$warehouse) {
                    session()->forget(['selected_plant_id', 'selected_plant_name', 'selected_warehouse_id', 'selected_warehouse_name']);
                    session()->save();
                    return redirect()->route('plant-warehouse.select')
                        ->with('error', 'The selected plant or warehouse no longer exists. Please select again.');
                }
                
                // Verify warehouse still belongs to plant
                if ($warehouse->plant_id !== $plant->_id) {
                    session()->forget(['selected_plant_id', 'selected_plant_name', 'selected_warehouse_id', 'selected_warehouse_name']);
                    session()->save();
                    return redirect()->route('plant-warehouse.select')
                        ->with('error', 'The selected warehouse no longer belongs to the selected plant. Please select again.');
                }
                
                // Verify user still has access
                if ($currentUser &&
                    PermissionHelper::hasPlantAccess($currentUser, $plantId) && 
                    PermissionHelper::hasWarehouseAccess($currentUser, $warehouseId)) {
                    return $next($request);
                } else {
                    // User access was revoked, clear selection
                    session()->forget(['selected_plant_id', 'selected_plant_name', 'selected_warehouse_id', 'selected_warehouse_name']);
                    session()->save();
                    return redirect()->route('plant-warehouse.select')
                        ->with('error', 'You no longer have access to the selected plant or warehouse. Please select again.');
                }
            }
        }

        // Check if user has only one plant and one warehouse (auto-select scenario)
        $this->tenantService->setTenantConnection(session('database_name'));
        
        if ($user) {
            $currentUserId = $user['_id'] ?? session('user_id');
            $currentUser = $currentUserId ? \App\Models\Client\User::find($currentUserId) : null;
            if ($currentUser) {
                $plants = PermissionHelper::getUserAccessiblePlants($currentUser);
                $warehouses = PermissionHelper::getUserAccessibleWarehouses($currentUser);
            
            // If user has only one plant and one warehouse, auto-select
            if ($plants->count() === 1 && $warehouses->count() === 1) {
                $plant = $plants->first();
                $warehouse = $warehouses->first();
                
                // Verify warehouse belongs to plant
                if ($warehouse->plant_id === $plant->_id) {
                    session([
                        'selected_plant_id' => $plant->_id,
                        'selected_plant_name' => $plant->plant_name,
                        'selected_warehouse_id' => $warehouse->_id,
                        'selected_warehouse_name' => $warehouse->warehouse_name,
                    ]);
                    session()->save();
                    
                    return $next($request);
                }
            }
            }
        }

        // Redirect to selection screen
        return redirect()->route('plant-warehouse.select');
    }
}

