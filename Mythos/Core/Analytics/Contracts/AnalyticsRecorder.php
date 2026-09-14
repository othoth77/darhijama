<?php

namespace Mythos\Core\Analytics\Contracts;

use Mythos\Core\Analytics\AnalyticsEventType;
use Mythos\Core\Analytics\Models\AnalyticsEvent;
use Mythos\Core\Analytics\Models\PageView;
use Mythos\Core\Analytics\Models\WhatsappClickEvent;
use Mythos\Core\Support\Api\ApiStatus;
use Mythos\Core\Support\Api\PublicApi;

#[PublicApi(ApiStatus::Stable, since: '1.0.0')]
interface AnalyticsRecorder
{
    public function recordWhatsappClick(
        string $source,
        string $visitorHash,
        ?int $invitationId = null,
        array $metadata = [],
    ): WhatsappClickEvent;

    public function recordPageView(
        string $source,
        string $visitorHash,
        ?string $subjectType = null,
        int|string|null $subjectId = null,
    ): PageView;

    public function record(
        AnalyticsEventType $type,
        string $deduplicationKey,
        ?string $subjectType = null,
        int|string|null $subjectId = null,
        ?string $source = null,
        ?string $visitorHash = null,
        array $metadata = [],
    ): AnalyticsEvent;
}
