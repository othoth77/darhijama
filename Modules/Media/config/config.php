<?php

return [
    'name' => 'Media',

    // Disque unique utilisé par les uploads applicatifs. Compatible avec les
    // drivers locaux Laravel et tout stockage S3-compatible.
    'disk' => env('MEDIA_DISK', env('FILESYSTEM_DISK', 'public')),

    'visibility' => env('MEDIA_VISIBILITY', 'public'),

    'validation' => [
        'profiles' => [
            'file' => ['required', 'file', 'max:102400'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'audio' => ['required', 'file', 'mimetypes:audio/mpeg,audio/mp4,audio/ogg,audio/wav', 'max:20480'],
            'video' => ['required', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:102400'],
        ],
    ],
];
