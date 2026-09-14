# ARCHITECTURE.md — Notre Jour

> Mythos OS Core boundaries and dependency rules are defined in
> `docs/MYTHOS_OS_ARCHITECTURE.md`. This document retains Notre Jour domain
> decisions and historical context.

## 1. Principes

- **Architecture modulaire** via `nwidart/laravel-modules` : chaque domaine métier est un module
  autonome sous `Modules/{Nom}/`, avec ses propres routes, vues, migrations, config et Service Provider.
- **Clean Architecture / SOLID** : Contrôleur → Service (cas d'usage) → Repository (interface) →
  Eloquent (implémentation). Un Contrôleur ne connaît jamais Eloquent directement.
- **Repository Pattern** : une interface `{Entity}RepositoryInterface` par agrégat métier sous
  `Repositories/Contracts/` (à la racine du module, sans dossier `app/` intermédiaire — voir §8), liée
  à son implémentation Eloquent dans le `ServiceProvider` du module (Dependency Inversion).
- **Service Layer** : la logique métier (créer une commande, publier une invitation, générer un QR
  code) vit dans des classes `Services/*Service.php`, testables indépendamment de HTTP.
- **Feature Flags (Laravel Pennant)** : chaque module futur est chargé au niveau structurel (nwidart,
  via `modules_statuses.json`) mais reste fonctionnellement invisible tant que son flag Pennant
  (`config/features.php`) n'est pas activé.

## 2. Couches, par module

| Couche | Rôle | Exemple |
|---|---|---|
| Http | Contrôleurs, Form Requests | `InvitationController`, `StoreOrderRequest` |
| Services | Cas d'usage métier | `PublishInvitationService`, `CreateOrderService` |
| Models | Eloquent, règles métier | `Invitation`, `Order` |
| Repositories | Interfaces + implémentations Eloquent | `InvitationRepositoryInterface` / `EloquentInvitationRepository` |
| Policies | Autorisations (spatie/laravel-permission) | `InvitationPolicy` |
| Infrastructure transverse | S3 (Media), WhatsApp, QR, Maps | `Mythos\Core\WhatsApp\WhatsAppLinkBuilder` |

### Service Media partagé

`Mythos\Core\Media\Services\MediaService` est l’unique point d’entrée pour valider, nommer, stocker,
supprimer et exposer les URLs des fichiers. Il accepte les disques locaux Laravel et les disques
S3-compatibles, crée optionnellement les métadonnées polymorphes et compense l’upload si leur création
échoue. Les profils de validation et le disque par défaut sont centralisés dans
`Modules/Media/config/config.php`.

La suppression historique d’un Template ou d’une Invitation conserve les fichiers physiques : les
observers passent explicitement `deleteFiles: false`. Les nouveaux flux qui possèdent le cycle de vie
complet d’un média utilisent `deleteMedia()` ou `deleteFor()` avec suppression physique activée.

## 3. Modules

### Core (actifs dans le MVP)

| Module | Rôle |
|---|---|
| Landing | Page d'accueil, argumentaire, CTA WhatsApp |
| Templates | Galerie de modèles + pages de démonstration |
| Orders | Suivi des commandes créées manuellement par les opérateurs |
| Invitations | Création, publication, affichage public des invitations (QR, Maps, countdown) |
| Admin | Back-office Filament, auth, policies transverses, Dashboard Analytics |
| Media | Upload/optimisation/stockage S3 — infrastructure active dès le MVP |

### Futurs (structure présente, désactivés par Feature Flag)

| Module | Flag Pennant |
|---|---|
| RSVP | `rsvp` |
| Guestbook | `guestbook` |
| Timeline | `timeline` |
| Notifications | `notifications` |
| AI | `ai_album` |
| Singles | `singles_corner` (coquille vide — périmètre non défini) |
| Api | `public_api` |

## 4. Flux principal (MVP)

1. Visiteur → Landing → Galerie de modèles → Démonstration → clic CTA WhatsApp (tracké, voir
   `whatsapp_click_events`).
2. Échange WhatsApp hors plateforme : le client transmet ses informations.
3. Opérateur crée la Commande dans le back-office (`orders`, statut `nouveau`).
4. Opérateur crée l'Invitation liée à la Commande (`invitations`, statut `brouillon`).
5. Opérateur publie l'Invitation : génération `public_token` (non énumérable) + QR code (`endroid/qr-code`),
   statut `publie`.
6. Le lien public `/i/{public_token}` est renvoyé au client par WhatsApp.
7. Toute modification repasse par WhatsApp → back-office.

## 5. Communication inter-modules (garde-fou anti-dépendances circulaires)

Un module ne doit **jamais** importer directement une classe interne (Model, Repository, Service)
d'un autre module. Deux mécanismes autorisés seulement :

1. **Events/Listeners** : un module émet un `Illuminate\Foundation\Events\Dispatchable` (ex.
   `OrderCreated`), un autre module écoute depuis son propre `ServiceProvider::boot()`. Aucun couplage
   de compilation entre les deux.
2. **Contrats partagés** sous `app/Contracts/` (racine, hors `Modules/`) quand une interface doit être
   connue de plusieurs modules (rare — à justifier au cas par cas).

Le catalogue public illustre ce contrat : `App\\Contracts\\Templates\\TemplateCatalog` expose uniquement des DTO `TemplateSummary`. Landing ne dépend donc jamais des modèles, services ou repositories internes du module Templates.

Exemple prévu en Phase 1 : `Invitations` ne doit pas appeler `Orders\Models\Order` directement pour
lire son statut — soit il reçoit l'`order_id` en paramètre du Service appelant (Orchestration faite au
niveau du Contrôleur/Service applicatif, pas au niveau modèle), soit il écoute l'event `OrderPaid`.

