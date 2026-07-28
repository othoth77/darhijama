<?php

namespace App\Notifications\Contracts;

interface NotificationQueue
{
    public function dispatch(int $deliveryId): void;
}
