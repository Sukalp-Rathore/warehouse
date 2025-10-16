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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
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