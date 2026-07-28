<?php

namespace App\Listeners;

use App\Analytics\AnalyticsService;
use App\Events\WhatsappClicked;

class RecordWhatsappClick
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function handle(WhatsappClicked $event): void
    {
        $this->analytics->recordWhatsappClick(
            $event->source,
            $event->visitorHash,
            $event->context['invitation_id'] ?? null,
            $event->context,
        );
    }
}
