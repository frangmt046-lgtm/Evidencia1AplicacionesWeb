<?php

use Illuminate\Support\Facades\Route;

// Main screen: customers track their order (Vue.js)
Route::view('/', 'track')->name('track');
