<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes du module AI (FUTUR — gaté par le Feature Flag 'ai_album')
|--------------------------------------------------------------------------
| Non chargées tant que le ServiceProvider ne détecte pas le flag actif.
*/

Route::middleware(['web'])->prefix('ai')->name('ai.')->group(function () {
    // Route::get('/', [\Modules\AI\Http\Controllers\AIController::class, 'index'])->name('index');
});
