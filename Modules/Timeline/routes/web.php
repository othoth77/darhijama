<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes du module Timeline (FUTUR — gaté par le Feature Flag 'timeline')
|--------------------------------------------------------------------------
| Non chargées tant que le ServiceProvider ne détecte pas le flag actif.
*/

Route::middleware(['web'])->prefix('timeline')->name('timeline.')->group(function () {
    // Route::get('/', [\Modules\Timeline\Http\Controllers\TimelineController::class, 'index'])->name('index');
});
