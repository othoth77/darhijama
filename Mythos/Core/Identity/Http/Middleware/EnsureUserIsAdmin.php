<?php

namespace Mythos\Core\Identity\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garde d'accès générique, réservée aux routes HORS panneau Filament
 * (ex. futures routes internes du module Api, webhooks). Le panneau
 * back-office Filament (`admin/*`) est protégé par sa propre logique
 * `User::canAccessPanel()` (voir `app/Models/User.php`) — ne pas dupliquer
 * ce middleware sur les routes Filament, cf. AUDIT_PHASE_0.md correctif L
 * (une seule source de vérité par contexte pour éviter la dérive).
 *
 * Un seul rôle "admin" au MVP, mais posé sur spatie/laravel-permission dès le
 * départ pour accueillir des rôles futurs (ex. opérateur commercial vs.
 * super-admin) sans migration lourde.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(
            $request->user()?->hasRole('admin'),
            403,
            "Accès réservé à l'équipe Notre Jour."
        );

        return $next($request);
    }
}
