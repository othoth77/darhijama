<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes du module Admin (core — actif dans le MVP)
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () {
    // Route::get('/', [\Modules\Admin\Http\Controllers\AdminController::class, 'index']);
});
