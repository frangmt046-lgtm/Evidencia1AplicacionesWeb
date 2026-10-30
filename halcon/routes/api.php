<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halcón REST API (prefix /api)
|--------------------------------------------------------------------------
| No authentication is required for this evidence.
| Send the header "Accept: application/json" in every request.
*/

// Users (employees)
Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])->whereNumber('user');
Route::patch('users/{user}/activate', [UserController::class, 'activate'])->whereNumber('user');
Route::apiResource('users', UserController::class)->whereNumber('user');

// Orders
// The fixed routes go before {order} so "archived" and "invoice" are not read as an id.
Route::get('orders/archived', [OrderController::class, 'archived']);
Route::get('orders/invoice/{invoice}', [OrderController::class, 'showByInvoice']);
Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->whereNumber('order');
Route::patch('orders/{order}/restore', [OrderController::class, 'restore'])->whereNumber('order');
Route::apiResource('orders', OrderController::class)->whereNumber('order');

// Customers (support for creating orders)
Route::apiResource('customers', CustomerController::class)
    ->only(['index', 'store', 'show'])
    ->whereNumber('customer');
