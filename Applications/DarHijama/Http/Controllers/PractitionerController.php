<?php

namespace Applications\DarHijama\Http\Controllers;

use Applications\DarHijama\Application\Services\DarHijamaOperations;
use Applications\DarHijama\Domain\Practitioner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Mythos\Core\Audit\AuditAction;

class PractitionerController
{
    public function index(): JsonResponse
    {
        return response()->json(Practitioner::query()->latest()->paginate());
    }

    public function store(Request $request, DarHijamaOperations $operations): JsonResponse
    {
        $practitioner = Practitioner::query()->create($this->validate($request));
        $operations->audit(AuditAction::Create, $practitioner);

        return response()->json($practitioner, 201);
    }

    public function update(Request $request, Practitioner $practitioner, DarHijamaOperations $operations): JsonResponse
    {
        $practitioner->update($this->validate($request, $practitioner));
        $operations->audit(AuditAction::Update, $practitioner);

        return response()->json($practitioner->fresh());
    }

    public function destroy(Practitioner $practitioner, DarHijamaOperations $operations): JsonResponse
    {
        $operations->audit(AuditAction::Delete, $practitioner);
        $practitioner->delete();

        return response()->json(status: 204);
    }

    private function validate(Request $request, ?Practitioner $practitioner = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'license_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('dar_hijama_practitioners')->ignore($practitioner),
            ],
            'active' => ['sometimes', 'boolean'],
        ]);
    }
}
