<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Back-office unique de la plateforme : Gestion des modèles, des commandes,
 * des invitations, du Dashboard Analytics (clics WhatsApp) et des Feature Flags.
 *
 * Stratégie d'enregistrement des Filament Resources par module (Phase 1, vérifiée
 * contre l'API réelle de Filament 3.3.54 installée — voir PHASE_1.md §3) :
 *
 * `Filament::registerResources()` est @deprecated dans la version installée et
 * dépend d'un "default panel" déjà résolu (sinon exception). Le panel lui-même
 * n'est construit que lorsque `PanelRegistry::class` est résolu pour la première
 * fois par le conteneur (voir `Filament\Facades\Filament::registerPanel()`), ce
 * qui ne garantit AUCUN ordre déterministe par rapport au `boot()` des
 * ServiceProvider de modules (chargés par nwidart/laravel-modules après ce
 * provider dans bootstrap/providers.php). Faire dépendre l'enregistrement des
 * Resources d'un module de ce timing serait fragile et non vérifiable.
 *
 * Choix retenu : chaque module expose ses Resources sous
 * `Modules/{Nom}/Filament/Resources`. Le PanelProvider énumère les répertoires
 * réels puis appelle `discoverResources()` avec le namespace calculé. Cette
 * résolution déterministe fonctionne sur Windows et Linux, ne dépend pas de
 * l’ordre de boot et ne contient aucune liste de modules métier.
 *
 * Les Pages imbriquées dans les Resources sont découvertes par Filament. La
 * découverte module-level des Pages/Widgets sera ajoutée lorsqu’un dossier réel
 * de ce type existera. */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Rose,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets');

        foreach (glob(base_path('Modules/*/Filament/Resources'), GLOB_ONLYDIR) ?: [] as $directory) {
            $module = basename(dirname(dirname($directory)));

            $panel->discoverResources(
                in: $directory,
                for: "Modules\\{$module}\\Filament\\Resources",
            );
        }

        foreach (glob(base_path('Applications/*/Filament/Resources'), GLOB_ONLYDIR) ?: [] as $directory) {
            $application = basename(dirname(dirname($directory)));

            $panel->discoverResources(
                in: $directory,
                for: "Applications\\{$application}\\Filament\\Resources",
            );
        }

        foreach (glob(base_path('Applications/*/Filament/Widgets'), GLOB_ONLYDIR) ?: [] as $directory) {
            $application = basename(dirname(dirname($directory)));

            $panel->discoverWidgets(
                in: $directory,
                for: "Applications\\{$application}\\Filament\\Widgets",
            );
        }

        return $panel
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
