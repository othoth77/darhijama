<?php

namespace Applications\DarHijama\Policies;

use Applications\DarHijama\Domain\Practitioner;
use Mythos\Core\Identity\Models\User;

class PractitionerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('dar-hijama.appointments.view');
    }

    public function view(User $user, Practitioner $practitioner): bool
    {
        return $user->can('dar-hijama.appointments.view');
    }

    public function create(User $user): bool
    {
        return $user->can('dar-hijama.practitioners.manage');
    }

    public function update(User $user, Practitioner $practitioner): bool
    {
        return $user->can('dar-hijama.practitioners.manage')
            || ((int) $practitioner->getAttribute('user_id') === (int) $user->getKey()
                && $user->can('dar-hijama.practitioner-availability.manage'));
    }
}
