<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes du module Api (FUTUR — gaté par le Feature Flag 'public_api')
|--------------------------------------------------------------------------
| Non chargées tant que le ServiceProvider ne détecte pas le flag actif.
*/

Route::middleware(['web'])->prefix('api')->name('api.')->group(function () {
    // Route::get('/', [\Modules\Api\Http\Controllers\ApiController::class, 'index'])->name('index');
});
