<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes du module RSVP (FUTUR — gaté par le Feature Flag 'rsvp')
|--------------------------------------------------------------------------
| Non chargées tant que le ServiceProvider ne détecte pas le flag actif.
*/

Route::middleware(['web'])->prefix('rsvp')->name('rsvp.')->group(function () {
    // Route::get('/', [\Modules\RSVP\Http\Controllers\RSVPController::class, 'index'])->name('index');
});
