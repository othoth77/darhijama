<?php

namespace Applications\DarHijama\Application\Operations;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class OperationalRecoveryProbeJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public readonly string $probeId)
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        if (Cache::add("dar-hijama:recovery-probe:{$this->probeId}", true, now()->addHour())) {
            throw new RuntimeException('Intentional operational recovery probe failure.');
        }

        Cache::put("dar-hijama:recovery-probe:{$this->probeId}:recovered", true, now()->addHour());
    }
}
