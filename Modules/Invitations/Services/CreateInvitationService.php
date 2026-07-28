<?php

namespace Modules\Invitations\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Models\Invitation;
use Modules\Orders\Models\Order;
use RuntimeException;

/**
 * Pas de DB::transaction() ici (v3 pt. 6) : une seule écriture. La sécurité
 * contre les collisions de public_token est assurée par une boucle de
 * ré-tentative (nouvel ULID + nouvelle tentative d'insertion), chaque essai
 * étant une opération atomique unique en elle-même (PHASE_1.md §7).
 */
class CreateInvitationService
{
    /**
     * @param  array{
     *     template_id?: ?int,
     *     slug?: ?string,
     *     title?: ?string,
     *     event_type?: string,
     *     locale?: string,
     *     timezone?: string,
     *     groom_name: string,
     *     bride_name: string,
     *     wedding_date: string,
     *     venue_name?: ?string,
     *     venue_address?: ?string,
     *     maps_embed_url?: ?string,
     *     lat?: ?float,
     *     lng?: ?float,
     *     message?: ?string,
     * }  $data
     */
    public function execute(Order $order, array $data): Invitation
    {
        // Règle "copy-on-create" (v2 pt. 6) : le modèle de l'invitation est copié
        // depuis celui de la commande si non fourni explicitement.
        $templateId = array_key_exists('template_id', $data)
            ? $data['template_id']
            : $order->template_id;

        $attributes = [
            'order_id' => $order->id,
            'template_id' => $templateId,
            'slug' => $data['slug'] ?? null,
            'title' => $data['title'] ?? null,
            'event_type' => $data['event_type'] ?? 'mariage',
            'locale' => $data['locale'] ?? 'fr',
            'timezone' => $data['timezone'] ?? 'Africa/Tunis',
            'groom_name' => $data['groom_name'],
            'bride_name' => $data['bride_name'],
            'wedding_date' => $data['wedding_date'],
            'venue_name' => $data['venue_name'] ?? null,
            'venue_address' => $data['venue_address'] ?? null,
            'maps_embed_url' => $data['maps_embed_url'] ?? null,
            'lat' => $data['lat'] ?? null,
            'lng' => $data['lng'] ?? null,
            'message' => $data['message'] ?? null,
            'status' => InvitationStatus::Brouillon,
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $attributes['public_token'] = (string) Str::ulid()->toBase32();

            try {
                return Invitation::create($attributes);
            } catch (QueryException $exception) {
                if (! $this->isUniqueViolation($exception)) {
                    throw $exception;
                }

                // Collision extrêmement improbable (80 bits d'aléa par ULID) : nouvel essai.
                continue;
            }
        }

        throw new RuntimeException(
            'Impossible de générer un token public unique après plusieurs tentatives.'
        );
    }

    protected function isUniqueViolation(QueryException $exception): bool
    {
        return $exception->getCode() === '23000';
    }
}
