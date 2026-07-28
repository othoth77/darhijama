<?php

namespace Modules\Invitations\Services;

use App\Support\QrCode\QrCodeService;
use Modules\Invitations\Models\Invitation;
use Modules\Media\Services\MediaService;
use Modules\Media\Services\StoredMediaFile;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Adaptateur Invitation du service QR partagé.
 *
 * Le disque public et le chemin historique qrcodes/{token}.png sont conservés.
 */
class InvitationQrCodeService
{
    private const DISK = 'public';

    public function __construct(
        private readonly QrCodeService $qrCodeService,
        private readonly InvitationPublicLinkService $publicLinkService,
        private readonly MediaService $mediaService,
    ) {}

    public function publicUrl(Invitation $invitation): string
    {
        return $this->publicLinkService->show($invitation);
    }

    public function qrUrl(Invitation $invitation): string
    {
        return $this->publicLinkService->qr($invitation);
    }

    public function getOrCreate(Invitation $invitation): StoredMediaFile
    {
        $stored = $this->qrCodeService->getOrCreate(
            contents: $this->publicUrl($invitation),
            path: $this->path($invitation),
            disk: self::DISK,
        );

        return $this->cachePath($invitation, $stored);
    }

    public function regenerate(Invitation $invitation): StoredMediaFile
    {
        $stored = $this->qrCodeService->regenerate(
            contents: $this->publicUrl($invitation),
            path: $this->path($invitation),
            disk: self::DISK,
        );

        return $this->cachePath($invitation, $stored);
    }

    public function invalidate(Invitation $invitation, bool $deleteFile = true): void
    {
        $path = $invitation->qr_code_path;

        if ($path === null) {
            return;
        }

        if ($deleteFile && ! $this->qrCodeService->invalidate($path, self::DISK)) {
            throw new RuntimeException('Impossible de supprimer le QR Code mis en cache.');
        }

        $invitation->forceFill(['qr_code_path' => null])->save();
    }

    public function response(Invitation $invitation): StreamedResponse
    {
        $stored = $this->getOrCreate($invitation);

        return $this->mediaService->response($stored->path, $stored->disk);
    }

    protected function path(Invitation $invitation): string
    {
        return $invitation->qr_code_path
            ?? "qrcodes/{$invitation->public_token}.png";
    }

    protected function cachePath(
        Invitation $invitation,
        StoredMediaFile $stored,
    ): StoredMediaFile {
        if ($invitation->qr_code_path !== $stored->path) {
            $invitation->forceFill(['qr_code_path' => $stored->path])->save();
        }

        return $stored;
    }
}