## 6. Back-office : découverte des Filament Resources par module

**Révisé en Phase 1** (voir `PHASE_1.md` §3) — le pattern décrit précédemment ici (auto-enregistrement
depuis chaque `ServiceProvider::boot()` via `Filament::registerResources()`) n'a pas résisté à la
vérification de l'API réelle installée (Filament 3.3.54) : cette méthode est `@deprecated`, dépend d'un
« default panel » déjà résolu, et surtout — la construction effective du `Panel` (via
`Filament\Facades\Filament::registerPanel()`) est différée jusqu'à la première résolution de
`PanelRegistry::class` par le conteneur, un instant non garanti par rapport à l'ordre de `boot()` des
modules. `PanelRegistry::register()` fige immédiatement la liste des composants Livewire des Resources
déjà présentes : toute Resource ajoutée après ce point serait listée mais avec des pages cassées.

**Stratégie retenue** : chaque module expose ses Resources sous
`Modules/{Nom}/Filament/Resources`. `App\Providers\Filament\AdminPanelProvider::panel()` énumère les
répertoires réels avec `glob(..., GLOB_ONLYDIR)`, calcule le namespace du module, puis appelle
`discoverResources()` pour chaque chemin. Cette résolution explicite est identique sur Windows et
Linux, reste indépendante de l’ordre de boot des Service Providers et ne requiert aucune liste de
modules dans le PanelProvider.

Les Pages propres aux Resources sont découvertes par Filament depuis chaque Resource. Les dossiers
module-level `Filament/Pages` et `Filament/Widgets` ne seront ajoutés à la découverte générique que
lorsqu’un module en fournira réellement.
## 7. Analytics événementielles

- Les modules métier émettent uniquement des événements partagés sous `App\\Events`.
- Les listeners transverses délèguent la persistance à `App\\Analytics\\AnalyticsService`.
- Les clics et vues sont dédupliqués par fenêtre temporelle et empreinte HMAC ; ni adresse IP ni user-agent brut ne sont conservés.
- Les événements métier ne stockent que les identifiants techniques nécessaires, sans contenu client ou RSVP.

