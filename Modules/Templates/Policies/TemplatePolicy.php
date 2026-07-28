<?php

namespace Modules\Templates\Policies;

use App\Models\User;
use Modules\Templates\Models\Template;

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
