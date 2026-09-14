<?php

namespace App\Listeners;

use App\Events\InvitationPublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mythos\Core\Analytics\AnalyticsEventType;
use Mythos\Core\Analytics\Contracts\AnalyticsRecorder as AnalyticsService;

class RecordInvitationPublication implements ShouldQueue
{
    public bool $afterCommit = true;

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
