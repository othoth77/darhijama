<?php

namespace Modules\Invitations\Policies;

use App\Models\User;
use Modules\Invitations\Models\Invitation;

class InvitationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('invitations.manage');
    }

    public function view(User $user, Invitation $invitation): bool
    {
        return $user->can('invitations.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('invitations.manage');
    }

    public function update(User $user, Invitation $invitation): bool
    {
        return $user->can('invitations.manage');
    }

    public function delete(User $user, Invitation $invitation): bool
    {
        return $user->can('invitations.manage');
    }
}
