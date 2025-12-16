<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Models\Client\Plant;
use App\Models\Client\Warehouse;
use App\Models\Client\User as ClientUser;

class PlantWarehouseSelectionController extends Controller
{
    /**
     * Show the plant and warehouse selection screen
     */
    public function show(Request $request)
    {
        // Only for client users, not master admins
        if (session('tenant_type') !== 'client') {
            return redirect()->route('dashboard');
        }

        $userSession = session('user');
        if (!$userSession) {
            return redirect()->route('login');
        }

        // Check if user is admin - admins bypass selection
        $userRole = strtolower(trim($userSession['role'] ?? ''));
        $isAdmin = in_array($userRole, ['admin', 'administrator']);
        
        \Log::info('PlantWarehouseSelectionController check', [
            'user_role' => $user['role'] ?? 'not_set',
            'userRole_lowercase' => $userRole,
            'isAdmin' => $isAdmin
        ]);
        
        if ($isAdmin) {
            \Log::info('Admin detected in selection controller - redirecting to dashboard');
            return redirect()->route('dashboard');
        }

        // Load full user model to access plant/warehouse arrays
        $currentUserId = $userSession['_id'] ?? session('user_id');
        $currentUser = $currentUserId ? ClientUser::find($currentUserId) : null;
        if (!$currentUser) {
            \Log::warning('PlantWarehouseSelectionController: user model not found, forcing logout', [
                'session_user' => $userSession
            ]);
            session()->flush();
            return redirect()->route('login')->withErrors(['error' => 'Session expired. Please login again.']);
        }

        // Get user's accessible plants and warehouses
        $plants = PermissionHelper::getUserAccessiblePlants($currentUser);
        $warehouses = PermissionHelper::getUserAccessibleWarehouses($currentUser);

        // If user has only one plant and one warehouse, auto-select and redirect
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
                
                return redirect()->route('dashboard');
            }
        }

        // Group warehouses by plant for display (string IDs for frontend)
        $warehousesByPlant = [];
        foreach ($warehouses as $warehouse) {
            $plantId = (string)($warehouse->plant_id ?? '');
            if (empty($plantId)) {
                continue;
            }
            if (!isset($warehousesByPlant[$plantId])) {
                $warehousesByPlant[$plantId] = [];
            }
            $warehousesByPlant[$plantId][] = [
                '_id' => (string)$warehouse->_id,
                'warehouse_name' => $warehouse->warehouse_name,
                'warehouse_code' => $warehouse->warehouse_code,
            ];
        }

        // Prepare plants with string IDs for dropdown
        $plantsForView = $plants->map(function ($plant) {
            $plant->string_id = (string)$plant->_id;
            $plant->string_plant_id = (string)$plant->_id;
            return $plant;
        });

        return view('Auth.select-plant-warehouse', [
            'plants' => $plantsForView,
            'warehousesByPlant' => $warehousesByPlant
        ]);
    }

    /**
     * Store the selected plant and warehouse
     */
    public function store(Request $request)
    {
        $request->validate([
            'plant_id' => 'required|string',
            'warehouse_id' => 'required|string',
        ]);

        // Only for client users
        if (session('tenant_type') !== 'client') {
            return redirect()->route('dashboard');
        }

        $userSession = session('user');
        if (!$userSession) {
            return redirect()->route('login');
        }

        $currentUserId = $userSession['_id'] ?? session('user_id');
        $currentUser = $currentUserId ? ClientUser::find($currentUserId) : null;
        if (!$currentUser) {
            session()->flush();
            return redirect()->route('login')->withErrors(['error' => 'Session expired. Please login again.']);
        }

        // Verify user has access to selected plant
        if (!PermissionHelper::hasPlantAccess($currentUser, $request->plant_id)) {
            return redirect()->back()
                ->withErrors(['plant_id' => 'You do not have access to the selected plant.'])
                ->withInput();
        }

        // Verify user has access to selected warehouse
        if (!PermissionHelper::hasWarehouseAccess($currentUser, $request->warehouse_id)) {
            return redirect()->back()
                ->withErrors(['warehouse_id' => 'You do not have access to the selected warehouse.'])
                ->withInput();
        }

        // Get plant and warehouse details
        $plant = Plant::find($request->plant_id);
        $warehouse = Warehouse::find($request->warehouse_id);

        // Verify plant exists
        if (!$plant) {
            return redirect()->back()
                ->withErrors(['plant_id' => 'The selected plant no longer exists.'])
                ->withInput();
        }

        // Verify warehouse exists
        if (!$warehouse) {
            return redirect()->back()
                ->withErrors(['warehouse_id' => 'The selected warehouse no longer exists.'])
                ->withInput();
        }

        // Verify warehouse belongs to plant
        if ($warehouse->plant_id !== $plant->_id) {
            return redirect()->back()
                ->withErrors(['warehouse_id' => 'The selected warehouse does not belong to the selected plant.'])
                ->withInput();
        }

        // Verify plant and warehouse are active
        if (!$plant->is_active) {
            return redirect()->back()
                ->withErrors(['plant_id' => 'The selected plant is not active.'])
                ->withInput();
        }

        if (!$warehouse->is_active || $warehouse->status !== 'active') {
            return redirect()->back()
                ->withErrors(['warehouse_id' => 'The selected warehouse is not active.'])
                ->withInput();
        }

        // Store in session
        session([
            'selected_plant_id' => $plant->_id,
            'selected_plant_name' => $plant->plant_name,
            'selected_warehouse_id' => $warehouse->_id,
            'selected_warehouse_name' => $warehouse->warehouse_name,
        ]);
        session()->save();

        return redirect()->route('dashboard')
            ->with('success', 'Plant and warehouse selected successfully.');
    }
}

