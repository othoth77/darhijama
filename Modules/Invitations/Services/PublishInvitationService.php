<?php

namespace Modules\Invitations\Services;

use Modules\Invitations\Models\Invitation;

class PublishInvitationService
{
    public function __construct(private readonly InvitationWorkflowService $workflow) {}

    public function execute(Invitation $invitation): Invitation
    {
        return $this->workflow->publish($invitation);
    }
}