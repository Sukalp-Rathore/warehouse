<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\InventoryController;

// Auth Routes 
Route::middleware(['web'])->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

Route::middleware(['web' ,'auth'])->group(function () {
    Route::get('/', function () {
        return view('dashboard');
    });
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    // Patient Routes 
    Route::get('/patients', [PatientController::class, 'index'])->name('patients');
    Route::post('/patients/create', [PatientController::class, 'create'])->name('patient.create');
    Route::post('/patient-edit', [PatientController::class, 'edit'])->name('patient.show.edit');
    Route::post('/patient-update', [PatientController::class, 'update'])->name('patient.update');
    Route::post('/patient-delete', [PatientController::class, 'delete'])->name('patient.delete');
    // Visit Routes
    Route::get('/visits', [VisitController::class, 'index'])->name('visits');
    Route::post('/visits-create', [VisitController::class, 'create'])->name('visit.create');
    Route::post('/visit-edit', [VisitController::class, 'edit'])->name('visit.show.edit');
    Route::post('/visit-delete', [VisitController::class, 'delete'])->name('visit.delete');
    // Appointment Routes
    Route::get('/appointments', [AppointmentController::class, 'show'])->name('appointments');
    Route::get('/create-appointment' , [AppointmentController::class, 'index'])->name('create.appointment');
    Route::post('/create-appointment' , [AppointmentController::class, 'create'])->name('create.appointment');
    // Inventory Routes 
    Route::get('/inventory', [InventoryController::class, 'inventory'])->name('inventory');
    Route::post('/add-inventory', [InventoryController::class, 'createInventory'])->name('inventory.create');
    Route::post('/inventory-show-image', [InventoryController::class, 'showImage'])->name('inventory.show.image');
    Route::post('/show-edit-inventory', [InventoryController::class, 'showEditInventory'])->name('inventory.show.edit');
    Route::post('/update-inventory', [InventoryController::class, 'updateInventory'])->name('inventory.update');
    Route::post('/delete-inventory', [InventoryController::class, 'deleteInventory'])->name('inventory.delete');
    Route::post('/fetch-stock', [InventoryController::class, 'fetchStock'])->name('inventory.fetch.stock');
    Route::post('/update-stock', [InventoryController::class, 'updateStock'])->name('inventory.update.stock');
});