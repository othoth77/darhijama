<?php

namespace Mythos\Core\QrCode\Contracts;

use Mythos\Core\Media\Services\StoredMediaFile;
use Mythos\Core\Support\Api\ApiStatus;
use Mythos\Core\Support\Api\PublicApi;

#[PublicApi(ApiStatus::Stable, since: '1.0.0')]
interface QrCodeGenerator
{
    public function getOrCreate(string $contents, string $path, ?string $disk = null): StoredMediaFile;

    public function regenerate(string $contents, string $path, ?string $disk = null): StoredMediaFile;

    public function invalidate(string $path, ?string $disk = null): bool;
}
