<?php

namespace Applications\DarHijama\Http\Controllers;

use Applications\DarHijama\Application\Services\DarHijamaOperations;
use Applications\DarHijama\Domain\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mythos\Core\Audit\AuditAction;

class SettingController
{
    public function index(): JsonResponse
    {
        return response()->json(Setting::query()->orderBy('key')->get());
    }

    public function update(string $key, Request $request, DarHijamaOperations $operations): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'array'],
        ]);
        $setting = Setting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $validated['value'],
                'updated_by' => $request->user()?->getAuthIdentifier(),
                'updated_at' => now(),
            ],
        );
        $operations->audit(
            $setting->wasRecentlyCreated ? AuditAction::Create : AuditAction::Update,
            $setting,
        );

        return response()->json($setting);
    }
}
