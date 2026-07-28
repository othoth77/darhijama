<?php

namespace App\Listeners;

use App\Analytics\AnalyticsEventType;
use App\Analytics\AnalyticsService;
use App\Events\OrderCreated;

class RecordOrderCreation
{
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
