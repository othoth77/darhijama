<?php

namespace App\Notifications\Channels;

use App\Notifications\Contracts\NotificationChannel;
use App\Notifications\NotificationMessage;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class DisabledNotificationChannel implements NotificationChannel
{
    public function __construct(private readonly string $name) {}

    public function send(Model $recipient, NotificationMessage $message): void
    {
        throw new RuntimeException("Le canal de notification \"{$this->name}\" n'est pas activé.");
    }
}
