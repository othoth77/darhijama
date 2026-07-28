<?php

namespace App\Listeners;

use App\Events\NotificationRequested;
use App\Notifications\NotificationService;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class QueueRequestedNotification
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function handle(NotificationRequested $event): void
    {
        if (! is_a($event->recipientType, Model::class, true)) {
            throw new InvalidArgumentException('Le destinataire doit être un modèle Eloquent.');
        }

        $recipient = $event->recipientType::findOrFail($event->recipientId);

        $this->notifications->send($recipient, $event->message);
    }
}
