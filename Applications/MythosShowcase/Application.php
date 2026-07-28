<?php

namespace Applications\MythosShowcase;

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
                label: 'Mythos Showcase',
                group: 'Applications',
                icon: 'heroicon-o-squares-2x2',
                url: '/mythos/showcase',
                sort: 20,
            ),
        ];
    }
}
