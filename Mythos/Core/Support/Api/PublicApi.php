<?php

namespace Mythos\Core\Support\Api;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class PublicApi
{
    public function __construct(
        public ApiStatus $status,
        public string $since,
    ) {}
}
