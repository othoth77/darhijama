<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes du module Notifications (FUTUR — gaté par le Feature Flag 'notifications')
|--------------------------------------------------------------------------
| Non chargées tant que le ServiceProvider ne détecte pas le flag actif.
*/

Route::middleware(['web'])->prefix('notifications')->name('notifications.')->group(function () {
    // Route::get('/', [\Modules\Notifications\Http\Controllers\NotificationsController::class, 'index'])->name('index');
});
