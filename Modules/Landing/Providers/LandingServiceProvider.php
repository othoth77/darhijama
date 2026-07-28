<?php

namespace Modules\Landing\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Module core (MVP) — toujours actif, aucun Feature Flag requis.
 * Page d'accueil, argumentaire et CTA WhatsApp.
 */
class LandingServiceProvider extends ServiceProvider
{
    protected string $name = 'Landing';

    protected string $nameLower = 'landing';

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));
        $this->loadViewsFrom(module_path($this->name, 'resources/views'), $this->nameLower);
        $this->loadRoutesFrom(module_path($this->name, 'routes/web.php'));

        $this->registerRepositoryBindings();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->name, 'config/config.php'), $this->nameLower);
    }

    /**
     * Liaison des interfaces de Repository vers leur implémentation Eloquent
     * (Dependency Inversion — voir Repositories/Contracts).
     */
    protected function registerRepositoryBindings(): void
    {
        // Exemple :
        // $this->app->bind(
        //     \Modules\Landing\Repositories\Contracts\LandingRepositoryInterface::class,
        //     \Modules\Landing\Repositories\EloquentLandingRepository::class
        // );
    }
}
