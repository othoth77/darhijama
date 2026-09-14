<?php

namespace Mythos\Core\Media\Services;

final readonly class StoredMediaFile
{
    public function __construct(
        public string $disk,
        public string $path,
        public ?string $originalName,
        public ?string $mimeType,
        public ?int $size,
    ) {}
}
