<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes du module Orders (core — actif dans le MVP)
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () {
    // Route::get('/', [\Modules\Orders\Http\Controllers\OrdersController::class, 'index']);
});
