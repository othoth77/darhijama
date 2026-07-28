<?php

namespace Modules\Invitations\Policies;

use App\Models\User;
use Modules\Invitations\Models\RsvpResponse;

/**
 * Réutilise la permission "invitations.manage" existante (aucune nouvelle
 * permission créée — voir DatabaseSeeder.php, Phase 1, non modifié). La
 * soumission publique d'un RSVP (visiteur non authentifié) ne passe jamais
 * par cette policy : elle ne gouverne que la consultation/gestion admin
 * (Filament RelationManager, tâche Phase 3 #42).
 */
class RsvpResponsePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('invitations.manage');
    }

    public function view(User $user, RsvpResponse $rsvpResponse): bool
    {
        return $user->can('invitations.manage');
    }

    public function delete(User $user, RsvpResponse $rsvpResponse): bool
    {
        return $user->can('invitations.manage');
    }

    public function update(User $user, RsvpResponse $rsvpResponse): bool
    {
        return $user->can('invitations.manage');
    }
}