## 8. Audit et notifications

- `App\\Audit\\AuditService` centralise le journal des actions administratives et filtre les secrets d'authentification.
- Les événements Eloquent, Auth, Pennant, Spatie Permission et les événements métier alimentent l'audit sans couplage inter-module.
- `App\\Notifications\\NotificationService` persiste une livraison idempotente avant de la déléguer à `NotificationQueue`.
- Le canal database est actif. Email, WhatsApp et Push implémentent la même frontière mais restent explicitement désactivés.
- Les jobs de notification utilisent trois tentatives, un backoff progressif et le stockage standard des échecs Laravel.

## 9. Composants UI partagés

- Les composants Blade transverses vivent sous `resources/views/components/shared` et conservent leurs attributs via l'attribute bag Blade.
- Les fabriques Filament transverses vivent sous `App\\Filament\\Components` ; elles configurent les primitives Filament installées sans sous-classer les Resources métier.
- Une extraction requiert plusieurs consommateurs réels ou une frontière d'infrastructure stable comme SEO, upload Media ou confirmations d'actions.
- Les styles de page et mises en page propres aux modules restent dans leurs vues afin d'éviter une abstraction prématurée.

## 10. Sécurité

- URL publique d'invitation basée sur un token opaque (`public_token`, ULID/base62), jamais sur un id
  auto-incrémenté seul.
- Les URLs publiques fondées sur un token passent par `Mythos\Core\PublicLinks\PublicLinkService`.
- Les QR Codes passent par `Mythos\Core\QrCode\QrCodeService` ; les modules fournissent seulement l'URL publique et le chemin historique via un adaptateur local.
- Le fichier QR constitue un cache régénérable : écriture temporaire, sauvegarde de l'ancien fichier, promotion atomique et restauration en cas d'échec.
- Back-office protégé par authentification Filament + rôle `admin` (spatie/laravel-permission),
  extensible à d'autres rôles sans migration lourde.
- HTTPS forcé en production (`AppServiceProvider`).
- Les opérations analytics et les notifications RSVP sont des listeners en queue, exécutés après commit.
- Les réponses HTTP applicatives ajoutent les en-têtes défensifs communs ; Nginx porte la CSP et HSTS en production.
- Les données Schema.org passent par le composant Blade partagé `shared.structured-data`, avec encodage JSON strict.

## 11. Décisions d'ajustement par rapport au rapport d'analyse initial

Le rapport de cadrage (`../Documentation/Notre_Jour_Rapport_Architecture.docx`) proposait une
arborescence `app/Modules/`. Une fois `nwidart/laravel-modules` validé comme package modulaire, la
convention du package a été adoptée : modules à la racine du projet dans `Modules/`.

Deux structures internes sont possibles avec ce package : `Modules/{Nom}/app/Http/...` (avec un dossier
`app/` intermédiaire, nécessitant soit un `composer.json` par module fusionné via
`wikimedia/composer-merge-plugin`, soit une autre stratégie d'autoload) ou `Modules/{Nom}/Http/...` (à
plat). Le scaffolding initial de la Phase 0 avait choisi la première option sans le plugin de fusion
nécessaire, créant une incohérence d'autoload PSR-4 détectée lors de l'audit Phase 0.5 (voir
`AUDIT_PHASE_0.md`, correctif A). Correction appliquée : structure **à plat**
(`Modules/{Nom}/Http`, `Modules/{Nom}/Models`, `Modules/{Nom}/Providers`, `Modules/{Nom}/Services`,
`Modules/{Nom}/Repositories`, `Modules/{Nom}/Policies`), couverte par le mapping unique déjà présent à
la racine de `/composer.json` (`"Modules\\": "Modules/"`), sans dépendance supplémentaire. Aucune
décision fonctionnelle du rapport validé n'est modifiée par cet ajustement.
