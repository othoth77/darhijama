<?php

namespace Modules\Singles\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

/**
 * Module FUTUR — présent dans le code mais désactivé par défaut.
 * Module hors mariage, perimetre a definir ulterieurement. Structure vide uniquement.
 *
 * Tant que le Feature Flag 'singles_corner' n'est pas actif (config/features.php,
 * pilotable depuis le back-office), aucune route ni vue de ce module n'est
 * exposée. Les migrations restent chargées pour permettre de préparer le
 * schéma en base à l'avance sans exposer de fonctionnalité.
 */
class SinglesServiceProvider extends ServiceProvider
{
    protected string $name = 'Singles';

    protected string $nameLower = 'singles';

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));

        if (! $this->moduleIsActive()) {
            return;
        }

        $this->loadViewsFrom(module_path($this->name, 'resources/views'), $this->nameLower);
        $this->loadRoutesFrom(module_path($this->name, 'routes/web.php'));
    }

    public function register(): void
    {
        $this->mergeConfigFrom(module_path($this->name, 'config/config.php'), $this->nameLower);
    }

    protected function moduleIsActive(): bool
    {
        return Feature::active('singles_corner');
    }
}
