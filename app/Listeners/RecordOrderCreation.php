<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mythos\Core\Analytics\AnalyticsEventType;
use Mythos\Core\Analytics\Contracts\AnalyticsRecorder as AnalyticsService;

class RecordOrderCreation implements ShouldQueue
{
    public bool $afterCommit = true;

    public function __construct(private readonly AnalyticsService $analytics) {}

    public function handle(OrderCreated $event): void
    {
        $this->analytics->record(
            AnalyticsEventType::OrderCreated,
            "order-created|{$event->orderId}",
            'order',
            $event->orderId,
        );
    }
}
