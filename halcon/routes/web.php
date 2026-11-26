<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

Route::view('/', 'track')->name('track');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1') // max. 5 attempts per minute
        ->name('login.attempt');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:'.Role::ADMINISTRATOR)
        ->prefix('dashboard/users')
        ->name('admin.users.')
        ->controller(UserManagementController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('{user}/edit', 'edit')->name('edit')->whereNumber('user');
            Route::put('{user}', 'update')->name('update')->whereNumber('user');
            Route::patch('{user}/toggle-status', 'toggleStatus')->name('toggle')->whereNumber('user');
        });

    Route::get('dashboard/modules/{module}', [ModuleController::class, 'show'])
        ->whereAlpha('module')
        ->name('modules.show');
});
