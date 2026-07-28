<?php

namespace App\Listeners;

use App\Analytics\AnalyticsService;
use App\Events\PublicPageViewed;

class RecordPublicPageView
{
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
