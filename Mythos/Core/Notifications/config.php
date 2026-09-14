<?php

return [
    'queue' => env('MYTHOS_NOTIFICATIONS_QUEUE', 'notifications'),
    'tries' => 3,
];
