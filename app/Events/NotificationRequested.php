<?php

namespace App\Events;

use App\Notifications\NotificationMessage;
use Illuminate\Foundation\Events\Dispatchable;

class NotificationRequested
{
    use Dispatchable;

    public function __construct(
        public readonly string $recipientType,
        public readonly int|string $recipientId,
        public readonly NotificationMessage $message,
    ) {}
}
