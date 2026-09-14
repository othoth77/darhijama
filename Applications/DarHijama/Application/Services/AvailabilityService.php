<?php

namespace Applications\DarHijama\Application\Services;

use Applications\DarHijama\Domain\Appointment;
use Applications\DarHijama\Domain\Practitioner;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class AvailabilityService
{
    public function assertAvailable(
        Practitioner $practitioner,
        CarbonImmutable $startsAt,
        int $durationMinutes,
        string $visitMode = 'clinic',
        ?int $ignoreAppointmentId = null,
    ): void {
        if (! $practitioner->getAttribute('active')) {
            $this->fail('practitioner_id', 'The selected practitioner is inactive.');
        }

        if ($visitMode === 'home' && ! $practitioner->getAttribute('home_visits')) {
            $this->fail('visit_mode', 'The selected practitioner does not provide home visits.');
        }

        $bufferBefore = (int) $practitioner->getAttribute('preparation_buffer_minutes');
        $bufferAfter = $visitMode === 'home'
            ? (int) $practitioner->getAttribute('travel_buffer_minutes')
            : 0;
        $endsAt = $startsAt->addMinutes($durationMinutes);
        $schedule = $practitioner->schedules()
            ->where('day_of_week', $startsAt->dayOfWeekIso)
            ->first();

        if ($schedule === null) {
            $this->fail('starts_at', 'The practitioner is not working on the selected day.');
        }

        $workStart = $startsAt->setTimeFromTimeString((string) $schedule->getAttribute('starts_at'));
        $workEnd = $startsAt->setTimeFromTimeString((string) $schedule->getAttribute('ends_at'));

        if ($startsAt->subMinutes($bufferBefore)->lessThan($workStart)
            || $endsAt->addMinutes($bufferAfter)->greaterThan($workEnd)) {
            $this->fail('starts_at', 'The appointment is outside practitioner working hours.');
        }

        $breakStart = $schedule->getAttribute('break_starts_at');
        $breakEnd = $schedule->getAttribute('break_ends_at');

        if (is_string($breakStart) && is_string($breakEnd)) {
            $breakStartsAt = $startsAt->setTimeFromTimeString($breakStart);
            $breakEndsAt = $startsAt->setTimeFromTimeString($breakEnd);

            if ($startsAt->lessThan($breakEndsAt) && $endsAt->greaterThan($breakStartsAt)) {
                $this->fail('starts_at', 'The appointment overlaps a practitioner break.');
            }
        }

        if ($practitioner->unavailability()
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists()) {
            $this->fail('starts_at', 'The practitioner is unavailable at the selected time.');
        }

        $appointments = Appointment::query()
            ->where('practitioner_id', $practitioner->getKey())
            ->whereNotIn('status', ['cancelled', 'rescheduled'])
            ->when($ignoreAppointmentId, fn ($query) => $query->whereKeyNot($ignoreAppointmentId));

        if ((clone $appointments)
            ->where('starts_at', '<', $endsAt->addMinutes($bufferAfter))
            ->where('ends_at', '>', $startsAt->subMinutes($bufferBefore))
            ->exists()) {
            $this->fail('starts_at', 'The selected time overlaps another appointment.');
        }

        if ((clone $appointments)
            ->whereBetween('starts_at', [$startsAt->startOfDay(), $startsAt->endOfDay()])
            ->count() >= (int) $practitioner->getAttribute('maximum_daily_appointments')) {
            $this->fail('starts_at', 'The practitioner daily appointment limit has been reached.');
        }
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
