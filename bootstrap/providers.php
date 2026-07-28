<?php

use App\Providers\AnalyticsServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\AuditNotificationServiceProvider;
use App\Providers\FeatureFlagServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use Nwidart\Modules\LaravelModulesServiceProvider;

return [
    AppServiceProvider::class,
    AnalyticsServiceProvider::class,
    AuditNotificationServiceProvider::class,
    FeatureFlagServiceProvider::class,
    AdminPanelProvider::class,

    // Enregistrement explicite plutôt que de dépendre uniquement de la
    // package discovery (voir AUDIT_PHASE_0.md correctif D) : nwidart/laravel-modules
    // charge ensuite lui-même le ServiceProvider de chaque module actif
    // (déclaré dans Modules/{Nom}/module.json) à partir de modules_statuses.json.
    LaravelModulesServiceProvider::class,
];
