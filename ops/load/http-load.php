<?php

$baseUrl = rtrim($argv[1] ?? 'http://127.0.0.1:8000', '/');
$requests = max(1, (int) ($argv[2] ?? 300));
$concurrency = max(1, (int) ($argv[3] ?? 20));
$paths = ['/up', '/mythos/dar-hijama/health', '/mythos/dar-hijama/ready'];
$pending = 0;
$completed = 0;
$latencies = [];
$statuses = [];
$errors = 0;
$multi = curl_multi_init();
$handles = [];
$startedAt = microtime(true);

$start = function () use (
    &$pending, &$handles, $requests, $baseUrl, $paths, $multi,
): void {
    if ($pending >= $requests) {
        return;
    }
    $path = $paths[$pending % count($paths)];
    $handle = curl_init($baseUrl.$path);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTPHEADER => ['Host: localhost'],
    ]);
    curl_multi_add_handle($multi, $handle);
    $handles[(int) $handle] = $handle;
    $pending++;
};

for ($i = 0; $i < min($concurrency, $requests); $i++) {
    $start();
}

do {
    curl_multi_exec($multi, $running);
    while ($info = curl_multi_info_read($multi)) {
        $handle = $info['handle'];
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        $latencies[] = (float) curl_getinfo($handle, CURLINFO_TOTAL_TIME) * 1000;
        $statuses[$status] = ($statuses[$status] ?? 0) + 1;
        if ($info['result'] !== CURLE_OK || ! in_array($status, [200, 503], true)) {
            $errors++;
        }
        curl_multi_remove_handle($multi, $handle);
        curl_close($handle);
        unset($handles[(int) $handle]);
        $completed++;
        $start();
    }
    if ($running > 0) {
        curl_multi_select($multi, 0.25);
    }
} while ($completed < $requests);

curl_multi_close($multi);
sort($latencies);
$duration = microtime(true) - $startedAt;
$percentile = static function (float $percentile) use ($latencies): float {
    $index = (int) ceil(($percentile / 100) * count($latencies)) - 1;

    return round($latencies[max(0, $index)], 2);
};

echo json_encode([
    'requests' => $requests,
    'concurrency' => $concurrency,
    'duration_seconds' => round($duration, 2),
    'requests_per_second' => round($requests / $duration, 2),
    'p50_ms' => $percentile(50),
    'p95_ms' => $percentile(95),
    'p99_ms' => $percentile(99),
    'error_rate' => round($errors / $requests, 4),
    'statuses' => $statuses,
    'client_peak_memory_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
