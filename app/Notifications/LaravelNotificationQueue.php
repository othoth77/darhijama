<?php

namespace App\Notifications;

use App\Notifications\Contracts\NotificationQueue;
use App\Notifications\Jobs\SendNotification;

class LaravelNotificationQueue implements NotificationQueue
{
    public function dispatch(int $deliveryId): void
    {
        SendNotification::dispatch($deliveryId);
    }
}
