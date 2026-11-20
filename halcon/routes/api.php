<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;


Route::get('track', [TrackingController::class, 'show']);

// Users (employees)
Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])->whereNumber('user');
Route::patch('users/{user}/activate', [UserController::class, 'activate'])->whereNumber('user');
Route::apiResource('users', UserController::class)->whereNumber('user');

// Orders
Route::get('orders/archived', [OrderController::class, 'archived']);
Route::get('orders/invoice/{invoice}', [OrderController::class, 'showByInvoice']);
Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->whereNumber('order');
Route::patch('orders/{order}/restore', [OrderController::class, 'restore'])->whereNumber('order');
Route::apiResource('orders', OrderController::class)->whereNumber('order');

// Customers (support for creating orders)
Route::apiResource('customers', CustomerController::class)
    ->only(['index', 'store', 'show'])
    ->whereNumber('customer');
