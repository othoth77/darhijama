<?php

namespace Modules\Templates\Policies;

use Modules\Templates\Models\TemplateCategory;
use Mythos\Core\Identity\Models\User;

class TemplateCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('templates.manage');
    }

    public function view(User $user, TemplateCategory $templateCategory): bool
    {
        return $user->can('templates.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('templates.manage');
    }

    public function update(User $user, TemplateCategory $templateCategory): bool
    {
        return $user->can('templates.manage');
    }

    public function delete(User $user, TemplateCategory $templateCategory): bool
    {
        return $user->can('templates.manage');
    }
}
