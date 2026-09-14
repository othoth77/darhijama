<?php

namespace Modules\Templates\Policies;

use Modules\Templates\Models\Template;
use Mythos\Core\Identity\Models\User;

class TemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('templates.manage');
    }

    public function view(User $user, Template $template): bool
    {
        return $user->can('templates.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('templates.manage');
    }

    public function update(User $user, Template $template): bool
    {
        return $user->can('templates.manage');
    }

    public function delete(User $user, Template $template): bool
    {
        return $user->can('templates.manage');
    }
}
