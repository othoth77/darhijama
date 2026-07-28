<?php

namespace App\Support\QrCode;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Modules\Media\Services\MediaService;
use Modules\Media\Services\StoredMediaFile;
use RuntimeException;
use Throwable;

/**
 * Génération QR réutilisable avec cache fichier, régénération sûre et support
 * de tous les disques gérés par MediaService.
 */
class QrCodeService
{
    public function __construct(
        private readonly MediaService $mediaService,
    ) {}

    public function getOrCreate(
        string $contents,
        string $path,
        ?string $disk = null,
    ): StoredMediaFile {
        $disk ??= $this->mediaService->defaultDisk();

        if ($this->mediaService->exists($path, $disk)) {
            return $this->storedFile($disk, $path);
        }

        return $this->regenerate($contents, $path, $disk);
    }

    public function regenerate(
        string $contents,
        string $path,
        ?string $disk = null,
    ): StoredMediaFile {
        $disk ??= $this->mediaService->defaultDisk();
        $directory = $this->directory($path);
        $png = $this->render($contents);
        $temporary = $this->mediaService->storeContents(
            contents: $png,
            directory: $directory,
            extension: 'png',
            disk: $disk,
        );
        $backupPath = null;
        $backupCreated = false;

        try {
            if ($this->mediaService->exists($path, $disk)) {
                $backupPath = $path.'.backup';

                if (! $this->mediaService->delete($backupPath, $disk)) {
                    throw new RuntimeException('Impossible de nettoyer la sauvegarde QR obsolète.');
                }

                if (! $this->mediaService->move($path, $backupPath, $disk)) {
                    throw new RuntimeException('Impossible de sauvegarder le QR Code existant.');
                }

                $backupCreated = true;
            }

            if (! $this->mediaService->move($temporary->path, $path, $disk)) {
                throw new RuntimeException('Impossible de promouvoir le nouveau QR Code.');
            }
        } catch (Throwable $exception) {
            $this->restoreAfterFailure(
                $path,
                $temporary->path,
                $backupCreated ? $backupPath : null,
                $disk,
                $exception,
            );
        }

        if ($backupPath !== null) {
            $this->mediaService->delete($backupPath, $disk);
        }

        $this->mediaService->delete($temporary->path, $disk);

        return $this->storedFile($disk, $path, strlen($png));
    }

    public function invalidate(string $path, ?string $disk = null): bool
    {
        return $this->mediaService->delete(
            $path,
            $disk ?? $this->mediaService->defaultDisk(),
        );
    }

    protected function render(string $contents): string
    {
        $writer = new PngWriter;
        $qrCode = QrCode::create($contents)
            ->setSize(480)
            ->setMargin(16);

        return $writer->write($qrCode)->getString();
    }

    protected function directory(string $path): string
    {
        $directory = str_replace('\\', '/', dirname($path));

        if ($directory === '.' || $directory === '') {
            throw new RuntimeException('Le QR Code doit être stocké dans un répertoire.');
        }

        return $directory;
    }

    protected function restoreAfterFailure(
        string $path,
        string $temporaryPath,
        ?string $backupPath,
        string $disk,
        Throwable $exception,
    ): never {
        $this->mediaService->delete($temporaryPath, $disk);

        if ($backupPath !== null && $this->mediaService->exists($backupPath, $disk)) {
            $this->mediaService->delete($path, $disk);

            if (! $this->mediaService->move($backupPath, $path, $disk)) {
                throw new RuntimeException(
                    'Échec de régénération et de restauration du QR Code.',
                    previous: $exception,
                );
            }
        }

        throw $exception;
    }

    protected function storedFile(string $disk, string $path, ?int $size = null): StoredMediaFile
    {
        return new StoredMediaFile(
            disk: $disk,
            path: $path,
            originalName: basename($path),
            mimeType: 'image/png',
            size: $size,
        );
    }
}
