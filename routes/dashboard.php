<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\PoskoDashboardController;
use App\Http\Controllers\Dashboard\RelawanCheckinController;
use Illuminate\Support\Facades\Route;

// Role-aware dashboard redirect
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
});

// POSKO Dashboard
Route::middleware(['auth', 'role:pcnu', 'scope:pcnu'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {

    Route::get('/posko', [PoskoDashboardController::class, 'index'])->name('posko');
});

// Dashboard API (AJAX polling endpoints)
Route::middleware(['auth', 'throttle:60,1'])
    ->prefix('dashboard/api')
    ->name('api.dashboard.')
    ->group(function () {

    // POSKO API
    Route::middleware(['role:pcnu', 'scope:pcnu'])->prefix('posko')->name('posko.')->group(function () {
        Route::get('summary', [PoskoDashboardController::class, 'summary'])->name('summary');
        Route::get('tugas', [PoskoDashboardController::class, 'tugas'])->name('tugas');
        Route::get('personel', [PoskoDashboardController::class, 'personel'])->name('personel');
    });

    // Check-in/Check-out
    Route::post('checkin', [RelawanCheckinController::class, 'checkin'])->name('checkin');
    Route::post('checkout', [RelawanCheckinController::class, 'checkout'])->name('checkout');
});

// Decision Queue API (shared, role-aware)
Route::middleware(['auth', 'throttle:60,1'])
    ->get('/dashboard/api/decision-queue', function () {
        $service = app(\App\Services\CommandCenter\DecisionQueueService::class);
        $user = auth()->user();
        return response()->json([
            'queue' => $user ? $service->getQueue($user) : [],
            'timestamp' => now()->toIso8601String(),
        ]);
    })->name('api.dashboard.decision-queue');
