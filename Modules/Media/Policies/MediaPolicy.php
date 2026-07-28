<?php

namespace Modules\Media\Policies;

use App\Models\User;
use Modules\Media\Models\Media;

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
