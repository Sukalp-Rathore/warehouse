<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
// Auth Routes 
Route::middleware(['web'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Test route without middleware
Route::get('/test-auth', function () {
    return response()->json([
        'auth_check' => session('authenticated', false),
        'user' => session('user'),
        'session_data' => session()->all()
    ]);
});

// Debug route for tenant admin navigation
Route::get('/debug-nav', function () {
    $user = session('user');
    $tenantType = session('tenant_type');
    $authenticated = session('authenticated');
    
    return response()->json([
        'authenticated' => $authenticated,
        'tenant_type' => $tenantType,
        'user_role' => $user['role'] ?? 'not_set',
        'user_data' => $user,
        'nav_condition_check' => [
            'authenticated' => $authenticated,
            'tenant_type_client' => $tenantType === 'client',
            'role_exists' => isset($user['role']),
            'role_value' => $user['role'] ?? 'not_set',
            'role_is_admin' => ($user['role'] ?? '') === 'admin',
            'full_condition' => $authenticated && $tenantType === 'client' && isset($user['role']) && $user['role'] === 'admin'
        ]
    ]);
});

// Debug route for testing role permissions
Route::get('/debug-roles', function () {
    $roles = \App\Models\Client\Role::all();
    return response()->json([
        'roles' => $roles->map(function($role) {
            return [
                'id' => $role->_id,
                'name' => $role->name,
                'permissions' => $role->permissions
            ];
        })
    ]);
})->middleware(['web', 'custom_auth', 'tenant_admin_only']);

// Debug route for testing specific role
Route::get('/debug-role/{id}', function ($id) {
    try {
        $role = \App\Models\Client\Role::findOrFail($id);
        return response()->json([
            'success' => true,
            'role' => [
                'id' => $role->_id,
                'name' => $role->name,
                'permissions' => $role->permissions
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
})->middleware(['web', 'custom_auth', 'tenant_admin_only']);

// Test dashboard without middleware
Route::get('/test-dashboard', function () {
    $user = session('user');
    
    $tenantInfo = [
        'type' => session('tenant_type'),
        'client_id' => session('client_id'),
        'database' => session('database_name'),
        'user' => $user,
        'auth_check' => session('authenticated', false),
        'session_data' => session()->all()
    ];
    
    return view('dashboard', compact('tenantInfo'));
});

// Test session setting
Route::get('/test-session', function () {
    session(['test_data' => 'Hello World']);
    session(['authenticated' => true]);
    session(['user' => ['name' => 'Test User', 'email' => 'test@example.com']]);
    session()->save();
    
    return response()->json([
        'message' => 'Session data set',
        'session_data' => session()->all()
    ]);
});

// Test tenant connection
Route::get('/test-tenant-connection', function () {
    try {
        $tenantService = new \App\Services\TenantService();
        $tenantService->setTenantConnection('test_database');
        
        $connection = DB::connection('tenant');
        
        // Test if we can access the collection method
        if (method_exists($connection, 'collection')) {
            return response()->json([
                'success' => true,
                'message' => 'Tenant connection working',
                'connection_class' => get_class($connection),
                'methods' => get_class_methods($connection)
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Collection method not available',
                'connection_class' => get_class($connection),
                'methods' => get_class_methods($connection)
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test optimized authentication flow
Route::get('/test-optimized-auth', function () {
    try {
        $authService = new \App\Services\MultiTenantAuthService(new \App\Services\TenantService());
        
        // Test with a sample email
        $testEmail = 'test@example.com';
        $hashedEmail = hash('sha256', $testEmail);
        
        // Check if there are any clients with this hashed email
        $client = \App\Models\Master\Client::where('hashed_email', $hashedEmail)->first();
        
        return response()->json([
            'success' => true,
            'message' => 'Optimized auth flow test',
            'test_email' => $testEmail,
            'hashed_email' => $hashedEmail,
            'client_found' => $client ? 'yes' : 'no',
            'client_data' => $client ? [
                'client_id' => $client->client_id,
                'name' => $client->name,
                'database_name' => $client->database_name
            ] : null,
            'total_clients' => \App\Models\Master\Client::count(),
            'active_clients' => \App\Models\Master\Client::where('status', 'active')->count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test admin user creation
Route::get('/test-admin-creation', function () {
    try {
        // Check if we have any clients with admin users
        $clients = \App\Models\Master\Client::whereNotNull('hashed_email')->get();
        
        $clientData = [];
        foreach ($clients as $client) {
            // Try to connect to client database and check for admin users
            try {
                $tenantService = new \App\Services\TenantService();
                $tenantService->setTenantConnection($client->database_name);
                
                $adminUsers = \App\Models\Client\User::where('role', 'admin')->get();
                $allUsers = \App\Models\Client\User::all();
                
                $clientData[] = [
                    'client_id' => $client->client_id,
                    'name' => $client->name,
                    'database_name' => $client->database_name,
                    'hashed_email' => $client->hashed_email,
                    'admin_users_count' => $adminUsers->count(),
                    'total_users_count' => $allUsers->count(),
                    'admin_users' => $adminUsers->map(function($user) {
                        return [
                            'email' => $user->email,
                            'name' => $user->name,
                            'role' => $user->role,
                            'created_at' => $user->created_at
                        ];
                    }),
                    'all_users' => $allUsers->map(function($user) {
                        return [
                            'email' => $user->email,
                            'name' => $user->name,
                            'role' => $user->role
                        ];
                    })
                ];
            } catch (\Exception $e) {
                $clientData[] = [
                    'client_id' => $client->client_id,
                    'name' => $client->name,
                    'database_name' => $client->database_name,
                    'error' => 'Could not connect to database: ' . $e->getMessage()
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Admin user creation test - Should show only 1 admin user per tenant',
            'clients_with_hashed_emails' => $clients->count(),
            'client_data' => $clientData
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test authorization for tenant management
Route::get('/test-tenant-auth', function () {
    try {
        $authStatus = [
            'is_authenticated' => session('authenticated', false),
            'tenant_type' => session('tenant_type'),
            'user_role' => session('user')['role'] ?? null,
            'user_name' => session('user')['name'] ?? null,
            'user_email' => session('user')['email'] ?? null,
            'can_access_tenant_management' => false
        ];

        // Check if user can access tenant management
        if ($authStatus['is_authenticated'] && 
            $authStatus['tenant_type'] === 'master' && 
            in_array($authStatus['user_role'], ['admin', 'super_admin'])) {
            $authStatus['can_access_tenant_management'] = true;
        }

        return response()->json([
            'success' => true,
            'message' => 'Authorization test for tenant management',
            'auth_status' => $authStatus,
            'tenant_management_routes' => [
                'onboard' => route('tenant.onboard'),
                'list' => route('tenant.list'),
                'accessible' => $authStatus['can_access_tenant_management']
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Debug session data
Route::get('/debug-session', function () {
    $user = session('user');
    return response()->json([
        'success' => true,
        'message' => 'Current session data',
        'session_data' => [
            'authenticated' => session('authenticated'),
            'tenant_type' => session('tenant_type'),
            'user' => $user,
            'user_id' => session('user_id'),
            'client_id' => session('client_id'),
            'database_name' => session('database_name'),
            'user_role' => $user['role'] ?? 'not_set',
            'user_name' => $user['name'] ?? 'not_set',
            'user_email' => $user['email'] ?? 'not_set',
            'all_session' => session()->all()
        ],
        'authorization_check' => [
            'is_authenticated' => session('authenticated') === true,
            'is_master' => session('tenant_type') === 'master',
            'has_admin_role' => isset($user['role']) && in_array($user['role'], ['admin', 'super_admin']),
            'can_access_tenant_management' => session('authenticated') === true && 
                                            session('tenant_type') === 'master' && 
                                            isset($user['role']) && 
                                            in_array($user['role'], ['admin', 'super_admin'])
        ]
    ]);
});

// Debug master admin users
Route::get('/debug-master-admins', function () {
    try {
        $adminUsers = \App\Models\Master\AdminUser::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Master admin users in database',
            'admin_users' => $adminUsers->map(function($user) {
                return [
                    'id' => $user->getKey(),
                    'email' => $user->email,
                    'name' => $user->name,
                    'role' => $user->role,
                    'created_at' => $user->created_at
                ];
            }),
            'count' => $adminUsers->count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Create a test master admin user
Route::get('/create-test-master-admin', function () {
    try {
        // Check if admin already exists
        $existingAdmin = \App\Models\Master\AdminUser::where('email', 'admin@wms.com')->first();
        
        if ($existingAdmin) {
            return response()->json([
                'success' => true,
                'message' => 'Master admin already exists',
                'admin' => [
                    'email' => $existingAdmin->email,
                    'name' => $existingAdmin->name,
                    'role' => $existingAdmin->role
                ]
            ]);
        }
        
        // Create master admin user
        $admin = \App\Models\Master\AdminUser::create([
            'email' => 'admin@wms.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'name' => 'Master Administrator',
            'role' => 'admin'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Master admin user created successfully',
            'admin' => [
                'id' => $admin->getKey(),
                'email' => $admin->email,
                'name' => $admin->name,
                'role' => $admin->role
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test middleware access
Route::get('/test-middleware-access', function () {
    return response()->json([
        'success' => true,
        'message' => 'Middleware test - if you can see this, middleware is working',
        'session_data' => [
            'authenticated' => session('authenticated'),
            'tenant_type' => session('tenant_type'),
            'user' => session('user')
        ]
    ]);
})->middleware(['web', 'custom_auth', 'master_admin']);

Route::middleware(['web', 'custom_auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
});

// Dashboard - Apply tenant_configured middleware (middleware will skip for master admins)
Route::middleware(['web', 'custom_auth', 'tenant_configured'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Tenant Configuration Routes (Before other tenant routes to avoid middleware conflicts)
Route::middleware(['web', 'custom_auth', 'tenant_admin_only'])->prefix('tenant')->group(function () {
    Route::get('/configure', [App\Http\Controllers\TenantConfigurationController::class, 'show'])->name('tenant.configure');
    Route::post('/configure', [App\Http\Controllers\TenantConfigurationController::class, 'store'])->name('tenant.configure.store');
});

// Tenant User Management Routes (Only accessible by tenant admins) - MUST BE BEFORE tenant management routes
Route::middleware(['web', 'custom_auth', 'tenant_admin_only', 'tenant_configured'])->prefix('tenant')->group(function () {
    
    // User Management
    Route::get('/users', [App\Http\Controllers\TenantUserController::class, 'index'])->name('tenant.users.index');
    Route::get('/users/create', [App\Http\Controllers\TenantUserController::class, 'create'])->name('tenant.users.create');
    Route::post('/users', [App\Http\Controllers\TenantUserController::class, 'store'])->name('tenant.users.store');
    Route::get('/users/{id}', [App\Http\Controllers\TenantUserController::class, 'show'])->name('tenant.users.show');
    Route::get('/users/{id}/edit', [App\Http\Controllers\TenantUserController::class, 'edit'])->name('tenant.users.edit');
    Route::put('/users/{id}', [App\Http\Controllers\TenantUserController::class, 'update'])->name('tenant.users.update');
    Route::delete('/users/{id}', [App\Http\Controllers\TenantUserController::class, 'destroy'])->name('tenant.users.destroy');
    Route::patch('/users/{id}/toggle-status', [App\Http\Controllers\TenantUserController::class, 'toggleStatus'])->name('tenant.users.toggle-status');
    
    // Role Management
    Route::resource('roles', App\Http\Controllers\TenantRoleController::class)->names([
        'index' => 'tenant.roles.index',
        'create' => 'tenant.roles.create',
        'store' => 'tenant.roles.store',
        'show' => 'tenant.roles.show',
        'edit' => 'tenant.roles.edit',
        'update' => 'tenant.roles.update',
        'destroy' => 'tenant.roles.destroy'
    ]);
    Route::get('/roles/{id}/permissions', [App\Http\Controllers\TenantRoleController::class, 'getPermissions'])->name('tenant.roles.permissions')->middleware(['web', 'custom_auth', 'tenant_admin_only']);
    
    // Plant Management  
    Route::resource('plants', App\Http\Controllers\TenantPlantController::class)->names([
        'index' => 'tenant.plants.index',
        'create' => 'tenant.plants.create',
        'store' => 'tenant.plants.store',
        'show' => 'tenant.plants.show',
        'edit' => 'tenant.plants.edit',
        'update' => 'tenant.plants.update',
        'destroy' => 'tenant.plants.destroy'
    ]);
    
    // Warehouse Management
    Route::resource('warehouses', App\Http\Controllers\WarehouseController::class)->names([
        'index' => 'tenant.warehouses.index',
        'create' => 'tenant.warehouses.create',
        'store' => 'tenant.warehouses.store',
        'show' => 'tenant.warehouses.show',
        'edit' => 'tenant.warehouses.edit',
        'update' => 'tenant.warehouses.update',
        'destroy' => 'tenant.warehouses.destroy'
    ]);
    Route::patch('/warehouses/{id}/toggle-status', [App\Http\Controllers\WarehouseController::class, 'toggleStatus'])->name('tenant.warehouses.toggle-status');
    
    // Storage Type Management
    Route::get('/storage-types', [App\Http\Controllers\StorageTypeController::class, 'index'])->name('tenant.storage-types.index');
    Route::get('/storage-types/list', [App\Http\Controllers\StorageTypeController::class, 'list'])->name('tenant.storage-types.list');
    Route::get('/storage-types/sections', [App\Http\Controllers\StorageTypeController::class, 'sections'])->name('tenant.storage-types.sections');
    Route::post('/storage-types', [App\Http\Controllers\StorageTypeController::class, 'store'])->name('tenant.storage-types.store');
    Route::put('/storage-types/{id}', [App\Http\Controllers\StorageTypeController::class, 'update'])->name('tenant.storage-types.update');
    Route::delete('/storage-types/{id}', [App\Http\Controllers\StorageTypeController::class, 'destroy'])->name('tenant.storage-types.destroy');
    
    // Section Management
    Route::get('/sections', [App\Http\Controllers\SectionController::class, 'index'])->name('tenant.sections.index');
    Route::post('/sections', [App\Http\Controllers\SectionController::class, 'store'])->name('tenant.sections.store');
    Route::put('/sections/{id}', [App\Http\Controllers\SectionController::class, 'update'])->name('tenant.sections.update');
    Route::delete('/sections/{id}', [App\Http\Controllers\SectionController::class, 'destroy'])->name('tenant.sections.destroy');
    
    // Bin Management
    Route::get('/bins', [App\Http\Controllers\BinController::class, 'index'])->name('tenant.bins.index');
    Route::get('/bins/create', [App\Http\Controllers\BinController::class, 'create'])->name('tenant.bins.create');
    Route::post('/bins', [App\Http\Controllers\BinController::class, 'store'])->name('tenant.bins.store');
    Route::put('/bins/{id}', [App\Http\Controllers\BinController::class, 'update'])->name('tenant.bins.update');
    Route::delete('/bins/{id}', [App\Http\Controllers\BinController::class, 'destroy'])->name('tenant.bins.destroy');
    Route::post('/bins/bulk-operation', [App\Http\Controllers\BinController::class, 'bulkOperation'])->name('tenant.bins.bulk-operation');
    Route::get('/bins/list', [App\Http\Controllers\BinController::class, 'list'])->name('tenant.bins.list');
    
    // Vendor Management
    Route::resource('vendors', App\Http\Controllers\VendorController::class)->names([
        'index' => 'tenant.vendors.index',
        'create' => 'tenant.vendors.create',
        'store' => 'tenant.vendors.store',
        'show' => 'tenant.vendors.show',
        'edit' => 'tenant.vendors.edit',
        'update' => 'tenant.vendors.update',
        'destroy' => 'tenant.vendors.destroy'
    ]);
    Route::patch('/vendors/{id}/toggle-status', [App\Http\Controllers\VendorController::class, 'toggleStatus'])->name('tenant.vendors.toggle-status');
    
    // Item Category Management
    Route::get('/categories', [App\Http\Controllers\ItemCategoryController::class, 'index'])->name('tenant.categories.index');
    Route::post('/categories', [App\Http\Controllers\ItemCategoryController::class, 'store'])->name('tenant.categories.store');
    Route::put('/categories/{id}', [App\Http\Controllers\ItemCategoryController::class, 'update'])->name('tenant.categories.update');
    Route::delete('/categories/{id}', [App\Http\Controllers\ItemCategoryController::class, 'destroy'])->name('tenant.categories.destroy');
    
    // Item Master Management - Put specific routes BEFORE resource route to avoid conflicts
    Route::get('/items/import', [App\Http\Controllers\ItemController::class, 'showImport'])->name('tenant.items.import');
    Route::post('/items/import', [App\Http\Controllers\ItemController::class, 'import'])->name('tenant.items.import.store');
    Route::get('/items/import/template', [App\Http\Controllers\ItemController::class, 'downloadTemplate'])->name('tenant.items.import.template');
    Route::get('/items/export', [App\Http\Controllers\ItemController::class, 'export'])->name('tenant.items.export');
    
    // Item Location Search - Must be BEFORE resource route
    Route::get('/items/locate', [App\Http\Controllers\ItemLocationController::class, 'locate'])->name('tenant.items.locate');
    Route::get('/items/locate/search', [App\Http\Controllers\ItemLocationController::class, 'search'])->name('tenant.items.locate.search');
    
    // Item resource route
    Route::resource('items', App\Http\Controllers\ItemController::class)->names([
        'index' => 'tenant.items.index',
        'create' => 'tenant.items.create',
        'store' => 'tenant.items.store',
        'show' => 'tenant.items.show',
        'edit' => 'tenant.items.edit',
        'update' => 'tenant.items.update',
        'destroy' => 'tenant.items.destroy'
    ]);
    
    Route::patch('/items/{id}/toggle-status', [App\Http\Controllers\ItemController::class, 'toggleStatus'])->name('tenant.items.toggle-status');
    Route::get('/items/{id}/batches', [App\Http\Controllers\ItemController::class, 'getBatches'])->name('tenant.items.batches');
    
    // Inventory Management
    Route::get('/inventory', [App\Http\Controllers\InventoryController::class, 'index'])->name('tenant.inventory.index');
    Route::get('/inventory/create', [App\Http\Controllers\InventoryController::class, 'create'])->name('tenant.inventory.create');
    Route::post('/inventory', [App\Http\Controllers\InventoryController::class, 'store'])->name('tenant.inventory.store');
    Route::get('/inventory/{id}', [App\Http\Controllers\InventoryController::class, 'show'])->name('tenant.inventory.show');
    Route::get('/inventory/{id}/transfer', [App\Http\Controllers\InventoryController::class, 'transferForm'])->name('tenant.inventory.transfer');
    Route::post('/inventory/{id}/transfer', [App\Http\Controllers\InventoryController::class, 'transfer'])->name('tenant.inventory.transfer.store');
    Route::get('/inventory/{id}/adjust', [App\Http\Controllers\InventoryController::class, 'adjustForm'])->name('tenant.inventory.adjust');
    Route::post('/inventory/{id}/adjust', [App\Http\Controllers\InventoryController::class, 'adjust'])->name('tenant.inventory.adjust.store');
    Route::post('/inventory/{id}/qc-hold', [App\Http\Controllers\InventoryController::class, 'qcHold'])->name('tenant.inventory.qc-hold');
    Route::post('/inventory/{id}/qc-release', [App\Http\Controllers\InventoryController::class, 'qcRelease'])->name('tenant.inventory.qc-release');
    
    // GRN Management
    Route::get('/grns', [App\Http\Controllers\GRNController::class, 'index'])->name('tenant.grns.index');
    Route::get('/grns/create', [App\Http\Controllers\GRNController::class, 'create'])->name('tenant.grns.create');
    Route::post('/grns', [App\Http\Controllers\GRNController::class, 'store'])->name('tenant.grns.store');
    Route::get('/grns/{id}', [App\Http\Controllers\GRNController::class, 'show'])->name('tenant.grns.show');
    Route::post('/grns/{id}/approve', [App\Http\Controllers\GRNController::class, 'approve'])->name('tenant.grns.approve');
    Route::get('/grns/{id}/putaway', [App\Http\Controllers\GRNController::class, 'putaway'])->name('tenant.grns.putaway');
    Route::post('/grns/{id}/putaway', [App\Http\Controllers\GRNController::class, 'executePutaway'])->name('tenant.grns.putaway.store');
    Route::post('/grns/{id}/complete', [App\Http\Controllers\GRNController::class, 'complete'])->name('tenant.grns.complete');
    
    // Batch Management
    Route::get('/batches', [App\Http\Controllers\BatchController::class, 'index'])->name('tenant.batches.index');
    Route::get('/batches/{id}', [App\Http\Controllers\BatchController::class, 'show'])->name('tenant.batches.show');
    
    // Putaway AJAX Routes
    Route::get('/putaway/suggest', [App\Http\Controllers\PutawayController::class, 'suggestBins'])->name('tenant.putaway.suggest');
    Route::post('/putaway/validate', [App\Http\Controllers\PutawayController::class, 'validateBin'])->name('tenant.putaway.validate');
});

// Tenant Management Routes - Only accessible to master admin users
Route::middleware(['web', 'custom_auth', 'master_admin'])->group(function () {
    // Tenant onboarding
    Route::get('/tenant/onboard', [App\Http\Controllers\TenantOnboardingController::class, 'index'])->name('tenant.onboard');
    Route::post('/tenant/onboard', [App\Http\Controllers\TenantOnboardingController::class, 'store'])->name('tenant.store');
    
    // Tenant management
    Route::get('/tenant/list', [App\Http\Controllers\TenantOnboardingController::class, 'list'])->name('tenant.list');
    Route::get('/tenant/{clientId}', [App\Http\Controllers\TenantOnboardingController::class, 'show'])->name('tenant.show');
    Route::get('/tenant/{clientId}/edit', [App\Http\Controllers\TenantOnboardingController::class, 'edit'])->name('tenant.edit');
    Route::put('/tenant/{clientId}', [App\Http\Controllers\TenantOnboardingController::class, 'update'])->name('tenant.update');
    Route::delete('/tenant/{clientId}', [App\Http\Controllers\TenantOnboardingController::class, 'destroy'])->name('tenant.destroy');
});