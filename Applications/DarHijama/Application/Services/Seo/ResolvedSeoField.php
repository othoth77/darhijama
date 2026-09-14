<?php

namespace Applications\DarHijama\Application\Services\Seo;

/**
 * A single SEO field after fallback resolution — carries whether the value
 * came from an explicit editor input or was generated, so the admin UI can
 * label it honestly ("Auto" vs "Manual") instead of hiding the distinction.
 */
final readonly class ResolvedSeoField
{
    public function __construct(
        public ?string $value,
        public bool $isAuto,
    ) {}
}
