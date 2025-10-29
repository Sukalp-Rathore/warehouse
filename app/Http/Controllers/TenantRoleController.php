<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\Role;
use Illuminate\Support\Facades\Validator;

class TenantRoleController extends Controller
{
    /**
     * Display a listing of roles
     */
    public function index()
    {
        $roles = Role::with('users')->paginate(15);
        return view('tenant.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     */
    public function create()
    {
        // Define available permissions
        $availablePermissions = [
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
        
        return view('tenant.roles.create', compact('availablePermissions'));
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        \Log::info('Role creation attempt', [
            'request_data' => $request->all(),
            'user' => session('user')
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'is_active' => 'nullable'
        ]);

        if ($validator->fails()) {
            \Log::warning('Role validation failed', ['errors' => $validator->errors()]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $role = Role::create([
                'name' => $request->name,
                'description' => $request->description,
                'permissions' => $request->permissions ?? [],
                'is_active' => $request->has('is_active'),
                'created_by' => session('user._id')
            ]);

            \Log::info('Role created successfully', ['role_id' => $role->_id]);

            return redirect()->route('tenant.roles.index')
                ->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            \Log::error('Role creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create role: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified role
     */
    public function show($id)
    {
        $role = Role::with('users')->findOrFail($id);
        return view('tenant.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        
        // Define available permissions
        $availablePermissions = [
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
        
        return view('tenant.roles.edit', compact('role', 'availablePermissions'));
    }

    /**
     * Update the specified role
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $id . ',_id',
            'description' => 'nullable|string|max:500',
            'permissions' => 'nullable|array',
            'is_active' => 'nullable'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
            'permissions' => $request->permissions ?? [],
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('tenant.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Get permissions for a specific role (AJAX endpoint)
     */
    public function getPermissions($id)
    {
        \Log::info('Getting permissions for role', ['role_id' => $id]);
        
        try {
            $role = Role::findOrFail($id);
            \Log::info('Role found', ['role' => $role->toArray()]);
            
            // Handle permissions that might be stored as string instead of array
            $permissions = $role->permissions ?? [];
            if (is_string($permissions)) {
                // Try to decode JSON string
                $decoded = json_decode($permissions, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $permissions = $decoded;
                } else {
                    // If not valid JSON, treat as empty array
                    $permissions = [];
                }
            }
            
            \Log::info('Processed permissions', ['permissions' => $permissions]);
            
            return response()->json([
                'success' => true,
                'permissions' => $permissions
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting role permissions', [
                'role_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        // Check if role has users assigned
        if ($role->users()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete role. Users are currently assigned to this role.');
        }
        
        $role->delete();
        
        return redirect()->route('tenant.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
