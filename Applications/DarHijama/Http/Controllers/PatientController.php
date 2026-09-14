<?php

namespace Applications\DarHijama\Http\Controllers;

use Applications\DarHijama\Application\Services\DarHijamaOperations;
use Applications\DarHijama\Application\Services\PatientService;
use Applications\DarHijama\Domain\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Mythos\Core\Audit\AuditAction;

class PatientController
{
    public function index(
        Request $request,
        PatientService $patients,
        DarHijamaOperations $operations,
    ): JsonResponse {
        $operations->recordSensitiveRead('patients');

        if ($request->filled('search')) {
            return response()->json($patients->search((string) $request->string('search')));
        }

        $query = Patient::query()->latest();

        if (! request()->user()?->can('dar-hijama.patients.view-private-notes')) {
            $query->select([
                'id', 'public_id', 'reference', 'first_name', 'last_name', 'gender',
                'date_of_birth', 'phone', 'secondary_phone', 'email', 'governorate',
                'city', 'address', 'preferred_language', 'emergency_contact_name',
                'emergency_contact_phone', 'active', 'consent_status', 'consent_at',
                'created_at', 'updated_at',
            ]);
        }

        return response()->json($query->paginate());
    }

    public function store(
        Request $request,
        PatientService $patients,
        DarHijamaOperations $operations,
    ): JsonResponse {
        $validated = $this->validate($request);
        if (! empty($validated['duplicate_override_reason'])
            && ! $request->user()?->can('dar-hijama.patients.duplicate-override')) {
            abort(403);
        }
        $patient = $patients->create($validated, (int) $request->user()?->getAuthIdentifier());
        if ($request->hasFile('media')) {
            $operations->attachPatientMedia($patient, $request->file('media'));
        }

        return response()->json(['patient' => $patient], 201);
    }

    public function update(Request $request, Patient $patient, PatientService $patients): JsonResponse
    {
        return response()->json($patients->update(
            $patient,
            $this->validate($request),
            (int) $request->user()?->getAuthIdentifier(),
        ));
    }

    public function destroy(Patient $patient, DarHijamaOperations $operations): JsonResponse
    {
        $operations->audit(AuditAction::Delete, $patient);
        $patient->delete();

        return response()->json(status: 204);
    }

    private function validate(Request $request): array
    {
        return $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['female', 'male', 'other', 'undisclosed'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'secondary_phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'governorate' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'],
            'preferred_language' => ['sometimes', Rule::in(['ar', 'fr', 'en'])],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'active' => ['sometimes', 'boolean'],
            'consent_status' => ['sometimes', Rule::in(['pending', 'granted', 'withdrawn'])],
            'duplicate_override_reason' => ['nullable', 'string', 'min:10', 'max:1000'],
            'media' => ['nullable', 'file'],
        ]);
    }
}
