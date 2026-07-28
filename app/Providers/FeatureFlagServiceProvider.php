<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

/**
 * Déclare chaque Feature Flag futur auprès de Laravel Pennant à partir du
 * registre déclaratif config/features.php, avec sa valeur par défaut.
 *
 * Un module reste ainsi présent dans le code (routes, migrations, back-office)
 * mais invisible côté public/admin tant que son flag n'est pas activé.
 * Voir App\Support\FeatureFlags\Modules pour l'API applicative (Modules::enabled()).
 */
class FeatureFlagServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach (config('features.flags', []) as $key => $definition) {
            Feature::define($key, fn () => (bool) ($definition['default'] ?? false));
        }
    }
}
