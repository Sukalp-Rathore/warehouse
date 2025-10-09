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

Route::middleware(['web', 'custom_auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});