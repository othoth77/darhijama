<?php

namespace Modules\Invitations\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Models\Invitation;

class DuplicateInvitationService
{
    public function execute(Invitation $source): Invitation
    {
        return DB::transaction(function () use ($source): Invitation {
            $copy = $source->replicate([
                'public_token',
                'qr_code_path',
                'status',
                'published_at',
            ]);
            $copy->forceFill([
                'public_token' => (string) Str::ulid()->toBase32(),
                'title' => trim(($source->title ?: "{$source->groom_name} & {$source->bride_name}").' — Copie'),
                'status' => InvitationStatus::Brouillon,
                'published_at' => null,
                'qr_code_path' => null,
            ])->save();

            foreach ($source->programSteps()->orderBy('order')->get() as $step) {
                $copy->programSteps()->create($step->only([
                    'time',
                    'title',
                    'description',
                    'order',
                ]));
            }

            foreach ($source->media()->orderBy('order')->get() as $media) {
                $copy->media()->create($media->only([
                    'disk',
                    'path',
                    'type',
                    'original_name',
                    'mime_type',
                    'size',
                    'order',
                ]));
            }

            return $copy;
        });
    }
}
