<?php

namespace Mythos\Core\Notifications\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Mythos\Core\Notifications\Contracts\NotificationDispatcher;
use Throwable;

class SendNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public function __construct(public readonly int $deliveryId)
    {
        $this->onQueue('notifications');
    }

    public function handle(NotificationDispatcher $notifications): void
    {
        $notifications->deliver($this->deliveryId);
    }

    public function failed(Throwable $exception): void
    {
        app(NotificationDispatcher::class)->markFailed($this->deliveryId, $exception);
    }
}
