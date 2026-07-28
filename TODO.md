# TODO.md — Notre Jour

## Phase 2.1 — Landing & Templates Completion : TERMINÉE ✅

- [x] Landing connectée au catalogue Templates par contrat partagé.
- [x] Catalogue et détails publics avec recherche, catégories, tri, pagination et `demo_data`.
- [x] URLs Media centralisées, images dimensionnées/lazy et upload redimensionné.
- [x] SEO, Open Graph, canonical, Schema.org, sitemap et robots finalisés.
- [x] Pages légales intégrées et analytics publiques vérifiées.
- [x] Accessibilité, responsive, Blade et Core Web Vitals structurels vérifiés.
- [x] 8 tests dédiés ; suite complète : 107 tests / 372 assertions.
- [x] Build Vite de production validé.

## Phase 1.5 — Shared UI Components : TERMINÉE ✅

- [x] Composants Blade partagés pour CTA, médias, partage, états, formulaires et métadonnées.
- [x] Composants Filament partagés pour statuts, médias, actions, confirmations, filtres et colonnes.
- [x] Landing, Templates, Invitations et Orders refactorés sans modifier leur design.
- [x] Rendu critique et contrats d'actions couverts ; breakpoints responsives vérifiés.
- [x] 7 tests dédiés ; suite complète : 99 tests / 311 assertions.
- [x] Build Vite de production validé.

## Phase 1.4 — Shared Audit Log & Notification Foundation : TERMINÉE ✅

- [x] Audit partagé des créations, modifications, suppressions, restaurations, publications et archives.
- [x] Audit des connexions, déconnexions, permissions et Feature Flags avec contexte de requête.
- [x] Service, file et canaux de notification abstraits avec canal database actif.
- [x] Email, WhatsApp et Push préparés mais désactivés ; retries, erreurs et idempotence couverts.
- [x] Intégration événementielle réutilisable.
- [x] 9 tests dédiés ; suite complète : 92 tests / 276 assertions.

## Phase 1.3 — Shared Events & Analytics : TERMINÉE ✅

- [x] Six événements applicatifs partagés et listeners découplés.
- [x] Analytics centralisées pour clics WhatsApp, pages publiques et cycles métier.
- [x] Endpoint beacon compatible, validation, limitation à 30 requêtes/minute et déduplication.
- [x] Données visiteurs pseudonymisées ; aucune donnée personnelle RSVP/client enregistrée.
- [x] 9 tests dédiés ; suite complète : 83 tests / 243 assertions.

## Phase 1.2 — Shared QR and Public Links Service : TERMINÉE ✅

- [x] Génération QR extraite des contrôleurs et centralisée dans un service partagé idempotent.
- [x] URLs publiques et liens par token centralisés sans modifier les routes ni les tokens existants.
- [x] Stockage local/S3-compatible délégué au `MediaService`.
- [x] Régénération atomique, restauration en cas d'échec, invalidation et nettoyage temporaire.
- [x] Invitation refactorée en conservant les chemins QR historiques.
- [x] 11 tests dédiés ; suite complète : 74 tests / 156 assertions.

## Phase 1.1 — Shared Media Service : TERMINÉE ✅

- [x] Validation centralisée par profils `file`, `image`, `audio` et `video`.
- [x] Nommage ULID, stockage local/S3, suppression, URLs et réponses centralisés.
- [x] Upload atomique avec création optionnelle des métadonnées polymorphes.
- [x] Templates, invitations, QR et observers refactorés sans modifier le comportement historique.
- [x] 7 tests dédiés ; suite complète : 63 tests / 118 assertions.

## Phase 0.6 — validation runtime : TERMINÉE ✅

Environnement de référence : le **VPS de production** (https://notrejour.tn) — voir
`RUNTIME_VALIDATION.md`. Protocole complet confirmé réussi, y compris le correctif
`config/modules.php` (`php artisan module:list` → 13/13 modules, statuts corrects). Score final 9/10.

- [ ] Reste ouvert (non bloquant) : confirmer le choix de visibilité S3 définitif (public vs signé)
      pour les visuels de modèles — le `.env` de production utilise `FILESYSTEM_DISK`/`AWS_*` propres
      au VPS, distincts du `.env` local de développement.

## Phase 1 — socle métier MVP : IMPLÉMENTÉE (en attente de validation runtime locale)

Détail complet (schéma, décisions, liste de fichiers) : `PHASE_1.md`. Résumé :

- [x] Migrations `clients`, `template_categories`, `templates`, `orders`, `invitations`, `media`.
- [x] Modèles Eloquent (`Client`, `TemplateCategory`, `Template`, `Order`, `Invitation`, `Media`) +
      enums `OrderStatus`/`InvitationStatus`. **Pas de couche Repository en Phase 1** (décision v2 pt. 8).
- [x] Observers dédiés (`ClientObserver`, `OrderObserver`, `TemplateObserver`, `InvitationObserver`).
- [x] Factories + `TemplatesDatabaseSeeder` + 4 permissions Spatie dans `DatabaseSeeder.php`.
- [x] Services : `CreateOrderService`, `CreateInvitationService`, `PublishInvitationService`.
- [x] Policies (6) + permissions `templates.manage`/`orders.manage`/`invitations.manage`/`media.manage`.
- [x] Filament Resources : `TemplateCategoryResource` (Simple), `TemplateResource`, `ClientResource`,
      `OrderResource` (+ RelationManager Invitations), `InvitationResource`, `MediaResource` —
      découverte générique déterministe des dossiers réels par `AdminPanelProvider` (`ARCHITECTURE.md` §6).
- [x] Tests (dédup téléphone, validation montants, unicité référence, invitations multiples,
      copy-on-create, idempotence publication, unicité token, relation Media polymorphe, policies,
      Filament Resources enregistrées).
- [x] Validation locale Phase 0 exécutée le 2026-07-28 : Composer valide, autoload optimisé sans
      alerte PSR-4, migrations et seeders complets sur base isolée, 56 tests / 96 assertions,
      Pint conforme, build Vite production et caches Laravel validés.
- [ ] Validation externe restante : exécuter le pipeline MySQL/Linux et le protocole VPS après
      déploiement des corrections de casse et de configuration.
- [ ] Reporté consciemment : `whatsapp_click_events`/`page_views` (Dashboard Analytics), authentification
      back-office (déjà en place depuis Phase 0 — `User::canAccessPanel()`), `GenerateQrCodeService`
      (QR code physique, endroid/qr-code non encore branché).

## Reporté consciemment (dette assumée, non bloquant)

- [ ] Docker / Laravel Sail : retiré de `composer.json` en Phase 0.5 (dépendance inutilisée tant
      qu'aucun `docker-compose.yml` n'existe). À réintroduire explicitement quand l'équipe en aura
      besoin (ex. onboarding d'un second développeur), pas avant.
- [ ] CI/CD : pipeline minimal ajouté (`.github/workflows/ci.yml` — validate, Pint, `module:list`,
      build front, tests). À enrichir en Phase 5 (Larastan, audit sécurité automatisé).

## Décisions produit en attente

- [ ] Périmètre fonctionnel du module Singles Corner (aucun développement au-delà de la coquille tant
      que non spécifié).
- [ ] Charte graphique définitive (couleurs `resources/css/app.css` actuellement provisoires).
- [ ] Politique de rétention des `whatsapp_click_events` / `page_views` (RGPD-like, durée de
      conservation).

## Dette technique assumée (à ne pas oublier)

- Aucune pour l'instant — le projet démarre. Toute dérogation temporaire aux règles de
  `ARCHITECTURE.md` doit être consignée ici avec justification et date de résorption prévue.
