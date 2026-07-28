<?php

namespace Applications\DarHijama\Application\Operations;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductionHealthService
{
    public function checks(bool $fresh = false): array
    {
        if (! $fresh) {
            try {
                $cached = Cache::get('dar-hijama:health:readiness-result');
                if (is_array($cached)) {
                    return $cached;
                }
            } catch (Throwable) {
                // The cache check below will report the dependency failure.
            }
        }

        $checks = [
            'application' => true,
            'database' => $this->database(),
            'cache' => $this->cache(),
            'queue_worker' => $this->freshHeartbeat('dar-hijama:health:queue-worker'),
            'scheduler' => $this->freshHeartbeat('dar-hijama:health:scheduler'),
            'private_storage' => $this->storage('local'),
            'public_storage' => $this->storage('public'),
            'environment' => $this->environment(),
            'migrations' => $this->migrations(),
            'disk_space' => $this->diskSpace(),
        ];

        try {
            Cache::put('dar-hijama:health:readiness-result', $checks, 5);
        } catch (Throwable) {
            $checks['cache'] = false;
        }

        return $checks;
    }

    public function ready(): bool
    {
        return ! in_array(false, $this->checks(), true);
    }

    private function database(): bool
    {
        return $this->attempt(fn () => DB::select('select 1'));
    }

    private function cache(): bool
    {
        return $this->attempt(function (): void {
            $key = 'dar-hijama:health:'.bin2hex(random_bytes(8));
            Cache::put($key, 'ok', 10);

            if (Cache::get($key) !== 'ok') {
                throw new \RuntimeException('Cache read/write failed.');
            }

            Cache::forget($key);
        });
    }

    private function storage(string $disk): bool
    {
        return $this->attempt(function () use ($disk): void {
            $path = '.health/'.bin2hex(random_bytes(8));
            Storage::disk($disk)->put($path, 'ok');

            if (Storage::disk($disk)->get($path) !== 'ok') {
                throw new \RuntimeException('Storage read/write failed.');
            }

            Storage::disk($disk)->delete($path);
        });
    }

    private function freshHeartbeat(string $key): bool
    {
        $heartbeat = Cache::get($key);

        return is_string($heartbeat) && Carbon::parse($heartbeat)->greaterThan(now()->subMinutes(3));
    }

    private function environment(): bool
    {
        return config('app.env') === 'production'
            ? filled(config('app.key')) && config('app.debug') === false
            : filled(config('app.key'));
    }

    private function migrations(): bool
    {
        return $this->attempt(function (): void {
            Artisan::call('migrate:status');

            if (preg_match('/\bPending\b/', Artisan::output()) === 1) {
                throw new \RuntimeException('Pending migrations detected.');
            }
        });
    }

    private function diskSpace(): bool
    {
        $free = disk_free_space(storage_path());
        $total = disk_total_space(storage_path());

        return is_numeric($free) && is_numeric($total) && $total > 0 && ($free / $total) >= 0.05;
    }

    private function attempt(callable $operation): bool
    {
        try {
            $operation();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
