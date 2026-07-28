<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Store des Feature Flags par défaut
    |--------------------------------------------------------------------------
    | 'database' : persistance réelle en base (table `features`), pilotable
    | depuis le back-office sans redéploiement — c'est le comportement attendu
    | par PENNANT_STORE=database dans .env.example.
    | Sans ce fichier, Pennant retombe silencieusement sur le store 'array'
    | (en mémoire, non persistant) — voir AUDIT_PHASE_0.md correctif B.
    */
    'default' => env('PENNANT_STORE', 'database'),

    'stores' => [

        'array' => [
            'driver' => 'array',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => null,
            'table' => 'features',
        ],

    ],

];
