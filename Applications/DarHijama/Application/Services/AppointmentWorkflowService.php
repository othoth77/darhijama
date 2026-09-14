<?php

namespace Applications\DarHijama\Application\Services;

use Applications\DarHijama\Domain\Appointment;
use Applications\DarHijama\Domain\AppointmentSource;
use Applications\DarHijama\Domain\AppointmentStatus;
use Applications\DarHijama\Domain\AppointmentType;
use Applications\DarHijama\Domain\Practitioner;
use Applications\DarHijama\Domain\SessionRecord;
use Carbon\CarbonImmutable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mythos\Core\Audit\AuditAction;
use Mythos\Core\Media\Contracts\MediaManager;

class AppointmentWorkflowService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly AppointmentStateMachine $stateMachine,
        private readonly DarHijamaOperations $operations,
        private readonly MediaManager $media,
    ) {}

    public function book(array $attributes, int $actorId): Appointment
    {
        Gate::authorize('create', Appointment::class);
        $appointment = DB::transaction(function () use (
            $attributes, $actorId,
        ): Appointment {
            $practitioner = Practitioner::query()
                ->whereKey($attributes['practitioner_id'])
                ->lockForUpdate()
                ->firstOrFail();
            $startsAt = CarbonImmutable::parse($attributes['starts_at']);
            $duration = (int) ($attributes['expected_duration_minutes']
                ?? $practitioner->getAttribute('appointment_duration_minutes'));
            $this->availability->assertAvailable(
                $practitioner,
                $startsAt,
                $duration,
                $attributes['visit_mode'] ?? 'clinic',
            );
            $appointment = new Appointment([
                ...$attributes,
                'public_id' => (string) Str::uuid(),
                'reference' => 'APT-'.now()->format('ym').'-'.strtoupper(Str::random(8)),
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->addMinutes($duration),
                'expected_duration_minutes' => $duration,
                'status' => AppointmentStatus::Pending,
                'type' => $attributes['type'] ?? AppointmentType::InitialConsultation,
                'source' => $attributes['source'] ?? AppointmentSource::Administration,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
            $appointment->save();
            $appointment->transitions()->create([
                'from_status' => AppointmentStatus::Draft,
                'to_status' => AppointmentStatus::Pending,
                'actor_id' => $actorId,
                'channel' => $attributes['source'] ?? 'administration',
                'occurred_at' => now(),
            ]);
            $this->operations->audit(AuditAction::Create, $appointment);
            $this->operations->track('appointment_created', $appointment);

            return $appointment;
        }, attempts: 5);

        $this->operations->notifyAppointmentCreated($appointment);

        return $appointment;
    }

    public function confirm(Appointment $appointment, int $actorId): Appointment
    {
        Gate::authorize('dar-hijama.appointments.confirm');
        $appointment = $this->stateMachine->transition(
            $appointment,
            AppointmentStatus::Confirmed,
            $actorId,
        );
        $this->operations->notify($appointment, 'appointment-confirmed', 'Appointment confirmed');

        return $appointment;
    }

    public function assign(Appointment $appointment, Practitioner $practitioner, int $actorId): Appointment
    {
        Gate::authorize('dar-hijama.appointments.assign');
        $startsAt = CarbonImmutable::parse($appointment->getAttribute('starts_at'));
        $this->availability->assertAvailable(
            $practitioner,
            $startsAt,
            (int) $appointment->getAttribute('expected_duration_minutes'),
            (string) $appointment->getAttribute('visit_mode'),
            (int) $appointment->getKey(),
        );
        $appointment->update([
            'practitioner_id' => $practitioner->getKey(),
            'updated_by' => $actorId,
        ]);
        $appointment = $this->stateMachine->transition(
            $appointment,
            AppointmentStatus::PractitionerAssigned,
            $actorId,
        );
        $this->operations->notify($appointment, 'practitioner-assigned', 'Practitioner assigned');

        return $appointment;
    }

    public function reschedule(
        Appointment $appointment,
        CarbonImmutable $startsAt,
        string $reason,
        int $actorId,
    ): Appointment {
        Gate::authorize('dar-hijama.appointments.reschedule');
        $replacement = DB::transaction(function () use (
            $appointment, $startsAt, $reason, $actorId,
        ): Appointment {
            $appointment = Appointment::query()
                ->whereKey($appointment->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $practitioner = Practitioner::query()
                ->whereKey($appointment->getAttribute('practitioner_id'))
                ->lockForUpdate()
                ->firstOrFail();
            $duration = (int) $appointment->getAttribute('expected_duration_minutes');
            $this->availability->assertAvailable(
                $practitioner,
                $startsAt,
                $duration,
                (string) $appointment->getAttribute('visit_mode'),
                (int) $appointment->getKey(),
            );
            $this->stateMachine->transition(
                $appointment,
                AppointmentStatus::Rescheduled,
                $actorId,
                $reason,
            );
            $replacement = $appointment->replicate([
                'public_id', 'reference', 'status', 'confirmed_at', 'completed_at',
                'cancelled_at', 'rescheduling_reason',
            ]);
            $replacement->forceFill([
                'public_id' => (string) Str::uuid(),
                'reference' => 'APT-'.now()->format('ym').'-'.strtoupper(Str::random(8)),
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->addMinutes($duration),
                'status' => AppointmentStatus::Pending,
                'original_appointment_id' => $appointment->getKey(),
                'rescheduling_reason' => $reason,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ])->save();
            $replacement->transitions()->create([
                'from_status' => AppointmentStatus::Draft,
                'to_status' => AppointmentStatus::Pending,
                'actor_id' => $actorId,
                'reason' => $reason,
                'channel' => 'administration',
                'occurred_at' => now(),
            ]);

            return $replacement;
        }, attempts: 5);

        $this->operations->notify($replacement, 'appointment-rescheduled', 'Appointment rescheduled');

        return $replacement;
    }

    public function cancel(
        Appointment $appointment,
        string $reason,
        int $actorId,
        string $channel,
    ): Appointment {
        Gate::authorize('dar-hijama.appointments.cancel');
        $appointment = $this->stateMachine->transition(
            $appointment,
            AppointmentStatus::Cancelled,
            $actorId,
            $reason,
            channel: $channel,
        );
        $this->operations->notify($appointment, 'appointment-cancelled', 'Appointment cancelled');

        return $appointment;
    }

    public function start(Appointment $appointment, int $actorId): Appointment
    {
        Gate::authorize('start', $appointment);
        $appointment = $this->stateMachine->transition(
            $appointment,
            AppointmentStatus::InProgress,
            $actorId,
        );
        $appointment->sessionRecord()->updateOrCreate(
            ['appointment_id' => $appointment->getKey()],
            ['started_at' => now(), 'recorded_by' => $actorId],
        );

        return $appointment;
    }

    public function complete(
        Appointment $appointment,
        array $attributes,
        int $actorId,
        ?UploadedFile $attachment = null,
    ): Appointment {
        Gate::authorize('complete', $appointment);
        $session = SessionRecord::query()->firstOrNew([
            'appointment_id' => $appointment->getKey(),
        ]);
        $session->forceFill([
            ...$attributes,
            'completed_at' => now(),
            'recorded_by' => $actorId,
        ])->save();

        if ($attachment !== null) {
            $this->media->uploadFor(
                $session,
                $attachment,
                'session-attachment',
                'dar-hijama/private/sessions',
                disk: 'local',
            );
        }

        $appointment = $this->stateMachine->transition(
            $appointment,
            AppointmentStatus::Completed,
            $actorId,
        );

        return $appointment;
    }

    public function createFollowUp(Appointment $appointment, CarbonImmutable $startsAt, int $actorId): Appointment
    {
        Gate::authorize('dar-hijama.appointments.create');
        if ($appointment->followUp()->exists()) {
            throw ValidationException::withMessages([
                'follow_up' => 'A follow-up appointment already exists.',
            ]);
        }

        $followUp = $this->book([
            'patient_id' => $appointment->getAttribute('patient_id'),
            'practitioner_id' => $appointment->getAttribute('practitioner_id'),
            'starts_at' => $startsAt,
            'type' => AppointmentType::FollowUp,
            'visit_mode' => $appointment->getAttribute('visit_mode'),
            'source' => AppointmentSource::Administration,
            'original_appointment_id' => $appointment->getKey(),
        ], $actorId);
        $this->operations->track('follow_up_created', $followUp, [
            'original_appointment_id' => $appointment->getKey(),
        ]);
        $this->operations->notify($followUp, 'follow-up-reminder', 'Follow-up scheduled');

        return $followUp;
    }
}
