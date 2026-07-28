<?php

namespace Mythos\Core\Notifications\Channels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Mythos\Core\Notifications\Contracts\NotificationChannel;
use Mythos\Core\Notifications\NotificationMessage;

class DatabaseNotificationChannel implements NotificationChannel
{
    public function send(Model $recipient, NotificationMessage $message): void
    {
        DatabaseNotification::firstOrCreate([
            'id' => substr(hash('sha256', (string) $message->idempotencyKey), 0, 26),
        ], [
            'type' => $message->type,
            'notifiable_type' => $recipient->getMorphClass(),
            'notifiable_id' => $recipient->getKey(),
            'data' => [
                'title' => $message->title,
                'body' => $message->body,
                ...$message->data,
            ],
        ]);
    }
}
