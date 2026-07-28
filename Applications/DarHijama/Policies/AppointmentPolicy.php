<?php

namespace Applications\DarHijama\Policies;

use Applications\DarHijama\Domain\Appointment;
use Applications\DarHijama\Domain\Practitioner;
use Mythos\Core\Identity\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('dar-hijama.appointments.view');
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->can('dar-hijama.appointments.view')
            && ($user->can('dar-hijama.appointments.manage') || $this->isAssigned($user, $appointment));
    }

    public function create(User $user): bool
    {
        return $user->can('dar-hijama.appointments.create');
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->can('dar-hijama.appointments.update');
    }

    public function start(User $user, Appointment $appointment): bool
    {
        return $user->can('dar-hijama.appointments.start')
            && $this->isAssigned($user, $appointment);
    }

    public function complete(User $user, Appointment $appointment): bool
    {
        return $user->can('dar-hijama.appointments.complete')
            && $this->isAssigned($user, $appointment);
    }

    private function isAssigned(User $user, Appointment $appointment): bool
    {
        return Practitioner::query()
            ->whereKey($appointment->getAttribute('practitioner_id'))
            ->where('user_id', $user->getKey())
            ->exists();
    }
}
