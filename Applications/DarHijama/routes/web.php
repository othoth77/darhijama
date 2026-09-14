<?php

use Applications\DarHijama\Http\Controllers\AppointmentController;
use Applications\DarHijama\Http\Controllers\ArticleController;
use Applications\DarHijama\Http\Controllers\DashboardController;
use Applications\DarHijama\Http\Controllers\HealthController;
use Applications\DarHijama\Http\Controllers\PatientController;
use Applications\DarHijama\Http\Controllers\PractitionerAvailabilityController;
use Applications\DarHijama\Http\Controllers\PractitionerController;
use Applications\DarHijama\Http\Controllers\PublicHomeController;
use Applications\DarHijama\Http\Controllers\SeoController;
use Applications\DarHijama\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;

Route::domain(config('applications.dar-hijama.public_hosts.primary'))
    ->get('/', PublicHomeController::class)
    ->name('dar-hijama.public.home');

Route::domain(config('applications.dar-hijama.public_hosts.www'))
    ->get('/', PublicHomeController::class)
    ->name('dar-hijama.public.home.www');

// Domain-scoped so these never collide with Modules/Landing's shared
// /sitemap.xml and /robots.txt (used by other applications on this same
// codebase, e.g. Notre Jour) — see SeoController's docblock.
Route::domain(config('applications.dar-hijama.public_hosts.primary'))->group(function (): void {
    Route::get('/articles', [ArticleController::class, 'index'])
        ->name('dar-hijama.articles.index');
    Route::get('/articles/category/{category:slug}', [ArticleController::class, 'category'])
        ->name('dar-hijama.articles.category');
    Route::get('/articles/{slug}', [ArticleController::class, 'show'])
        ->name('dar-hijama.articles.show');

    Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])
        ->name('dar-hijama.sitemap');
    Route::get('/robots.txt', [SeoController::class, 'robots'])
        ->name('dar-hijama.robots');
});

Route::get('/mythos/dar-hijama/health', [HealthController::class, 'live'])
    ->name('mythos.dar-hijama.health');
Route::get('/mythos/dar-hijama/ready', [HealthController::class, 'ready'])
    ->name('mythos.dar-hijama.ready');

Route::middleware(['auth', PermissionMiddleware::using('dar-hijama.access')])
    ->prefix('dar-hijama')
    ->name('dar-hijama.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('operations/diagnostics', [HealthController::class, 'diagnostics'])
            ->middleware(PermissionMiddleware::using('dar-hijama.settings.manage'))
            ->name('operations.diagnostics');
        Route::get('patients', [PatientController::class, 'index'])
            ->middleware(PermissionMiddleware::using('dar-hijama.patients.view'))
            ->name('patients.index');
        Route::post('patients', [PatientController::class, 'store'])
            ->middleware(PermissionMiddleware::using('dar-hijama.patients.create'))
            ->name('patients.store');
        Route::put('patients/{patient}', [PatientController::class, 'update'])
            ->middleware(PermissionMiddleware::using('dar-hijama.patients.update'))
            ->name('patients.update');
        Route::delete('patients/{patient}', [PatientController::class, 'destroy'])
            ->middleware(PermissionMiddleware::using('dar-hijama.patients.update'))
            ->name('patients.destroy');
        Route::apiResource('practitioners', PractitionerController::class)
            ->except('show')
            ->middleware(PermissionMiddleware::using('dar-hijama.practitioners.manage'));
        Route::get('appointments', [AppointmentController::class, 'index'])
            ->middleware(PermissionMiddleware::using('dar-hijama.appointments.view'))
            ->name('appointments.index');
        Route::post('appointments', [AppointmentController::class, 'store'])
            ->middleware(PermissionMiddleware::using('dar-hijama.appointments.create'))
            ->name('appointments.store');
        Route::put('appointments/{appointment}', [AppointmentController::class, 'update'])
            ->middleware(PermissionMiddleware::using('dar-hijama.appointments.update'))
            ->name('appointments.update');
        Route::get('practitioners/{practitioner}/availability', [PractitionerAvailabilityController::class, 'show'])
            ->middleware(PermissionMiddleware::using('dar-hijama.appointments.view'))
            ->name('practitioners.availability.show');
        Route::put('practitioners/{practitioner}/availability', [PractitionerAvailabilityController::class, 'update'])
            ->middleware(PermissionMiddleware::using('dar-hijama.practitioner-availability.manage'))
            ->name('practitioners.availability.update');
        Route::post('appointments/{appointment}/confirm', [AppointmentController::class, 'confirm'])
            ->middleware(PermissionMiddleware::using('dar-hijama.appointments.confirm'))
            ->name('appointments.confirm');
        Route::post('appointments/{appointment}/assign', [AppointmentController::class, 'assign'])
            ->middleware(PermissionMiddleware::using('dar-hijama.appointments.assign'))
            ->name('appointments.assign');
        Route::post('appointments/{appointment}/transition', [AppointmentController::class, 'transition'])
            ->middleware(PermissionMiddleware::using('dar-hijama.appointments.update'))
            ->name('appointments.transition');
        Route::post('appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])
            ->middleware(PermissionMiddleware::using('dar-hijama.appointments.reschedule'))
            ->name('appointments.reschedule');
        Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
            ->middleware(PermissionMiddleware::using('dar-hijama.appointments.cancel'))
            ->name('appointments.cancel');
        Route::post('appointments/{appointment}/start', [AppointmentController::class, 'start'])
            ->middleware(PermissionMiddleware::using('dar-hijama.appointments.start'))
            ->name('appointments.start');
        Route::post('appointments/{appointment}/complete', [AppointmentController::class, 'complete'])
            ->middleware(PermissionMiddleware::using('dar-hijama.appointments.complete'))
            ->name('appointments.complete');
        Route::post('appointments/{appointment}/follow-up', [AppointmentController::class, 'followUp'])
            ->middleware(PermissionMiddleware::using('dar-hijama.appointments.create'))
            ->name('appointments.follow-up');
        Route::get('settings', [SettingController::class, 'index'])
            ->middleware(PermissionMiddleware::using('dar-hijama.settings.manage'))
            ->name('settings.index');
        Route::put('settings/{key}', [SettingController::class, 'update'])
            ->where('key', '[A-Za-z0-9._-]+')
            ->middleware(PermissionMiddleware::using('dar-hijama.settings.manage'))
            ->name('settings.update');
    });
