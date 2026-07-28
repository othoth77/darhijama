<?php

namespace Mythos\Core\Applications\Definitions;

final readonly class PermissionDefinition
{
    public function __construct(
        public string $name,
        public string $guard = 'web',
        public ?string $description = null,
    ) {}
}
