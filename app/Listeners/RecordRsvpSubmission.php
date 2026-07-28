<?php

namespace App\Listeners;

use App\Analytics\AnalyticsEventType;
use App\Analytics\AnalyticsService;
use App\Events\RsvpSubmitted;

class RecordRsvpSubmission
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function handle(RsvpSubmitted $event): void
    {
        $this->analytics->record(
            AnalyticsEventType::RsvpSubmitted,
            "rsvp-submitted|{$event->responseId}",
            'invitation',
            $event->invitationId,
            metadata: ['response_id' => $event->responseId],
        );
    }
}
