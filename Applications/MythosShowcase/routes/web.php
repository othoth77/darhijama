<?php

use Applications\MythosShowcase\Http\Controllers\ShowcaseController;
use Illuminate\Support\Facades\Route;

Route::get('/mythos/mythos-showcase/health', fn () => response()->json([
    'application' => 'mythos-showcase',
    'status' => 'ok',
]))->name('mythos.mythos-showcase.health');

Route::get('/mythos/showcase', [ShowcaseController::class, 'index'])
    ->name('mythos.showcase.index');

Route::post('/mythos/showcase', [ShowcaseController::class, 'store'])
    ->name('mythos.showcase.store');
