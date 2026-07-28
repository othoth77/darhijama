<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class WhatsappClicked
{
    use Dispatchable;

    public function __construct(
        public readonly string $source,
        public readonly string $visitorHash,
        public readonly array $context = [],
    ) {}
}
