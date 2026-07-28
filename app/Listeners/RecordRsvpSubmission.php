<?php

namespace App\Listeners;

use App\Events\RsvpSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mythos\Core\Analytics\AnalyticsEventType;
use Mythos\Core\Analytics\Contracts\AnalyticsRecorder as AnalyticsService;

class RecordRsvpSubmission implements ShouldQueue
{
    public bool $afterCommit = true;

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
