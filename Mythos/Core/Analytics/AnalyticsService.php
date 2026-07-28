<?php

namespace Mythos\Core\Analytics;

use Mythos\Core\Analytics\Contracts\AnalyticsRecorder;
use Mythos\Core\Analytics\Models\AnalyticsEvent;
use Mythos\Core\Analytics\Models\PageView;
use Mythos\Core\Analytics\Models\WhatsappClickEvent;

class AnalyticsService implements AnalyticsRecorder
{
    public function recordWhatsappClick(
        string $source,
        string $visitorHash,
        ?int $invitationId = null,
        array $metadata = [],
    ): WhatsappClickEvent {
        $hash = hash('sha256', implode('|', [$source, $visitorHash, now()->format('Y-m-d-H-i')]));

        WhatsappClickEvent::query()->insertOrIgnore([
            'source' => $source,
            'invitation_id' => $invitationId,
            'visitor_hash' => $visitorHash,
            'deduplication_key' => $hash,
            'metadata' => $metadata ? json_encode($metadata, JSON_THROW_ON_ERROR) : null,
            'occurred_at' => now(),
        ]);

        return WhatsappClickEvent::where('deduplication_key', $hash)->firstOrFail();
    }

    public function recordPageView(
        string $source,
        string $visitorHash,
        ?string $subjectType = null,
        int|string|null $subjectId = null,
    ): PageView {
        $hash = hash('sha256', implode('|', [
            $source,
            $subjectType,
            $subjectId,
            $visitorHash,
            now()->format('Y-m-d-H'),
        ]));

        PageView::query()->insertOrIgnore([
            'source' => $source,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId === null ? null : (string) $subjectId,
            'visitor_hash' => $visitorHash,
            'deduplication_key' => $hash,
            'occurred_at' => now(),
        ]);

        return PageView::where('deduplication_key', $hash)->firstOrFail();
    }

    public function record(
        AnalyticsEventType $type,
        string $deduplicationKey,
        ?string $subjectType = null,
        int|string|null $subjectId = null,
        ?string $source = null,
        ?string $visitorHash = null,
        array $metadata = [],
    ): AnalyticsEvent {
        $hash = hash('sha256', $deduplicationKey);

        AnalyticsEvent::query()->insertOrIgnore([
            'type' => $type->value,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId === null ? null : (string) $subjectId,
            'source' => $source,
            'visitor_hash' => $visitorHash,
            'deduplication_key' => $hash,
            'metadata' => $metadata ? json_encode($metadata, JSON_THROW_ON_ERROR) : null,
            'occurred_at' => now(),
        ]);

        return AnalyticsEvent::where('deduplication_key', $hash)->firstOrFail();
    }
}
