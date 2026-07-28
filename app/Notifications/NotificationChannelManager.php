<?php

namespace App\Notifications;

use App\Notifications\Channels\DatabaseNotificationChannel;
use App\Notifications\Channels\DisabledNotificationChannel;
use App\Notifications\Contracts\NotificationChannel;
use InvalidArgumentException;

class NotificationChannelManager
{
    public function channel(string $name): NotificationChannel
    {
        return match ($name) {
            'database' => app(DatabaseNotificationChannel::class),
            'mail', 'whatsapp', 'push' => new DisabledNotificationChannel($name),
            default => throw new InvalidArgumentException("Canal de notification inconnu : {$name}"),
        };
    }
}
