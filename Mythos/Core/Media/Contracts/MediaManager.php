<?php

namespace Mythos\Core\Media\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Mythos\Core\Media\Models\Media;
use Mythos\Core\Media\Services\StoredMediaFile;
use Mythos\Core\Support\Api\ApiStatus;
use Mythos\Core\Support\Api\PublicApi;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[PublicApi(ApiStatus::Stable, since: '1.0.0')]
interface MediaManager
{
    public function defaultDisk(): string;

    public function upload(UploadedFile $file, string $directory, string $profile = 'file', ?string $disk = null): StoredMediaFile;

    public function uploadFor(Model $mediable, UploadedFile $file, string $type, string $directory, string $profile = 'file', ?string $disk = null, int $order = 0): Media;

    public function storeContents(string $contents, string $directory, string $extension, ?string $disk = null, ?string $filename = null): StoredMediaFile;

    public function delete(string $path, ?string $disk = null): bool;

    public function move(string $from, string $to, ?string $disk = null): bool;

    public function deleteMedia(Media $media, bool $deleteFile = true): bool;

    public function deleteFor(Model $mediable, bool $deleteFiles = true): void;

    public function exists(string $path, ?string $disk = null): bool;

    public function url(string $path, ?string $disk = null): string;

    public function response(string $path, ?string $disk = null): StreamedResponse;

    public function validate(UploadedFile $file, string $profile = 'file'): void;
}
