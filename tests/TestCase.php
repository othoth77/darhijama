<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Garde-fou : interdit l'exécution de tout test si la connexion active ne
     * pointe pas vers une base de données de test explicitement nommée
     * "*_test". Découvert nécessaire suite à un incident de stabilité :
     * `php artisan optimize` (exécuté côté production pour le déploiement)
     * met en cache `bootstrap/cache/config.php`. Or, lorsqu'un cache de
     * configuration existe, Laravel ne relit plus jamais les fichiers
     * `config/*.php` — il désérialise directement le cache — ce qui fait
     * ignorer silencieusement les surcharges `<env>` de phpunit.xml
     * (DB_CONNECTION/DB_DATABASE) et exécute la suite de tests contre la
     * base de PRODUCTION réelle, avec seulement la protection implicite
     * (et fragile) du rollback de transaction de RefreshDatabase.
     *
     * Plutôt que de compter sur une discipline opérationnelle ("ne pas
     * oublier de `config:clear` avant les tests"), ce garde-fou fait
     * échouer bruyamment et immédiatement tout test qui tenterait de
     * s'exécuter dans ces conditions, avant la moindre écriture en base.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if (! str_ends_with((string) $database, '_test')) {
            throw new RuntimeException(
                "Exécution des tests refusée : la connexion \"{$connection}\" pointe vers la base ".
                "\"{$database}\", qui ne se termine pas par \"_test\".\n\n".
                'Cause la plus probable : la configuration a été mise en cache '.
                '(bootstrap/cache/config.php), ce qui fait ignorer les surcharges de phpunit.xml et '.
                "exécuterait les tests contre la base de production réelle.\n\n".
                'Corrigez avec : php artisan config:clear'
            );
        }
    }
}
