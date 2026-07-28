<?php

namespace Modules\Invitations\Services;

use Illuminate\Support\Facades\Validator;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Rules\SafeExternalUrl;

class InvitationBusinessValidator
{
    public function validateForPublication(Invitation $invitation): void
    {
        Validator::make($invitation->getAttributes(), [
            'public_token' => ['required', 'string', 'size:26'],
            'groom_name' => ['required', 'string', 'max:255'],
            'bride_name' => ['required', 'string', 'max:255'],
            'wedding_date' => ['required', 'date'],
            'maps_embed_url' => ['nullable', new SafeExternalUrl(['google.com', 'google.tn'])],
            'external_video_url' => ['nullable', new SafeExternalUrl([
                'youtube.com', 'youtu.be', 'youtube-nocookie.com', 'vimeo.com',
            ])],
            'external_audio_url' => ['nullable', new SafeExternalUrl([
                'soundcloud.com', 'spotify.com', 'cdn.notrejour.tn',
            ])],
            'facebook_url' => ['nullable', new SafeExternalUrl(['facebook.com'])],
            'instagram_url' => ['nullable', new SafeExternalUrl(['instagram.com'])],
        ])->validate();
    }
}
