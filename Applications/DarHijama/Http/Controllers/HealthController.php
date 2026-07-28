<?php

namespace Applications\DarHijama\Http\Controllers;

use Applications\DarHijama\Application\Operations\ProductionHealthService;
use Illuminate\Http\JsonResponse;

class HealthController
{
    public function live(): JsonResponse
    {
        return response()->json(['application' => 'dar-hijama', 'status' => 'ok']);
    }

    public function ready(ProductionHealthService $health): JsonResponse
    {
        $ready = $health->ready();

        return response()->json(['status' => $ready ? 'ready' : 'not_ready'], $ready ? 200 : 503);
    }

    public function diagnostics(ProductionHealthService $health): JsonResponse
    {
        $checks = $health->checks(fresh: true);
        $ready = ! in_array(false, $checks, true);

        return response()->json([
            'application' => 'dar-hijama',
            'status' => $ready ? 'ready' : 'not_ready',
            'checks' => $checks,
            'checked_at' => now()->toIso8601String(),
        ], $ready ? 200 : 503);
    }
}
