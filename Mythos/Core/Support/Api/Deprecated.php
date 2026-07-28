<?php

namespace Mythos\Core\Support\Api;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final readonly class Deprecated
{
    public function __construct(
        public string $since,
        public string $replacement,
        public string $removalVersion,
        public ?string $reason = null,
    ) {}
}
