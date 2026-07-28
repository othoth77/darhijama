<?php

namespace Mythos\Core\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Mythos\Core\Analytics\AnalyticsService;
use Mythos\Core\Analytics\Contracts\AnalyticsRecorder;
use Mythos\Core\Applications\Capabilities\CoreCapabilityCatalog;
use Mythos\Core\Applications\Console\InspectApplicationCommand;
use Mythos\Core\Applications\Console\ListApplicationsCommand;
use Mythos\Core\Applications\Console\MakeApplicationCommand;
use Mythos\Core\Applications\Console\ValidateApplicationCommand;
use Mythos\Core\Applications\Contracts\ApplicationRegistry;
use Mythos\Core\Applications\Manifest\ManifestValidator;
use Mythos\Core\Applications\Registry\MythosApplicationRegistry;
use Mythos\Core\Audit\AuditService;
use Mythos\Core\Audit\Contracts\AuditLogger;
use Mythos\Core\Media\Contracts\MediaManager;
use Mythos\Core\Media\Services\MediaService;
use Mythos\Core\Notifications\Contracts\NotificationDispatcher;
use Mythos\Core\Notifications\NotificationService;
use Mythos\Core\PublicLinks\Contracts\PublicLinkGenerator;
use Mythos\Core\PublicLinks\PublicLinkService;
use Mythos\Core\QrCode\Contracts\QrCodeGenerator;
use Mythos\Core\QrCode\QrCodeService;
use Mythos\Core\Support\Deprecation\DeprecationsCommand;

class MythosCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Analytics/config.php', 'mythos.analytics');
        $this->mergeConfigFrom(__DIR__.'/../Audit/config.php', 'mythos.audit');
        $this->mergeConfigFrom(__DIR__.'/../Identity/config.php', 'mythos.identity');
        $this->mergeConfigFrom(__DIR__.'/../Media/config.php', 'mythos.media');
        $this->mergeConfigFrom(__DIR__.'/../Notifications/config.php', 'mythos.notifications');
        $this->mergeConfigFrom(__DIR__.'/../PublicLinks/config.php', 'mythos.public-links');
        $this->mergeConfigFrom(__DIR__.'/../QrCode/config.php', 'mythos.qr-code');
        $this->mergeConfigFrom(__DIR__.'/../Security/config.php', 'mythos.security');
        $this->mergeConfigFrom(__DIR__.'/../UI/config.php', 'mythos.ui');

        $this->app->singleton(AnalyticsRecorder::class, AnalyticsService::class);
        $this->app->singleton(AuditLogger::class, AuditService::class);
        $this->app->singleton(MediaManager::class, MediaService::class);
        $this->app->singleton(NotificationDispatcher::class, NotificationService::class);
        $this->app->singleton(PublicLinkGenerator::class, PublicLinkService::class);
        $this->app->singleton(QrCodeGenerator::class, QrCodeService::class);
        $this->app->singleton(CoreCapabilityCatalog::class);
        $this->app->singleton(ManifestValidator::class);
        $this->app->singleton(ApplicationRegistry::class, MythosApplicationRegistry::class);

        $this->app->make(ApplicationRegistry::class)->discover(
            config('mythos.applications', []),
        );
    }

    public function boot(): void
    {
        $this->app->make(ApplicationRegistry::class)->boot();

        Blade::anonymousComponentPath(
            __DIR__.'/../UI/resources/views/components',
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeApplicationCommand::class,
                ListApplicationsCommand::class,
                InspectApplicationCommand::class,
                ValidateApplicationCommand::class,
                DeprecationsCommand::class,
            ]);
        }
    }
}
