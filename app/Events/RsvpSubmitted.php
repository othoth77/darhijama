<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class RsvpSubmitted
{
    use Dispatchable;

    public function __construct(
        public readonly int $responseId,
        public readonly int $invitationId,
    ) {}
}
