<?php

namespace App\Listeners;

use App\Analytics\AnalyticsEventType;
use App\Analytics\AnalyticsService;
use App\Events\InvitationViewed;

class RecordInvitationView
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function handle(InvitationViewed $event): void
    {
        $this->analytics->record(
            AnalyticsEventType::InvitationView,
            implode('|', [$event->invitationId, $event->visitorHash, now()->format('Y-m-d-H')]),
            'invitation',
            $event->invitationId,
            visitorHash: $event->visitorHash,
        );
    }
}
