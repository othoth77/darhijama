<?php

return [
    'disk' => env('MEDIA_DISK', env('FILESYSTEM_DISK', 'public')),
    'visibility' => env('MEDIA_VISIBILITY', 'public'),
];
