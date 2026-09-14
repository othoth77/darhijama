<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$event = collect(app(Schedule::class)->events())->first(
    fn ($event) => str_contains((string) $event->command, 'dar-hijama:health-heartbeat'),
);

if ($event === null || ! $event->mutex->create($event)) {
    fwrite(STDERR, 'Unable to acquire scheduler overlap mutex.');
    exit(1);
}

$before = Cache::get('dar-hijama:health:scheduler');
$skipped = $event->shouldSkipDueToOverlapping();
Artisan::call('schedule:run', ['-v' => true]);
$after = Cache::get('dar-hijama:health:scheduler');
$event->mutex->forget($event);

echo json_encode([
    'mutex_acquired' => true,
    'skip_due_to_overlap' => $skipped,
    'heartbeat_unchanged' => $before === $after,
    'scheduler_output' => trim(Artisan::output()),
], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

exit($before === $after && $skipped ? 0 : 1);
