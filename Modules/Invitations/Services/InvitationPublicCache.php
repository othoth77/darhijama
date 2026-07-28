<?php

namespace Modules\Invitations\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Invitations\Models\Invitation;
use Modules\Media\Services\MediaService;
use Throwable;

class InvitationPublicCache
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function data(Invitation $invitation): array
    {
        return Cache::remember(
            $this->key($invitation),
            now()->addMinutes(15),
            fn () => $this->build($invitation),
        );
    }

    public function invalidate(Invitation $invitation): void
    {
        Cache::forget($this->key($invitation));
    }

    private function build(Invitation $invitation): array
    {
        $invitation->load([
            'media' => fn ($query) => $query->orderBy('order'),
            'programSteps',
        ]);

        $media = $invitation->media->filter(function ($item): bool {
            try {
                return $this->mediaService->exists($item->path, $item->disk);
            } catch (Throwable) {
                return false;
            }
        });
        $images = $media->where('type', 'image')->values()
            ->map(fn ($item) => [
                'url' => $this->mediaService->url($item->path, $item->disk),
                'id' => $item->id,
            ]);
        $video = $media->firstWhere('type', 'video');
        $audio = $media->firstWhere('type', 'audio');

        return [
            'images' => $images,
            'video_url' => $video ? $this->mediaService->url($video->path, $video->disk) : null,
            'audio_url' => $audio ? $this->mediaService->url($audio->path, $audio->disk) : null,
            'program_steps' => $invitation->programSteps,
        ];
    }

    private function key(Invitation $invitation): string
    {
        return "invitations.public.{$invitation->id}";
    }
}
