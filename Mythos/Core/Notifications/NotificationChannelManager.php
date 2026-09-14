<?php

namespace Mythos\Core\Notifications;

use InvalidArgumentException;
use Mythos\Core\Notifications\Channels\DatabaseNotificationChannel;
use Mythos\Core\Notifications\Channels\DisabledNotificationChannel;
use Mythos\Core\Notifications\Contracts\NotificationChannel;

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
