<?php

// File: routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;

// Public routes (no license check)
Route::get('/', function () {
    return view('welcome');
});

Route::get('/license-expired', function () {
    return view('license-expired');
})->name('license.expired');

// Protected routes (require valid license)
Route::middleware(['license'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::get('/users', [UserController::class, 'index'])->name('users');
});

// Alternative: Apply to specific routes
Route::get('/admin', function () {
    return view('admin.dashboard');
})->middleware('license');
