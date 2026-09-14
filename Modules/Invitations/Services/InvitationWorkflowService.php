<?php

namespace Modules\Invitations\Services;

use App\Events\InvitationPublished;
use DomainException;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Models\Invitation;

class InvitationWorkflowService
{
    public function __construct(private readonly InvitationBusinessValidator $validator) {}

    public function publish(Invitation $invitation): Invitation
    {
        if ($invitation->isPublished()) {
            return $invitation;
        }

        $this->assertTransition($invitation, InvitationStatus::Publie);
        $this->validator->validateForPublication($invitation);

        $invitation->forceFill([
            'status' => InvitationStatus::Publie,
            'published_at' => $invitation->published_at ?? now(),
        ])->save();

        InvitationPublished::dispatch($invitation->id);

        return $invitation;
    }

    public function archive(Invitation $invitation): Invitation
    {
        if ($invitation->status === InvitationStatus::Archive) {
            return $invitation;
        }

        $this->assertTransition($invitation, InvitationStatus::Archive);
        $invitation->forceFill(['status' => InvitationStatus::Archive])->save();

        return $invitation;
    }

    public function restore(Invitation $invitation): Invitation
    {
        if ($invitation->status === InvitationStatus::Brouillon) {
            return $invitation;
        }

        $this->assertTransition($invitation, InvitationStatus::Brouillon);
        $invitation->forceFill([
            'status' => InvitationStatus::Brouillon,
            'published_at' => null,
        ])->save();

        return $invitation;
    }

    public function assertTransition(Invitation $invitation, InvitationStatus $target): void
    {
        $allowed = match ($invitation->status) {
            InvitationStatus::Brouillon => [InvitationStatus::Publie],
            InvitationStatus::Publie => [InvitationStatus::Archive],
            InvitationStatus::Archive => [InvitationStatus::Brouillon],
        };

        if (! in_array($target, $allowed, true)) {
            throw new DomainException(
                "Transition d'invitation invalide : {$invitation->status->value} vers {$target->value}."
            );
        }
    }
}
