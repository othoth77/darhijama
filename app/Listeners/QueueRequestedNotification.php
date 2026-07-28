<?php

namespace App\Listeners;

use App\Events\NotificationRequested;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Mythos\Core\Notifications\Contracts\NotificationDispatcher as NotificationService;

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
