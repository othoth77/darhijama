<?php

namespace Applications\DarHijama\Application\Services;

use Applications\DarHijama\Domain\Appointment;
use Applications\DarHijama\Domain\AppointmentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Mythos\Core\Audit\AuditAction;

class AppointmentStateMachine
{
    private const TRANSITIONS = [
        'draft' => ['pending', 'cancelled'],
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['practitioner_assigned', 'cancelled', 'rescheduled'],
        'practitioner_assigned' => ['in_progress', 'cancelled', 'rescheduled', 'no_show'],
        'in_progress' => ['completed'],
        'completed' => [],
        'cancelled' => [],
        'no_show' => [],
        'rescheduled' => [],
    ];

    public function __construct(
        private readonly DarHijamaOperations $operations,
    ) {}

    public function transition(
        Appointment $appointment,
        AppointmentStatus $to,
        int $actorId,
        ?string $reason = null,
        ?string $notes = null,
        string $channel = 'administration',
    ): Appointment {
        $permission = match ($to) {
            AppointmentStatus::Confirmed => 'dar-hijama.appointments.confirm',
            AppointmentStatus::PractitionerAssigned => 'dar-hijama.appointments.assign',
            AppointmentStatus::Cancelled => 'dar-hijama.appointments.cancel',
            AppointmentStatus::Rescheduled => 'dar-hijama.appointments.reschedule',
            AppointmentStatus::InProgress => 'dar-hijama.appointments.start',
            AppointmentStatus::Completed => 'dar-hijama.appointments.complete',
            default => 'dar-hijama.appointments.update',
        };
        Gate::authorize($permission);

        $from = $appointment->status;

        if (! in_array($to->value, self::TRANSITIONS[$from->value], true)) {
            throw ValidationException::withMessages([
                'status' => "Invalid appointment transition: {$from->value} to {$to->value}.",
            ]);
        }

        if (in_array($to, [AppointmentStatus::Cancelled, AppointmentStatus::Rescheduled], true)
            && trim((string) $reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'A reason is required for this transition.',
            ]);
        }

        return DB::transaction(function () use (
            $appointment, $from, $to, $actorId, $reason, $notes, $channel,
        ): Appointment {
            $updates = [
                'status' => $to,
                'updated_by' => $actorId,
            ];

            if ($to === AppointmentStatus::Confirmed) {
                $updates['confirmed_at'] = now();
            } elseif ($to === AppointmentStatus::Completed) {
                $updates['completed_at'] = now();
            } elseif ($to === AppointmentStatus::Cancelled) {
                $updates['cancelled_at'] = now();
                $updates['cancellation_reason'] = $reason;
            } elseif ($to === AppointmentStatus::Rescheduled) {
                $updates['rescheduling_reason'] = $reason;
            }

            $appointment->update($updates);
            $appointment->transitions()->create([
                'from_status' => $from,
                'to_status' => $to,
                'actor_id' => $actorId,
                'reason' => $reason,
                'notes' => $notes,
                'channel' => $channel,
                'occurred_at' => now(),
            ]);

            $action = $to === AppointmentStatus::Cancelled
                ? AuditAction::Archive
                : AuditAction::Update;
            $this->operations->audit($action, $appointment, [
                'status' => ['before' => $from->value, 'after' => $to->value],
                'reason' => $reason,
            ]);
            $this->operations->track("appointment_{$to->value}", $appointment);

            return $appointment->fresh() ?? $appointment;
        });
    }
}
