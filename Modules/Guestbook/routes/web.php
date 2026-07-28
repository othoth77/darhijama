<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes du module Guestbook (FUTUR — gaté par le Feature Flag 'guestbook')
|--------------------------------------------------------------------------
| Non chargées tant que le ServiceProvider ne détecte pas le flag actif.
*/

Route::middleware(['web'])->prefix('guestbook')->name('guestbook.')->group(function () {
    // Route::get('/', [\Modules\Guestbook\Http\Controllers\GuestbookController::class, 'index'])->name('index');
});
