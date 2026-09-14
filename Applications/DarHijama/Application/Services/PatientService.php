<?php

namespace Applications\DarHijama\Application\Services;

use Applications\DarHijama\Domain\ApplicationEvent;
use Applications\DarHijama\Domain\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mythos\Core\Audit\AuditAction;

class PatientService
{
    public function __construct(
        private readonly DarHijamaOperations $operations,
    ) {}

    public function create(array $attributes, int $actorId): Patient
    {
        Gate::authorize('create', Patient::class);
        $normalizedPhone = $this->normalizePhone($attributes['phone'] ?? null);
        $overrideReason = trim((string) ($attributes['duplicate_override_reason'] ?? ''));

        if ($overrideReason !== '' && ! Gate::allows('dar-hijama.patients.duplicate-override')) {
            abort(403);
        }

        if ($normalizedPhone !== null
            && Patient::query()->where('normalized_phone', $normalizedPhone)->exists()
            && $overrideReason === '') {
            throw ValidationException::withMessages([
                'phone' => 'A patient with this phone number already exists.',
            ]);
        }

        return DB::transaction(function () use ($attributes, $actorId, $normalizedPhone): Patient {
            $patient = new Patient([
                ...$attributes,
                'public_id' => (string) Str::uuid(),
                'reference' => $this->reference(),
                'normalized_phone' => $normalizedPhone,
                'created_by' => $actorId,
                'updated_by' => $actorId,
                'consent_at' => ($attributes['consent_status'] ?? 'pending') === 'granted'
                    ? now()
                    : null,
            ]);
            $patient->save();

            $this->operations->audit(AuditAction::Create, $patient);
            $this->track('patient_created', $patient, $actorId);

            return $patient;
        });
    }

    public function update(Patient $patient, array $attributes, int $actorId): Patient
    {
        Gate::authorize('update', $patient);
        $normalizedPhone = $this->normalizePhone($attributes['phone'] ?? $patient->phone);
        $duplicate = $normalizedPhone !== null && Patient::query()
            ->where('normalized_phone', $normalizedPhone)
            ->whereKeyNot($patient->getKey())
            ->exists();

        if ($duplicate && trim((string) ($attributes['duplicate_override_reason'] ?? '')) === '') {
            throw ValidationException::withMessages([
                'phone' => 'A patient with this phone number already exists.',
            ]);
        }

        $before = $patient->only($patient->getFillable());
        $patient->forceFill([
            ...$attributes,
            'normalized_phone' => $normalizedPhone,
            'updated_by' => $actorId,
            'consent_at' => ($attributes['consent_status'] ?? null) === 'granted'
                ? ($patient->getAttribute('consent_at') ?? now())
                : $patient->getAttribute('consent_at'),
        ])->save();
        $this->operations->audit(AuditAction::Update, $patient, [
            'before' => $before,
            'after' => $patient->only($patient->getFillable()),
        ]);

        return $patient->fresh() ?? $patient;
    }

    public function search(string $term)
    {
        $normalizedPhone = $this->normalizePhone($term);

        return Patient::query()
            ->select([
                'public_id', 'reference', 'first_name', 'last_name', 'phone',
                'email', 'city', 'active', 'created_at',
            ])
            ->where(function ($query) use ($term, $normalizedPhone): void {
                $query->where('reference', 'like', "%{$term}%")
                    ->orWhere('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%");

                if ($normalizedPhone !== null) {
                    $query->orWhere('normalized_phone', 'like', "%{$normalizedPhone}%");
                }
            })
            ->latest()
            ->paginate();
    }

    public function normalizePhone(?string $phone): ?string
    {
        if ($phone === null || trim($phone) === '') {
            return null;
        }

        $normalized = preg_replace('/\D+/', '', $phone);

        return $normalized === '' ? null : $normalized;
    }

    private function reference(): string
    {
        return 'PAT-'.now()->format('ym').'-'.strtoupper(Str::random(8));
    }

    private function track(string $event, Patient $patient, int $actorId): void
    {
        ApplicationEvent::query()->create([
            'event' => $event,
            'subject_type' => Patient::class,
            'subject_id' => (string) $patient->getKey(),
            'actor_id' => $actorId,
            'occurred_at' => now(),
        ]);
    }
}
