<?php

namespace Modules\Guestbook\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

/**
 * Module FUTUR — présent dans le code mais désactivé par défaut.
 * Livre d'or numerique.
 *
 * Tant que le Feature Flag 'guestbook' n'est pas actif (config/features.php,
 * pilotable depuis le back-office), aucune route ni vue de ce module n'est
 * exposée. Les migrations restent chargées pour permettre de préparer le
 * schéma en base à l'avance sans exposer de fonctionnalité.
 */
class GuestbookServiceProvider extends ServiceProvider
{
    protected string $name = 'Guestbook';

    protected string $nameLower = 'guestbook';

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
        return Feature::active('guestbook');
    }
}
