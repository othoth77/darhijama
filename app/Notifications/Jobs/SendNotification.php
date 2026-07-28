<?php

namespace App\Notifications\Jobs;

use App\Notifications\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
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

    public function handle(NotificationService $notifications): void
    {
        $notifications->deliver($this->deliveryId);
    }

    public function failed(Throwable $exception): void
    {
        app(NotificationService::class)->markFailed($this->deliveryId, $exception);
    }
}
