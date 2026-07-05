<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;

// === Redirect kompatibilitas — rute /register lama → /daftar ===
Route::permanentRedirect('/register', '/daftar');

// === GUEST: Login & Registrasi ===
Route::middleware('guest')->group(function () {
    // Login
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:login');

    // Registrasi multi-step
    Route::get('daftar',          [RegistrationController::class, 'pilihJenis'])->name('register');
    Route::get('daftar/{jenis}',  [RegistrationController::class, 'form'])->name('register.form');
    Route::post('daftar/{jenis}', [RegistrationController::class, 'proses'])->name('register.proses');
    Route::get('daftar/menunggu', [RegistrationController::class, 'menunggu'])->name('register.menunggu');
});

// === AUTH: Logout, Konfirmasi Sandi, Update Sandi ===
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
});

// === APPROVAL QUEUE (role-based) ===
Route::middleware(['auth', 'role:super_admin,pwnu,pcnu'])
    ->prefix('admin/approval')
    ->name('admin.approval.')
    ->group(function () {
        Route::get('/',                      [ApprovalController::class, 'index'])->name('index');
        Route::patch('{calon}/setujui',      [ApprovalController::class, 'setujui'])->name('setujui');
        Route::patch('{calon}/tolak',        [ApprovalController::class, 'tolak'])->name('tolak');
    });
