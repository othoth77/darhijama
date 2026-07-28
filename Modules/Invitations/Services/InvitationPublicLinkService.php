<?php

namespace Modules\Invitations\Services;

use App\Support\PublicLinks\PublicLinkService;
use Modules\Invitations\Models\Invitation;

class InvitationPublicLinkService
{
    public function __construct(
        private readonly PublicLinkService $publicLinkService,
    ) {}

    public function show(Invitation $invitation): string
    {
        return $this->link('invitations.public.show', $invitation);
    }

    public function qr(Invitation $invitation): string
    {
        return $this->link('invitations.public.qr', $invitation);
    }

    public function rsvp(Invitation $invitation): string
    {
        return $this->link('invitations.public.rsvp', $invitation);
    }

    protected function link(string $routeName, Invitation $invitation): string
    {
        return $this->publicLinkService->forToken(
            $routeName,
            $invitation->public_token,
        );
    }
}
