<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes du module Singles (FUTUR — gaté par le Feature Flag 'singles_corner')
|--------------------------------------------------------------------------
| Non chargées tant que le ServiceProvider ne détecte pas le flag actif.
*/

Route::middleware(['web'])->prefix('singles')->name('singles.')->group(function () {
    // Route::get('/', [\Modules\Singles\Http\Controllers\SinglesController::class, 'index'])->name('index');
});
