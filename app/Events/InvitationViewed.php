<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class InvitationViewed
{
    use Dispatchable;

    public function __construct(
        public readonly int $invitationId,
        public readonly string $visitorHash,
    ) {}
}
