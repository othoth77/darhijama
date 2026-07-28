<?php

use Nwidart\Modules\Activators\FileActivator;
use Nwidart\Modules\Providers\ConsoleServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Namespace
    |--------------------------------------------------------------------------
    */
    'namespace' => 'Modules',

    /*
    |--------------------------------------------------------------------------
    | Statuts des modules
    |--------------------------------------------------------------------------
    | Piloté par modules_statuses.json à la racine du projet. Ce fichier
    | ne fait qu'activer le mécanisme de statut ; la vérité applicative
    | fine (module futur visible ou non) reste gérée par Laravel Pennant
    | (config/features.php), pas par ce simple booléen.
    */
    'activators' => [
        'file' => [
            'class' => FileActivator::class,
            'statuses-file' => base_path('modules_statuses.json'),
        ],
    ],
    'activator' => 'file',

    /*
    |--------------------------------------------------------------------------
    | Chemins
    |--------------------------------------------------------------------------
    */
    'paths' => [
        'modules' => base_path('Modules'),
        'assets' => public_path('modules'),
        'migration' => base_path('database/migrations'),

        // Structure "à plat" (sans dossier app/ intermédiaire) — voir AUDIT_PHASE_0.md
        // correctif A : le mapping racine `"Modules\\": "Modules/"` de /composer.json
        // n'est valide que si le namespace Modules\{Nom}\Providers\X correspond
        // exactement à Modules/{Nom}/Providers/X.php, sans segment app/ intercalé.
        'generator' => [
            'module' => ['path' => 'module.json', 'generate' => true],
            'provider' => ['path' => 'Providers', 'generate' => true],
            'route-provider' => ['path' => 'Providers', 'generate' => true],
            'controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'request' => ['path' => 'Http/Requests', 'generate' => true],
            'middleware' => ['path' => 'Http/Middleware', 'generate' => true],
            'model' => ['path' => 'Models', 'generate' => true],
            'services' => ['path' => 'Services', 'generate' => true],
            'repository' => ['path' => 'Repositories', 'generate' => true],
            'policies' => ['path' => 'Policies', 'generate' => true],
            'config' => ['path' => 'config', 'generate' => true],
            'migration' => ['path' => 'Database/Migrations', 'generate' => true],
            'seeder' => ['path' => 'Database/Seeders', 'generate' => true],
            'factory' => ['path' => 'Database/Factories', 'generate' => true],
            'views' => ['path' => 'resources/views', 'generate' => true],
            'lang' => ['path' => 'resources/lang', 'generate' => false],
            'routes' => ['path' => 'routes', 'generate' => true],
            'test' => ['path' => 'Tests/Feature', 'generate' => false],
            'test-unit' => ['path' => 'Tests/Unit', 'generate' => false],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache du scan des modules
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('MODULES_CACHE_ENABLED', false),
        'driver' => 'file',
        'key' => 'laravel-modules',
        'lifetime' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Commandes artisan personnalisées par module
    |--------------------------------------------------------------------------
    | Réservé pour d'éventuelles commandes propres à un module (ex. import de
    | données Templates). Vide au MVP.
    */
    'commands' => ConsoleServiceProvider::defaultCommands()
        ->merge([])
        ->toArray(),

    'register' => [
        'translations' => true,
        'files' => 'register',
    ],

    'scan' => [
        'enabled' => false,
    ],

    'composer' => [
        'vendor' => 'notrejour',
        'author' => [
            'name' => 'Notre Jour',
            'email' => 'contact@notrejour.tn',
        ],
    ],

    'stubs' => [
        'enabled' => true,
        'path' => base_path('.stubs/laravel-modules'),
        'files' => [
            'routes/web' => 'routes/web.php',
            'scaffold/config' => 'config/config.php',
        ],
        'replacements' => [
            'routes/web' => ['MODULE_NAMESPACE'],
        ],
    ],
];
