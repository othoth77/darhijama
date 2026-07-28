<?php

namespace Applications\DarHijama;

use Mythos\Core\Applications\AbstractApplication;
use Mythos\Core\Applications\Definitions\NavigationDefinition;
use Mythos\Core\Applications\Definitions\PermissionDefinition;

class Application extends AbstractApplication
{
    public function permissions(): array
    {
        return collect($this->manifest()->permissions)
            ->map(fn (string $permission) => new PermissionDefinition($permission))
            ->all();
    }

    public function navigation(): array
    {
        return [
            new NavigationDefinition(
                label: 'Dar Hijama',
                group: 'Applications',
                icon: 'heroicon-o-calendar-days',
                url: '/dar-hijama',
                sort: 30,
            ),
        ];
    }
}
