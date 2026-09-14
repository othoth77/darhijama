<?php

use Illuminate\Support\Env;

return [
    'name' => 'Dar Hijama',
    'slug' => 'dar-hijama',
    'public_hosts' => [
        'primary' => Env::get('DAR_HIJAMA_HOST', 'darhijama.tn'),
        'www' => Env::get('DAR_HIJAMA_WWW_HOST', 'www.darhijama.tn'),
    ],
    'notifications' => [
        'channels' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) Env::get('DAR_HIJAMA_NOTIFICATION_CHANNELS', 'database')),
        ))),
    ],
];
