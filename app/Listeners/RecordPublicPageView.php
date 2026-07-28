<?php

namespace App\Listeners;

use App\Events\PublicPageViewed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mythos\Core\Analytics\Contracts\AnalyticsRecorder as AnalyticsService;

class RecordPublicPageView implements ShouldQueue
{
    public bool $afterCommit = true;

    public function __construct(private readonly AnalyticsService $analytics) {}

    public function handle(PublicPageViewed $event): void
    {
        $this->analytics->recordPageView(
            $event->page,
            $event->visitorHash,
            $event->subjectType,
            $event->subjectId,
        );
    }
}
