<?php

namespace App\Listeners;

use App\Analytics\AnalyticsEventType;
use App\Analytics\AnalyticsService;
use App\Events\InvitationPublished;

class RecordInvitationPublication
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function handle(InvitationPublished $event): void
    {
        $this->analytics->record(
            AnalyticsEventType::InvitationPublished,
            "invitation-published|{$event->invitationId}",
            'invitation',
            $event->invitationId,
        );
    }
}
