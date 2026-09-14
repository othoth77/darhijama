<?php

namespace Mythos\Core\Applications;

use Mythos\Core\Applications\Contracts\MythosApplication;
use Mythos\Core\Applications\Manifest\ApplicationManifest;

abstract class AbstractApplication implements MythosApplication
{
    public function __construct(
        private readonly ApplicationManifest $applicationManifest,
    ) {}

    final public function manifest(): ApplicationManifest
    {
        return $this->applicationManifest;
    }

    public function permissions(): array
    {
        return [];
    }

    public function navigation(): array
    {
        return [];
    }

    public function registering(): void {}

    public function registered(): void {}

    public function booting(): void {}

    public function booted(): void {}

    public function enabling(): void {}

    public function disabling(): void {}
}
