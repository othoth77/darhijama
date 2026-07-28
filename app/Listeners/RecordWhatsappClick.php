<?php

namespace App\Listeners;

use App\Events\WhatsappClicked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mythos\Core\Analytics\Contracts\AnalyticsRecorder as AnalyticsService;

class RecordWhatsappClick implements ShouldQueue
{
    public bool $afterCommit = true;

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
