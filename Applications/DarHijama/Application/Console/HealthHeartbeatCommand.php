<?php

namespace Applications\DarHijama\Application\Console;

use Applications\DarHijama\Application\Operations\QueueHeartbeatJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class HealthHeartbeatCommand extends Command
{
    protected $signature = 'dar-hijama:health-heartbeat';

    protected $description = 'Record scheduler health and dispatch a queue worker heartbeat';

    public function handle(): int
    {
        Cache::put('dar-hijama:health:scheduler', now()->toIso8601String(), now()->addMinutes(10));
        QueueHeartbeatJob::dispatch();
        $this->components->info('Health heartbeat dispatched.');

        return self::SUCCESS;
    }
}
