<?php

namespace Mythos\Core\Applications\Contracts;

use Mythos\Core\Applications\Definitions\NavigationDefinition;
use Mythos\Core\Applications\Definitions\PermissionDefinition;
use Mythos\Core\Applications\Manifest\ApplicationManifest;
use Mythos\Core\Support\Api\ApiStatus;
use Mythos\Core\Support\Api\PublicApi;

#[PublicApi(ApiStatus::Stable, since: '1.0.0')]
interface ApplicationRegistry
{
    public function discover(array $manifestPaths): void;

    public function registerManifest(string $manifestPath): ApplicationManifest;

    public function boot(): void;

    /** @return list<ApplicationManifest> */
    public function applications(): array;

    public function find(string $slug): ?ApplicationManifest;

    public function validate(string $slug): ApplicationManifest;

    /** @return list<PermissionDefinition> */
    public function permissions(string $slug): array;

    /** @return list<NavigationDefinition> */
    public function navigation(string $slug): array;
}
