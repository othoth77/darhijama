<?php

namespace Applications\DarHijama\Http\Controllers;

use Applications\DarHijama\Application\Services\DarHijamaOperations;
use Applications\DarHijama\Domain\Practitioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mythos\Core\Audit\AuditAction;

class PractitionerAvailabilityController
{
    public function show(Practitioner $practitioner): JsonResponse
    {
        return response()->json($practitioner->load(['schedules', 'unavailability']));
    }

    public function update(
        Request $request,
        Practitioner $practitioner,
        DarHijamaOperations $operations,
    ): JsonResponse {
        $validated = $request->validate([
            'schedules' => ['required', 'array'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:1,7'],
            'schedules.*.starts_at' => ['required', 'date_format:H:i'],
            'schedules.*.ends_at' => ['required', 'date_format:H:i', 'after:schedules.*.starts_at'],
            'schedules.*.break_starts_at' => ['nullable', 'date_format:H:i'],
            'schedules.*.break_ends_at' => ['nullable', 'date_format:H:i'],
            'schedules.*.service_area' => ['nullable', 'string', 'max:100'],
            'schedules.*.home_visits' => ['sometimes', 'boolean'],
            'unavailability' => ['sometimes', 'array'],
            'unavailability.*.starts_at' => ['required', 'date'],
            'unavailability.*.ends_at' => ['required', 'date', 'after:unavailability.*.starts_at'],
            'unavailability.*.reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($practitioner, $validated): void {
            $practitioner->schedules()->delete();
            $practitioner->schedules()->createMany($validated['schedules']);
            $practitioner->unavailability()->delete();
            $practitioner->unavailability()->createMany($validated['unavailability'] ?? []);
        });
        $operations->audit(AuditAction::Update, $practitioner, [
            'availability_updated' => true,
        ]);

        return response()->json($practitioner->load(['schedules', 'unavailability']));
    }
}
