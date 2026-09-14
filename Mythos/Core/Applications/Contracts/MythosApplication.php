<?php

namespace Mythos\Core\Applications\Contracts;

use Mythos\Core\Applications\Definitions\NavigationDefinition;
use Mythos\Core\Applications\Definitions\PermissionDefinition;
use Mythos\Core\Applications\Manifest\ApplicationManifest;
use Mythos\Core\Support\Api\ApiStatus;
use Mythos\Core\Support\Api\PublicApi;

#[PublicApi(ApiStatus::Stable, since: '1.0.0')]
interface MythosApplication
{
    public function manifest(): ApplicationManifest;

    /** @return list<PermissionDefinition> */
    public function permissions(): array;

    /** @return list<NavigationDefinition> */
    public function navigation(): array;

    public function registering(): void;

    public function registered(): void;

    public function booting(): void;

    public function booted(): void;

    public function enabling(): void;

    public function disabling(): void;
}
