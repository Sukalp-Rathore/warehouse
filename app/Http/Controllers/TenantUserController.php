<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client\User;
use App\Models\Client\Role;
use App\Models\Client\Plant;
use App\Models\Master\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TenantUserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }
        
        // Filter by role
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $users = $query->with('role')->paginate(15);
        $roles = Role::active()->get();
        
        return view('tenant.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::active()->get();
        $plants = Plant::active()->get();
        
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
        
        return view('tenant.users.create', compact('roles', 'plants', 'availablePermissions'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        \Log::info('User creation attempt', [
            'request_data' => $request->all(),
            'user' => session('user')
        ]);

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'mobile' => 'nullable|string|max:20',
            'role_id' => 'required',
            'permissions' => 'nullable|array',
            'plant_access' => 'nullable|array',
            'status' => 'required|in:active,inactive'
        ]);

        // Custom validation for role_id
        $validator->after(function ($validator) use ($request) {
            if ($request->role_id) {
                $role = Role::find($request->role_id);
                if (!$role) {
                    $validator->errors()->add('role_id', 'The selected role is invalid.');
                }
            }
        });

        if ($validator->fails()) {
            \Log::warning('User validation failed', ['errors' => $validator->errors()]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create user in tenant database
            $user = User::create([
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'mobile' => $request->mobile,
                'role_id' => $request->role_id,
                'permissions' => $request->permissions ?? [],
                'plant_access' => $request->plant_access ?? [],
                'status' => $request->status,
                'created_by' => session('user._id')
            ]);

            // Create user mapping in master database (one record per user)
            $clientId = session('client_id');
            $databaseName = session('database_name');

            // Create a separate user record in master.clients
            Client::create([
                'client_id' => $clientId,
                'name' => $request->name, // user's name
                'email' => $request->email,
                'hashed_email' => hash('sha256', $request->email),
                'database_name' => $databaseName,
                'status' => 'active',
                'record_type' => 'user',
                'user_id' => $user->_id, // reference to tenant user
            ]);

            \Log::info('User created successfully', [
                'user_id' => $user->_id,
                'email' => $request->email,
                'client_id' => $clientId
            ]);

            return redirect()->route('tenant.users.index')
                ->with('success', 'User created successfully.');
                
        } catch (\Exception $e) {
            \Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::with(['role', 'plants'])->findOrFail($id);
        return view('tenant.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::active()->get();
        $plants = Plant::active()->get();
        
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
        
        return view('tenant.users.edit', compact('user', 'roles', 'plants', 'availablePermissions'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username,' . $id . ',_id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id . ',_id',
            'password' => 'nullable|string|min:8|confirmed',
            'mobile' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,_id',
            'permissions' => 'nullable|array',
            'plant_access' => 'nullable|array',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updateData = [
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'role_id' => $request->role_id,
            'permissions' => $request->permissions ?? [],
            'plant_access' => $request->plant_access ?? [],
            'status' => $request->status,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('tenant.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting the current admin user
        if ($user->_id === session('user._id')) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }
        
        try {
            // Remove user record from master.clients
            $clientId = session('client_id');
            Client::where('client_id', $clientId)
                ->where('record_type', 'user')
                ->where('user_id', $user->_id)
                ->delete();

            \Log::info('User removed from clients collection', [
                'client_id' => $clientId,
                'user_id' => $user->_id,
                'email' => $user->email
            ]);

            // Delete user from tenant database
            $user->delete();
            
            \Log::info('User deleted successfully', [
                'user_id' => $id,
                'email' => $user->email
            ]);
            
            return redirect()->route('tenant.users.index')
                ->with('success', 'User deleted successfully.');
                
        } catch (\Exception $e) {
            \Log::error('User deletion failed', [
                'error' => $e->getMessage(),
                'user_id' => $id
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deactivating the current admin user
        if ($user->_id === session('user._id')) {
            return redirect()->back()
                ->with('error', 'You cannot deactivate your own account.');
        }
        
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();
        
        $status = $user->status === 'active' ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "User {$status} successfully.");
    }
}
