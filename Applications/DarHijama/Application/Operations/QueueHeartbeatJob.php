<?php

namespace Applications\DarHijama\Application\Operations;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class QueueHeartbeatJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(): void
    {
        Cache::put('dar-hijama:health:queue-worker', now()->toIso8601String(), now()->addMinutes(10));
    }
}
