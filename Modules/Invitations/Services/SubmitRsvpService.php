<?php

namespace Modules\Invitations\Services;

use App\Events\RsvpSubmitted;
use Illuminate\Support\Str;
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
    public function execute(
        Invitation $invitation,
        array $data,
        ?string $visitorHash = null,
        ?string $correctionToken = null,
    ): RsvpResponse {
        $identityHash = hash_hmac('sha256', implode('|', [
            Str::lower(trim($data['name'])),
            preg_replace('/\D+/', '', $data['phone'] ?? ''),
            $visitorHash ?? '',
        ]), config('app.key'));

        $response = filled($correctionToken)
            ? $invitation->rsvpResponses()->where('correction_token', $correctionToken)->first()
            : null;
        $response ??= $invitation->rsvpResponses()->firstOrNew(['identity_hash' => $identityHash]);

        $response->fill([
            'identity_hash' => $response->exists ? $response->identity_hash : $identityHash,
            'correction_token' => $response->correction_token ?: Str::random(64),
            'status' => $data['status'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'guests_count' => $data['guests_count'] ?? 0,
            'comment' => $data['comment'] ?? null,
            'submissions_count' => $response->exists ? $response->submissions_count + 1 : 1,
            'last_submitted_at' => now(),
        ]);
        $response->save();

        RsvpSubmitted::dispatch($response->id, $invitation->id);

        return $response;
    }
}
