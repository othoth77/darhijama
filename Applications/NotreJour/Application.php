<?php

namespace Applications\NotreJour;

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
                label: 'Notre Jour',
                group: 'Notre Jour',
                icon: 'heroicon-o-heart',
                sort: 10,
            ),
        ];
    }
}
