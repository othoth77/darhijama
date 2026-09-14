<?php

namespace Mythos\Core\Media\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mythos\Core\Media\Contracts\MediaManager;
use Mythos\Core\Media\Models\Media;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Point d'entrée unique pour le cycle de vie des fichiers de l'application.
 *
 * Les suppressions de métadonnées peuvent volontairement conserver les fichiers
 * physiques pour préserver le comportement historique des observers métier.
 */
class MediaService implements MediaManager
{
    public function defaultDisk(): string
    {
        return (string) config('media.disk', config('filesystems.default', 'public'));
    }

    /**
     * @throws ValidationException
     */
    public function upload(
        UploadedFile $file,
        string $directory,
        string $profile = 'file',
        ?string $disk = null,
    ): StoredMediaFile {
        $this->validate($file, $profile);

        $disk ??= $this->defaultDisk();
        $directory = $this->normalizeDirectory($directory);
        $filename = $this->generateFilename($file);
        $path = Storage::disk($disk)->putFileAs(
            $directory,
            $file,
            $filename,
            $this->storageOptions(),
        );

        if ($path === false) {
            throw new RuntimeException('Impossible de stocker le fichier média.');
        }

        return new StoredMediaFile(
            disk: $disk,
            path: $path,
            originalName: $file->getClientOriginalName(),
            mimeType: $file->getMimeType(),
            size: $file->getSize() ?: null,
        );
    }

    /**
     * @throws ValidationException
     */
    public function uploadFor(
        Model $mediable,
        UploadedFile $file,
        string $type,
        string $directory,
        string $profile = 'file',
        ?string $disk = null,
        int $order = 0,
    ): Media {
        $stored = $this->upload($file, $directory, $profile, $disk);

        try {
            /** @var Media $media */
            $media = $mediable->media()->create([
                'disk' => $stored->disk,
                'path' => $stored->path,
                'type' => $type,
                'original_name' => $stored->originalName,
                'mime_type' => $stored->mimeType,
                'size' => $stored->size,
                'order' => $order,
            ]);

            return $media;
        } catch (\Throwable $exception) {
            $this->delete($stored->path, $stored->disk);

            throw $exception;
        }
    }

    public function storeContents(
        string $contents,
        string $directory,
        string $extension,
        ?string $disk = null,
        ?string $filename = null,
    ): StoredMediaFile {
        $disk ??= $this->defaultDisk();
        $directory = $this->normalizeDirectory($directory);
        $extension = $this->normalizeExtension($extension);
        $filename = $filename === null
            ? Str::ulid()->toBase32().'.'.$extension
            : $this->normalizeFilename($filename, $extension);
        $path = $directory.'/'.$filename;

        if (! Storage::disk($disk)->put($path, $contents, $this->storageOptions())) {
            throw new RuntimeException('Impossible de stocker le contenu média.');
        }

        return new StoredMediaFile(
            disk: $disk,
            path: $path,
            originalName: $filename,
            mimeType: null,
            size: strlen($contents),
        );
    }

    public function delete(string $path, ?string $disk = null): bool
    {
        $disk ??= $this->defaultDisk();

        if (! Storage::disk($disk)->exists($path)) {
            return true;
        }

        return Storage::disk($disk)->delete($path);
    }

    public function move(string $from, string $to, ?string $disk = null): bool
    {
        $disk ??= $this->defaultDisk();

        return Storage::disk($disk)->move($from, $to);
    }

    public function deleteMedia(Media $media, bool $deleteFile = true): bool
    {
        $fileIsShared = Media::query()
            ->where('disk', $media->disk)
            ->where('path', $media->path)
            ->whereKeyNot($media->getKey())
            ->exists();

        if ($deleteFile && ! $fileIsShared && ! $this->delete($media->path, $media->disk)) {
            return false;
        }

        return (bool) $media->delete();
    }

    public function deleteFor(Model $mediable, bool $deleteFiles = true): void
    {
        $mediable->media()->get()->each(function (Media $media) use ($deleteFiles): void {
            $this->deleteMedia($media, $deleteFiles);
        });
    }

    public function exists(string $path, ?string $disk = null): bool
    {
        return Storage::disk($disk ?? $this->defaultDisk())->exists($path);
    }

    public function url(string $path, ?string $disk = null): string
    {
        return Storage::disk($disk ?? $this->defaultDisk())->url($path);
    }

    public function response(string $path, ?string $disk = null): StreamedResponse
    {
        return Storage::disk($disk ?? $this->defaultDisk())->response($path);
    }

    /**
     * @throws ValidationException
     */
    public function validate(UploadedFile $file, string $profile = 'file'): void
    {
        $profiles = config('media.validation.profiles', []);

        if (! array_key_exists($profile, $profiles)) {
            throw new RuntimeException("Profil de validation média inconnu : {$profile}.");
        }

        Validator::make(
            ['file' => $file],
            ['file' => $profiles[$profile]],
        )->validate();
    }

    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->guessExtension()
            ?: $file->getClientOriginalExtension()
            ?: 'bin';

        return Str::ulid()->toBase32().'.'.$this->normalizeExtension($extension);
    }

    protected function normalizeDirectory(string $directory): string
    {
        $directory = trim(str_replace('\\', '/', $directory), '/');

        if ($directory === '' || str_contains($directory, '..')) {
            throw new RuntimeException('Répertoire média invalide.');
        }

        return $directory;
    }

    protected function normalizeExtension(string $extension): string
    {
        $extension = strtolower(ltrim($extension, '.'));

        if ($extension === '' || preg_match('/^[a-z0-9]+$/', $extension) !== 1) {
            throw new RuntimeException('Extension média invalide.');
        }

        return $extension;
    }

    protected function normalizeFilename(string $filename, string $extension): string
    {
        $filename = basename(str_replace('\\', '/', $filename));

        if (preg_match('/^[A-Za-z0-9._-]+$/', $filename) !== 1) {
            throw new RuntimeException('Nom de fichier média invalide.');
        }

        return pathinfo($filename, PATHINFO_EXTENSION) === ''
            ? $filename.'.'.$extension
            : $filename;
    }

    /**
     * @return array{visibility: string}
     */
    protected function storageOptions(): array
    {
        return [
            'visibility' => (string) config('media.visibility', 'public'),
        ];
    }
}
