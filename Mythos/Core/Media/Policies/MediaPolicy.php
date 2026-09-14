<?php

namespace Mythos\Core\Media\Policies;

use Mythos\Core\Identity\Models\User;
use Mythos\Core\Media\Models\Media;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('media.manage');
    }

    public function view(User $user, Media $media): bool
    {
        return $user->can('media.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('media.manage');
    }

    public function update(User $user, Media $media): bool
    {
        return $user->can('media.manage');
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->can('media.manage');
    }
}
