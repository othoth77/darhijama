<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes du module Media (core — actif dans le MVP)
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () {
    // Route::get('/', [\Modules\Media\Http\Controllers\MediaController::class, 'index']);
});
