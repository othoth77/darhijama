<?php

namespace Modules\Media\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Invitations\Models\Invitation;
use Modules\Templates\Models\Template;
use Mythos\Core\Media\Models\Media;
use Mythos\Core\Media\Policies\MediaPolicy;

/**
 * Module core (MVP) — toujours actif, aucun Feature Flag requis.
 * Upload, optimisation et stockage S3 des medias (infrastructure active des le MVP).
 */
class MediaServiceProvider extends ServiceProvider
{
    protected string $name = 'Media';

    protected string $nameLower = 'media';

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));
        $this->loadViewsFrom(module_path($this->name, 'resources/views'), $this->nameLower);
        $this->loadRoutesFrom(module_path($this->name, 'routes/web.php'));

        // Alias stables en base pour mediable_type — jamais le FQCN brut
        // (voir PHASE_1.md §1, table media). morphMap() (et non enforceMorphMap())
        // délibérément : la version "enforce" impose l'alias à TOUTE relation
        // polymorphe de l'application, y compris celles de spatie/laravel-permission
        // (model_has_roles sur Mythos\Core\Identity\Models\User) qui n'ont pas à connaître notre
        // convention — voir correctif VPS du 2026-07-19.
        Relation::morphMap([
            'template' => Template::class,
            'invitation' => Invitation::class,
        ]);

        Gate::policy(Media::class, MediaPolicy::class);

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
        //     \Modules\Media\Repositories\Contracts\MediaRepositoryInterface::class,
        //     \Modules\Media\Repositories\EloquentMediaRepository::class
        // );
    }
}
