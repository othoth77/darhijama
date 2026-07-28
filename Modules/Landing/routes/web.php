<?php

use Illuminate\Support\Facades\Route;
use Modules\Landing\Http\Controllers\LandingController;
use Modules\Landing\Http\Controllers\PublicSiteController;

/*
|--------------------------------------------------------------------------
| Routes du module Landing (core — actif dans le MVP)
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('landing.index');
    Route::get('/mentions-legales', [PublicSiteController::class, 'legal'])->name('legal.mentions');
    Route::get('/politique-de-confidentialite', [PublicSiteController::class, 'privacy'])->name('legal.privacy');
    Route::get('/robots.txt', [PublicSiteController::class, 'robots'])->name('robots');
    Route::get('/sitemap.xml', [PublicSiteController::class, 'sitemap'])->name('sitemap');
});
