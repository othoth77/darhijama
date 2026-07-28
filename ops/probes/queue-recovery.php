<?php

use Applications\DarHijama\Application\Operations\OperationalEvidenceJob;
use Applications\DarHijama\Application\Operations\OperationalRecoveryProbeJob;
use Applications\DarHijama\Application\Operations\QueueHeartbeatJob;
use Applications\DarHijama\Domain\Appointment;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$mode = $argv[1] ?? '';
$probeId = $argv[2] ?? (string) Str::uuid();

if ($mode === 'dispatch-success') {
    $appointment = Appointment::query()->firstOrFail();
    QueueHeartbeatJob::dispatch();
    foreach (['audit', 'analytics', 'reminder'] as $operation) {
        OperationalEvidenceJob::dispatch((int) $appointment->getKey(), $operation, $probeId);
    }
    echo json_encode(['probe_id' => $probeId, 'queued' => 4], JSON_THROW_ON_ERROR);
    exit(0);
}

if ($mode === 'dispatch-failure') {
    Cache::forget("dar-hijama:recovery-probe:{$probeId}");
    Cache::forget("dar-hijama:recovery-probe:{$probeId}:recovered");
    OperationalRecoveryProbeJob::dispatch($probeId);
    echo $probeId;
    exit(0);
}

echo json_encode([
    'jobs' => DB::table('jobs')->count(),
    'failed_jobs' => DB::table('failed_jobs')->count(),
    'queue_heartbeat' => Cache::has('dar-hijama:health:queue-worker'),
    'recovered' => Cache::has("dar-hijama:recovery-probe:{$probeId}:recovered"),
    'audit' => DB::table('audit_logs')->where('changes', 'like', "%{$probeId}%")->count(),
    'analytics' => DB::table('dar_hijama_analytics_events')->where('metadata', 'like', "%{$probeId}%")->count(),
    'notification' => DB::table('notification_deliveries')->where('message', 'like', "%{$probeId}%")->count(),
], JSON_THROW_ON_ERROR);
