<?php

namespace Modules\Templates\Providers;

use App\Contracts\Templates\TemplateCatalog;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Templates\Models\Template;
use Modules\Templates\Models\TemplateCategory;
use Modules\Templates\Observers\TemplateObserver;
use Modules\Templates\Policies\TemplateCategoryPolicy;
use Modules\Templates\Policies\TemplatePolicy;
use Modules\Templates\Services\EloquentTemplateCatalog;

/**
 * Module core (MVP) — toujours actif, aucun Feature Flag requis.
 * Galerie de modeles d'invitations et pages de demonstration.
 */
class TemplatesServiceProvider extends ServiceProvider
{
    protected string $name = 'Templates';

    protected string $nameLower = 'templates';

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));
        $this->loadViewsFrom(module_path($this->name, 'resources/views'), $this->nameLower);
        $this->loadRoutesFrom(module_path($this->name, 'routes/web.php'));

        Template::observe(TemplateObserver::class);

        Gate::policy(Template::class, TemplatePolicy::class);
        Gate::policy(TemplateCategory::class, TemplateCategoryPolicy::class);

        $this->registerRepositoryBindings();
    }

    public function register(): void
    {
        $this->app->bind(TemplateCatalog::class, EloquentTemplateCatalog::class);

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
        //     \Modules\Templates\Repositories\Contracts\TemplatesRepositoryInterface::class,
        //     \Modules\Templates\Repositories\EloquentTemplatesRepository::class
        // );
    }
}
