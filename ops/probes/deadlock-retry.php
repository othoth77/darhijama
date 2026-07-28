<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

[$script, $worker, $first, $second, $barrier] = $argv + [null, null, null, null, null];
$attempts = 0;

DB::transaction(function () use ($worker, $first, $second, $barrier, &$attempts): void {
    $attempts++;
    DB::table('dar_hijama_practitioners')->where('id', (int) $first)->lockForUpdate()->first();

    if ($attempts === 1) {
        file_put_contents("{$barrier}.{$worker}", 'ready');
        $other = $worker === 'a' ? 'b' : 'a';
        $deadline = microtime(true) + 10;
        while (! is_file("{$barrier}.{$other}") && microtime(true) < $deadline) {
            usleep(10_000);
        }
    }

    DB::table('dar_hijama_practitioners')->where('id', (int) $second)->lockForUpdate()->first();
}, attempts: 5);

echo json_encode(['worker' => $worker, 'attempts' => $attempts, 'completed' => true], JSON_THROW_ON_ERROR);
