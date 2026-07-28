<?php

namespace Modules\AI\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

/**
 * Module FUTUR — présent dans le code mais désactivé par défaut.
 * Generation et retouche assistees par IA (Album IA).
 *
 * Tant que le Feature Flag 'ai_album' n'est pas actif (config/features.php,
 * pilotable depuis le back-office), aucune route ni vue de ce module n'est
 * exposée. Les migrations restent chargées pour permettre de préparer le
 * schéma en base à l'avance sans exposer de fonctionnalité.
 */
class AIServiceProvider extends ServiceProvider
{
    protected string $name = 'AI';

    protected string $nameLower = 'ai';

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
        return Feature::active('ai_album');
    }
}
