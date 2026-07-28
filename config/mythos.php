<?php

return [
    'core_version' => '1.0.0',

    /*
     * Explicit allow-list: production never scans the filesystem for
     * applications. Add a manifest path here only after code review.
     */
    'applications' => [
        base_path('Applications/NotreJour/mythos.json'),
        base_path('Applications/MythosShowcase/mythos.json'),
        base_path('Applications/DarHijama/mythos.json'),
    ],
];
