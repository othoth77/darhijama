<?php

namespace App\Contracts\Templates;

class TemplateSummary
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $category,
        public readonly ?string $description,
        public readonly ?string $previewUrl,
        public readonly string $detailUrl,
    ) {}
}
