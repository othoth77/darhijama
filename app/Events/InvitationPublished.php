<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class InvitationPublished
{
    use Dispatchable;

    public function __construct(public readonly int $invitationId) {}
}
