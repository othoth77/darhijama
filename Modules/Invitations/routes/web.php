<?php

use Illuminate\Support\Facades\Route;
use Modules\Invitations\Http\Controllers\InvitationPublicController;

/*
|--------------------------------------------------------------------------
| Routes du module Invitations (core — actif dans le MVP)
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])->group(function () {
    // Route::get('/', [\Modules\Invitations\Http\Controllers\InvitationsController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| Page publique d'invitation (Phase 3)
|--------------------------------------------------------------------------
| Aucune authentification (pas de comptes clients dans le MVP). Accessible
| à quiconque possède le lien https://notrejour.tn/i/{public_token}. Le
| contrôleur filtre lui-même les invitations non publiées (404).
*/

Route::middleware(['web'])->prefix('i')->name('invitations.public.')->group(function () {
    Route::get('/{token}', [InvitationPublicController::class, 'show'])->name('show');
    Route::post('/{token}/rsvp', [InvitationPublicController::class, 'rsvp'])->middleware('throttle:rsvp')->name('rsvp');
    Route::get('/{token}/qr', [InvitationPublicController::class, 'qrCode'])->name('qr');
});

Route::get('/admin/invitations/preview/{token}', [InvitationPublicController::class, 'preview'])
    ->middleware(['web', 'auth', 'signed'])
    ->name('invitations.preview');
