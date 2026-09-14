<?php

namespace Mythos\Core\Notifications;

use Mythos\Core\Notifications\Contracts\NotificationQueue;
use Mythos\Core\Notifications\Jobs\SendNotification;

class LaravelNotificationQueue implements NotificationQueue
{
    public function dispatch(int $deliveryId): void
    {
        SendNotification::dispatch($deliveryId);
    }
}
