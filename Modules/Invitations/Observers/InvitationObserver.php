<?php

namespace Modules\Invitations\Observers;

use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Services\InvitationPublicCache;
use Modules\Media\Services\MediaService;

/**
 * Supprime uniquement les lignes Media rattachées (jamais les fichiers physiques
 * sur disque) — voir PHASE_1.md §8. Suppression de fichiers réservée à un futur
 * Le comportement historique est conservé explicitement via MediaService.
 */
class InvitationObserver
{
    public function saved(Invitation $invitation): void
    {
        app(InvitationPublicCache::class)->invalidate($invitation);
    }

    public function deleting(Invitation $invitation): void
    {
        app(MediaService::class)->deleteFor($invitation, deleteFiles: false);
    }
}
