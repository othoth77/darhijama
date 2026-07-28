<?php

namespace Modules\Invitations\Services;

use App\Events\RsvpSubmitted;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Models\RsvpResponse;

/**
 * Pas de DB::transaction() ici (même convention que CreateInvitationService/
 * PublishInvitationService) : une seule écriture. Un même invité peut
 * soumettre plusieurs réponses (ex. correction) — aucune contrainte d'unicité
 * n'est imposée en Phase 3, conformément au brief ("Enregistrer les réponses
 * proprement"), sans complexifier avec une notion de compte visiteur absente
 * du MVP (pas de comptes clients, voir ARCHITECTURE.md).
 */
class SubmitRsvpService
{
    /**
     * @param  array{
     *     status: string,
     *     name: string,
     *     phone?: ?string,
     *     guests_count?: ?int,
     *     comment?: ?string,
     * }  $data
     */
    public function execute(Invitation $invitation, array $data): RsvpResponse
    {
        $response = $invitation->rsvpResponses()->create([
            'status' => $data['status'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'guests_count' => $data['guests_count'] ?? 0,
            'comment' => $data['comment'] ?? null,
        ]);

        RsvpSubmitted::dispatch($response->id, $invitation->id);

        return $response;
    }
}
