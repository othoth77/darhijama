<?php

namespace Applications\DarHijama\Policies;

use Applications\DarHijama\Domain\Patient;
use Mythos\Core\Identity\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('dar-hijama.patients.view');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->can('dar-hijama.patients.view');
    }

    public function create(User $user): bool
    {
        return $user->can('dar-hijama.patients.create');
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->can('dar-hijama.patients.update');
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->can('dar-hijama.patients.update');
    }
}
