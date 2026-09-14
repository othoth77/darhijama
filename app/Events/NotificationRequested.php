<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Mythos\Core\Notifications\NotificationMessage;

class NotificationRequested
{
    use Dispatchable;

    public function __construct(
        public readonly string $recipientType,
        public readonly int|string $recipientId,
        public readonly NotificationMessage $message,
    ) {}
}
