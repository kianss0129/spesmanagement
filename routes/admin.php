<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\RoleController;

// =======================
// ADMIN ROUTES
// =======================
Route::middleware(['auth', 'role:Admin|PESO Admin|PESO'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // =====================
        // DASHBOARD & SETTINGS
        // =====================
        Route::get('settings', [AdminController::class, 'settings'])->name('settings');
        Route::get('stats', [AdminController::class, 'stats'])->name('stats');

        // =====================
        // ACTIVITY LOG
        // =====================
        Route::get('activity', [AdminController::class, 'activity'])->name('activity');

        // =====================
        // USER EXPORTS
        // =====================
        Route::get('export-users', [AdminController::class, 'exportUsers'])->name('export.users');

        // =====================
        // ROLE MANAGEMENT
        // =====================
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('roles/assign', [RoleController::class, 'assign'])->name('roles.assign');
        Route::delete('roles/{user}', [RoleController::class, 'remove'])->name('roles.remove');

        // =====================
        // USER MANAGEMENT
        // =====================
        Route::delete('users/{user}', [RoleController::class, 'destroy'])->name('users.destroy');
        Route::get('users/approve/{id}', [UserApprovalController::class, 'approve'])->name('users.approve');
    });
