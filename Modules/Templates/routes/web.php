<?php

use Illuminate\Support\Facades\Route;
use Modules\Templates\Http\Controllers\TemplateCatalogController;

/*
|--------------------------------------------------------------------------
| Routes du module Templates (core — actif dans le MVP)
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->prefix('templates')->name('templates.')->group(function () {
    Route::get('/', [TemplateCatalogController::class, 'index'])->name('index');
    Route::get('/{slug}', [TemplateCatalogController::class, 'show'])->name('show');
});
