<?php

namespace Mythos\Core\Notifications\Channels;

use Illuminate\Database\Eloquent\Model;
use Mythos\Core\Notifications\Contracts\NotificationChannel;
use Mythos\Core\Notifications\NotificationMessage;
use RuntimeException;

class DisabledNotificationChannel implements NotificationChannel
{
    public function __construct(private readonly string $name) {}

    public function send(Model $recipient, NotificationMessage $message): void
    {
        throw new RuntimeException("Le canal de notification \"{$this->name}\" n'est pas activé.");
    }
}
