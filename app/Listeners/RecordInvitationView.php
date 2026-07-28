<?php

namespace App\Listeners;

use App\Events\InvitationViewed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mythos\Core\Analytics\AnalyticsEventType;
use Mythos\Core\Analytics\Contracts\AnalyticsRecorder as AnalyticsService;

class RecordInvitationView implements ShouldQueue
{
    public bool $afterCommit = true;

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
