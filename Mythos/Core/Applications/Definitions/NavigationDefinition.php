<?php

namespace Mythos\Core\Applications\Definitions;

final readonly class NavigationDefinition
{
    public function __construct(
        public string $label,
        public ?string $group = null,
        public ?string $icon = null,
        public ?string $url = null,
        public ?string $widget = null,
        public int $sort = 0,
    ) {}
}
