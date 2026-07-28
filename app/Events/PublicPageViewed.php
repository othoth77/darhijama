<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class PublicPageViewed
{
    use Dispatchable;

    public function __construct(
        public readonly string $page,
        public readonly string $visitorHash,
        public readonly ?string $subjectType = null,
        public readonly int|string|null $subjectId = null,
    ) {}
}
