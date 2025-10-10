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

Route::middleware(['web', 'custom_auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Tenant Management Routes
Route::middleware(['web', 'custom_auth'])->group(function () {
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