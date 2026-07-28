<?php

namespace Modules\Invitations\Services;

use Illuminate\Support\Facades\URL;
use Modules\Invitations\Models\Invitation;

class InvitationPreviewLinkService
{
    public function temporary(Invitation $invitation, int $minutes = 15): string
    {
        return URL::temporarySignedRoute(
            'invitations.preview',
            now()->addMinutes($minutes),
            ['token' => $invitation->public_token],
        );
    }
}
