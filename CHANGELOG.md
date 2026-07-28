# CHANGELOG.md — Notre Jour

Format inspiré de [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/).

## [Unreleased]

### Phase 2.1 — Landing & Templates Completion (2026-07-28)

- Connexion de Landing au catalogue actif via `App\\Contracts\\Templates\\TemplateCatalog`.
- Ajout du catalogue public et des détails Template avec recherche, catégories, tri, pagination et démos réelles fondées sur `demo_data`.
- Utilisation de `MediaService` pour toutes les URLs d'aperçu et optimisation du redimensionnement des uploads.
- Ajout des canonical, Open Graph, Schema.org Service/Product, sitemap XML et robots.txt.
- Intégration des mentions légales et de la politique de confidentialité dans le footer.
- Ajout du tracking des vues catalogue/détail et du contexte Template pour les clics WhatsApp.
- Ajout de 8 tests dédiés ; suite complète validée à 107 tests / 372 assertions.
- Compilation Blade, accessibilité structurelle, responsive et build Vite validés.

### Phase 1.5 — Shared UI Components (2026-07-28)

- Ajout de composants Blade partagés pour WhatsApp, médias responsives, partage social, états vides, erreurs, SEO/Open Graph, chargement et confirmations.
- Ajout de fabriques Filament partagées pour statuts, uploads Media, actions publier/archiver/restaurer/copier, confirmations, filtres et colonnes.
- Refactor de Landing, Templates, Invitations et Orders en conservant classes, rendu et workflows existants.
- Ajout des confirmations aux publications/archives et d'un état de chargement au RSVP.
- Ajout de 7 tests dédiés ; suite complète validée à 99 tests / 311 assertions.
- Build Vite de production validé.

### Phase 1.4 — Shared Audit Log & Notification Foundation (2026-07-28)

- Ajout d'un journal d'audit partagé couvrant actions Eloquent administratives, publication, archive, authentification, permissions et Feature Flags.
- Conservation de l'acteur, l'entité, l'action, les changements filtrés, l'horodatage, l'IP et le user-agent.
- Ajout de `NotificationService`, d'une abstraction de file et d'une abstraction de canaux.
- Activation du canal database uniquement ; fondations Email, WhatsApp et Push laissées désactivées.
- Ajout de retries, suivi des échecs, idempotence des demandes et livraisons, et événement `NotificationRequested`.
- Ajout de 9 tests dédiés ; suite complète validée à 92 tests / 276 assertions.

### Phase 1.3 — Shared Events & Analytics (2026-07-28)

- Ajout d'événements partagés pour clics WhatsApp, vues publiques, vues et publications d'invitations, RSVP et commandes.
- Ajout de listeners et d'un `AnalyticsService` réutilisable, sans dépendance entre modules métier.
- Ajout des tables `whatsapp_click_events`, `page_views` et `analytics_events` avec déduplication atomique.
- Implémentation de `POST /analytics/whatsapp-click` avec payload beacon historique, validation et rate limiting.
- Pseudonymisation des visiteurs et exclusion des données personnelles client/RSVP.
- Ajout de 9 tests dédiés ; suite complète validée à 83 tests / 243 assertions.

### Phase 1.2 — Shared QR and Public Links Service (2026-07-28)

- Ajout de `QrCodeService`, service partagé idempotent de génération, régénération atomique, invalidation et nettoyage des QR Codes.
- Ajout de `PublicLinkService` pour centraliser les URLs publiques fondées sur des tokens opaques.
- Ajout des adaptateurs Invitation conservant les routes, tokens, chemins QR et réponses existants.
- Stockage QR local ou S3-compatible délégué au `MediaService`, avec restauration du cache précédent lorsque la promotion du nouveau fichier échoue.
- Ajout de 11 tests dédiés ; suite complète validée à 74 tests / 156 assertions.

### Phase 1.1 — Shared Media Service (2026-07-28)

- Ajout de `MediaService`, point d’entrée unique pour validation, nommage ULID, stockage, suppression,
  existence, URL et réponse de fichiers sur disques locaux ou S3-compatibles.
- Ajout de `StoredMediaFile` et de l’upload atomique avec métadonnées polymorphes et compensation.
- Centralisation des profils `file`, `image`, `audio` et `video` dans la configuration du module Media.
- Refactor du preview Template, des médias publics Invitation, du QR Code et des suppressions historiques.
- Compatibilité conservée : mêmes chemins QR, mêmes colonnes et conservation des fichiers physiques lors
  de la suppression d’un Template ou d’une Invitation.
- Ajout de 7 tests dédiés ; suite complète validée à 63 tests / 118 assertions.

### Phase 0 — Reproductibilité et compatibilité production (2026-07-28)

- Publication des configurations Laravel standard manquantes, dont app, database, cache, queue,
  session, logging, mail, auth, CORS et view.
- Publication de la migration Spatie Permission requise par le seeder et restauration des options de
  configuration nécessaires à l’intégration Gate.
- Normalisation PSR-4/Linux des dossiers module Database/Factories, Seeders, Migrations et Tests.
- Remplacement du wildcard Filament non portable par une découverte générique déterministe des
  répertoires de Resources.
- Alignement de la base MySQL CI sur notrejour_test et ajout de package-lock.json pour npm ci.
- Validation locale : Composer valide, autoload optimisé sans avertissement, migrations et seeders
  complets, 56 tests / 96 assertions, Pint conforme, build Vite et caches production validés.


### Phase 3 — Système complet des invitations publiques (2026-07-20)

Création du cœur du produit : page publique d'invitation accessible via `https://notrejour.tn/i/{public_token}`
(ULID existant réutilisé tel quel, aucune régénération). Aucune fonctionnalité des phases précédentes
modifiée ; Landing non touchée ; aucun test existant modifié.

**Schéma (additif uniquement, aucune table/colonne existante modifiée) :**
- 4 colonnes nullable ajoutées à `invitations` : `dress_code`, `additional_info`, `contact_name`,
  `contact_phone`.
- Nouvelle table `program_steps` (`invitation_id`, `time`, `title`, `description`, `order`).
- Nouvelle table `rsvp_responses` (`invitation_id`, `status`, `name`, `phone`, `guests_count`, `comment`).
- Nouveaux modèles `ProgramStep`, `RsvpResponse` + enum `RsvpStatus` (Présent/Absent, `HasColor`/`HasLabel`,
  même convention que `InvitationStatus`).
- `Invitation` : ajout additif des 4 nouveaux champs à `$fillable` et de deux relations `programSteps()`
  / `rsvpResponses()` — aucune méthode existante modifiée.

**Réutilisation stricte de l'existant (zéro duplication) :**
- Galerie / vidéo / musique : lues depuis la relation polymorphe `invitation->media()` existante, filtrée
  par la colonne `type` (`image`/`video`/`audio`) déjà présente sur `Media` — aucune nouvelle colonne média
  sur `invitations`.
- QR Code : utilise la colonne `invitations.qr_code_path`, présente depuis Phase 1 mais jamais utilisée
  jusqu'ici. Génération à la demande (première requête `/i/{token}/qr`) via `endroid/qr-code`, déjà
  installé. `PublishInvitationService` non modifié — génération volontairement découplée de la publication.
- Compte à rebours : réutilise le composant Alpine `countdown()` existant (`resources/js/app.js`, non
  modifié) ; le calcul des années se fait côté vue (`Math.floor(days / 365)`).
- Lien WhatsApp de partage : réutilise `WhatsAppLinkBuilder` existant.

**Nouveaux fichiers :**
- `Modules/Invitations/Http/Controllers/InvitationPublicController.php` (`show`, `rsvp`, `qrCode`).
- `Modules/Invitations/Services/SubmitRsvpService.php`.
- `Modules/Invitations/Policies/RsvpResponsePolicy.php` (réutilise la permission `invitations.manage`
  existante — aucune nouvelle permission créée).
- `Modules/Invitations/Filament/Resources/InvitationResource/RelationManagers/RsvpResponsesRelationManager.php`
  (lecture + suppression des réponses RSVP depuis la fiche invitation).
- Vue publique premium (`Modules/Invitations/resources/views/public/`) : `show.blade.php` (SEO complet —
  meta description, Open Graph, Twitter Cards, canonical, Schema.org `Event` JSON-LD) + 9 partials
  réutilisables (`hero` avec compte à rebours, `details`, `map`, `program`, `gallery` avec lightbox
  Alpine.js sans dépendance, `media-extra` vidéo/musique, `rsvp`, `share`, `footer`). Même palette et
  typographie que la Landing (Playfair Display / Inter / rose poudré / blanc cassé / doré).
- 3 migrations additives, 2 modèles, 1 enum, 2 factories de test.
- 6 fichiers de tests (`Modules/Invitations/tests/{Feature,Unit}/`) : page publique (200/404 selon statut),
  service RSVP, contrôleur RSVP (validation, 404 si non publiée), QR Code, policy RSVP, enum RSVP.

**Accès public** : seules les invitations au statut `InvitationStatus::Publie` sont visibles ; toute autre
valeur ou tout token inconnu renvoie un 404 générique (aucune fuite d'existence d'une invitation non
publiée).

### Stabilité — Correction de la cause réelle des échecs intermittents (2026-07-19)

**Cause racine identifiée** : `php artisan optimize` (exécuté côté production lors des déploiements)
met en cache `bootstrap/cache/config.php`. Quand un cache de configuration existe, Laravel ne relit
plus `config/*.php` au boot — il désérialise directement le cache — ce qui fait **ignorer
silencieusement** les surcharges `<env>` de `phpunit.xml` (`DB_CONNECTION`/`DB_DATABASE`). Résultat :
`php artisan test` exécutait en réalité la suite contre la base de **production réelle**
(`notrejour`), protégée seulement par le rollback de transaction de `RefreshDatabase` — d'où
l'absence de dégât permanent, mais des échecs de comptage dus aux données réelles déjà présentes
(1 client et 1 média créés précédemment via `DemoDataSeeder` lors de la validation GO LIVE).

Ni les Observers, ni les Services, ni le morphMap, ni `RefreshDatabase`, ni les factories/seeders
n'étaient en cause — tous vérifiés et confirmés corrects.

Corrections apportées :
- `composer.json` : le script `test` exécute désormais systématiquement `artisan config:clear` avant
  `artisan test`, garantissant que les surcharges de `phpunit.xml` sont toujours prises en compte.
- `tests/TestCase.php` : garde-fou ajouté dans `setUp()` — toute tentative d'exécuter les tests contre
  une base de données dont le nom ne se termine pas par `_test` échoue immédiatement avec un message
  explicite, avant toute écriture. Protection structurelle contre toute récidive (même après un futur
  `config:cache` oublié), sans dépendre de la discipline opérationnelle seule.

Aucun test désactivé, aucune assertion modifiée ou supprimée, aucune logique métier touchée.

### Phase 2 — Refonte premium de la Landing Page (2026-07-19)

Refonte complète du rendu visuel de la page d'accueil publique, sans aucune modification de logique
métier, de base de données, de Service, de Model, ni des modules Invitations/Orders/Templates.
Périmètre strictement limité au module `Landing` et aux ressources front-end partagées
(`resources/css/app.css`).

Structure éclatée en partials réutilisables (`Modules/Landing/resources/views/partials/`) :
`header`, `hero`, `pricing`, `features`, `how-it-works`, `gallery`, `faq`, `cta`, `footer`.

Contenu éditorial (avantages, étapes, FAQ, galerie de démonstration, liens de navigation) centralisé
dans `LandingController` sous forme de tableaux structurés — les vues restent de purs gabarits
d'affichage. La galerie de modèles utilise des données statiques dont la forme (`name`/`category`)
est alignée sur les colonnes réelles de `Modules\Templates\Models\Template`, pour un branchement
ultérieur facilité, sans lecture actuelle du module Templates.

Ajouts à `resources/css/app.css` (fichier front-end partagé, pas de logique métier) : tokens de thème
`--color-brand-beige` et `--color-brand-gold` (accent doré discret), `scroll-behavior: smooth`,
`scroll-margin-top` pour les ancres sous le header sticky, règle `[x-cloak]`.

Interactivité en Alpine.js core uniquement (déjà présent en Phase 0, aucune nouvelle dépendance) :
menu mobile, accordéon FAQ, tracking des clics WhatsApp via le composant `whatsappCta` existant.
Icônes via `blade-ui-kit/blade-heroicons` (déjà installé).

Tests : `LandingPageTest` étendu d'un test supplémentaire vérifiant la présence des nouvelles sections
(avantages, étapes, modèles, FAQ), les 3 tests existants restent valides sans modification.

### Landing — Page d'accueil publique (2026-07-19)

Préparé en local pendant la phase GO LIVE pour combler l'absence de route `/` (404 Laravel constaté
lors des tests de VirtualHost). Utilise le module `Landing` déjà scaffoldé en Phase 0 (actif, core MVP)
et le `WhatsAppLinkBuilder` déjà présent dans `app/Support/WhatsApp/` — non modifié, réutilisé tel quel.

Fichiers ajoutés :
- `Modules/Landing/Http/Controllers/LandingController.php`
- `Modules/Landing/resources/views/index.blade.php`
- `tests/Feature/LandingPageTest.php`

Fichier modifié :
- `Modules/Landing/routes/web.php` (route `GET /` décommentée et reliée au contrôleur)

Portée volontairement limitée à la page de vente (argumentaire + CTA WhatsApp + offre 49 DT), en
cohérence avec `config/whatsapp.php`. La page de rendu public d'une invitation (accessible via
`public_token`, ce que verront réellement les invités d'un mariage) est un périmètre distinct, non
couvert par Phase 1, et nécessite des décisions de design/contenu par module — non traité ici.

**Non déployé sur le VPS à ce stade** : fichiers prêts localement, en attente de transfert et
vérification (voir rapport de fin de session).

### Phase 1 — Socle métier MVP (2026-07-18)

Détail complet (schéma, décisions techniques, liste exacte des fichiers) : `PHASE_1.md`. Développement
strictement local, aucune modification de `.env`, aucune migration exécutée sur le VPS, aucun module
optionnel activé.

**Point bloquant levé avant implémentation** : vérification de l'API réelle de Filament 3.3.54
installée (au lieu de supposer `Filament::registerResources()` fiable). Conclusion : cette méthode est
dépréciée et sa fiabilité dépend d'un ordre de résolution du conteneur non garanti par rapport au boot
des modules. `AdminPanelProvider` découvre désormais lui-même les Resources/Pages/Widgets de chaque
module via un motif générique (`Modules/*/Filament/...`), de façon synchrone — voir `ARCHITECTURE.md`
§6 (réécrite) et `PHASE_1.md` §3.

**Ajouté** :
- 6 migrations (`clients`, `template_categories`, `templates`, `orders`, `invitations`, `media`) dans
  les modules Orders/Templates/Invitations/Media.
- 6 modèles Eloquent + enums `OrderStatus`/`InvitationStatus` (`HasLabel`/`HasColor` pour Filament).
  Aucune couche Repository (décision explicite Phase 1).
- 4 Observers dédiés (normalisation/déduplication téléphone, validation des montants de commande,
  suppression des lignes Media uniquement — jamais les fichiers physiques).
- Factories pour les 6 modèles, `TemplatesDatabaseSeeder`, 4 permissions Spatie ajoutées à
  `DatabaseSeeder.php`.
- 3 services métier : `CreateOrderService` (transaction, référence race-safe), `CreateInvitationService`
  (token ULID 26 caractères race-safe, copy-on-create du modèle), `PublishInvitationService`
  (idempotent).
- 6 Policies + 4 permissions Spatie (`templates.manage`, `orders.manage`, `invitations.manage`,
  `media.manage`).
- 6 Filament Resources (`TemplateCategoryResource` en Simple Resource, `TemplateResource`,
  `ClientResource`, `OrderResource` avec RelationManager Invitations, `InvitationResource`,
  `MediaResource`).
- Suite de tests couvrant la déduplication téléphone, la validation des montants, l'unicité de
  référence, les invitations multiples par commande, le copy-on-create, l'idempotence de publication,
  l'unicité du token public, la relation Media polymorphe, les policies et l'enregistrement des
  Filament Resources.

**Déployé sur le VPS de production le 2026-07-19**, à la demande explicite du Product Owner (bascule de
la stratégie "local d'abord" à un déploiement direct, avec sauvegarde préalable des fichiers modifiés
dans `storage/backups/phase1-20260718-175246/`, migrations et seeders exécutés avec succès). Trois
régressions détectées et corrigées pendant ce déploiement (aucune n'était détectable sans exécution
réelle) :

1. **Casse de dossiers** (`Database/Factories`, `Database/Seeders`) : sous Windows (système de fichiers
   insensible à la casse), les fichiers Factory/Seeder créés en visant `Database/Factories` ont été
   silencieusement fusionnés dans un dossier `database/factories` préexistant (minuscule, scaffold
   Phase 0). Sur le VPS (Linux, sensible à la casse), l'autoload PSR-4 ne trouvait plus ces classes.
   Corrigé sur le VPS par renommage (`mv`) vers la casse correcte, sans modification de code.
2. **`AdminPanelProvider`** : les appels génériques `discoverPages()`/`discoverWidgets()` sur
   `Modules/*/Filament/{Pages,Widgets}` faisaient échouer Symfony Finder (`DirectoryNotFoundException`)
   car aucun module n'a de tel dossier pour l'instant — seules les Resources en ont. Ces deux lignes ont
   été retirées (seul `discoverResources()` reste, qui fonctionne car des dossiers correspondants
   existent réellement). À réintroduire quand un module en aura besoin.
3. **`MediaServiceProvider`** : `Relation::enforceMorphMap()` imposait la présence dans la table de
   correspondance à **toute** relation polymorphe de l'application, y compris celle de
   `spatie/laravel-permission` (`model_has_roles` sur `App\Models\User`), faisant échouer
   `php artisan db:seed` à l'attribution du rôle admin. Remplacé par `Relation::morphMap()` (non
   contraignant), qui ne fait que déclarer nos deux alias (`template`, `invitation`) sans affecter les
   autres relations polymorphes de l'application.

Résultat final confirmé sur le VPS : 6 migrations exécutées, seeders exécutés (1 rôle, 4 permissions, 1
compte admin, 3 catégories + 3 modèles de démonstration), 18 routes `admin/*` enregistrées couvrant les
6 Filament Resources, `php artisan about`/`module:list` sans erreur.

**Non exécuté par l'agent en local** (pas d'accès PHP/Composer dans cet environnement) : `php artisan
test` reste à exécuter (localement ou sur le VPS) pour valider la suite de tests Phase 1.

### Phase 0.6 — Validation runtime VPS confirmée + correctif module:list (2026-07-15)

Environnement de référence clarifié : le **VPS de production** (https://notrejour.tn), pas le dossier
local. État confirmé sur le VPS : PHP 8.5.4, Composer 2.10.2, Laravel 12.64, Livewire 3.8.2, Filament
3.3.54, Pennant 1.24, Spatie Permission 6.25 ; `composer install`, migrations socle + Spatie Permission,
seeding, `npm install`, `npm run build`, `php artisan optimize`, `nginx -t`, `storage:link` tous
réussis. Détail complet dans `RUNTIME_VALIDATION.md`.

**Correctif** (`config/modules.php`) : régression identifiée — `'commands' => []` (ajouté en Phase 0.5,
correctif M) court-circuitait `Nwidart\Modules\Providers\ConsoleServiceProvider::defaultCommands()`,
désactivant silencieusement toutes les commandes `module:*` (`list`, `enable`, `disable`, `make-*`).
Ce n'était pas une absence de la commande dans le package (elle existe bien en v11.1.10), mais un effet
de bord de notre propre configuration. Corrigé en reprenant le pattern du stub officiel du package.
**Déployé et confirmé sur le VPS** (`php artisan module:list` → 13/13 modules, 6 `Enabled` / 7
`Disabled`, conforme à `modules_statuses.json`) — voir `RUNTIME_VALIDATION.md` §4.

**Analyse (aucune anomalie, comportement attendu)** : `route:list` ne montre que les routes Laravel et
Filament car chaque `Modules/{Nom}/routes/web.php` ne contient qu'une ligne de route commentée
(scaffolding Phase 0, aucun contrôleur écrit) ; les migrations de modules ne contiennent qu'un
`.gitkeep` (aucune table métier créée, Phase 1 non démarrée). Chargement des providers de module
vérifié correct par lecture du code source réel de `nwidart/laravel-modules`.

### Phase 0.6 — Validation runtime (suite, 2026-07-15)

`composer install` confirmé réussi sur la machine du Product Owner (versions réelles résolues sans
conflit — voir `RUNTIME_VALIDATION.md` §0). Corrections apportées par outil fichier (aucune commande
exécutée) : ajout de `ADMIN_EMAIL`/`ADMIN_PASSWORD` dans `.env.example` (utilisées par
`DatabaseSeeder.php` mais absentes du fichier d'exemple — écart détecté) ; création de `.env` local.
`APP_KEY` laissé vide intentionnellement (à générer via `php artisan key:generate`, seule source de
randomness cryptographique fiable). Suite du protocole (migrations, tests, npm) toujours en attente
d'exécution par le Product Owner.

### Phase 0.6 — Validation runtime (2026-07-15) — BLOQUÉE

Tentative de validation runtime réelle (`composer install`, `php artisan test`, `npm run build`, etc.)
demandée par le Product Owner. **Bloquée avant la première commande** : cet environnement de travail
n'a ni PHP/Composer/MySQL installés, ni accès shell à l'ordinateur réel du Product Owner (l'outil de
contrôle à distance du bureau bloque explicitement la saisie clavier dans les applications
Terminal/IDE, par restriction de sécurité de la plateforme). Aucun binaire non officiel téléchargé,
conformément à la consigne.

Détail complet, liste exacte des prérequis et bloc de commandes à exécuter par le Product Owner :
voir `RUNTIME_VALIDATION.md` (nouveau document). Score de validation runtime : 0/10 (non réalisable
dans cet environnement — pas un échec de code, une absence de vérification). Phase 1 non démarrée.

### Phase 0.5 — Audit et hardening (2026-07-15)

Audit complet du socle Phase 0 (20 points + dette technique/risques/doublons/SOLID) — détail complet
dans `AUDIT_PHASE_0.md`. Corrections appliquées :

- **Correctif A (critique)** : structure des 13 modules aplatie (suppression du dossier `app/`
  intermédiaire) pour corriger une incohérence d'autoload PSR-4 qui aurait empêché l'application de
  démarrer.
- **Correctif B (critique)** : ajout de `config/pennant.php`, manquant — sans lui, les Feature Flags ne
  persistaient jamais réellement en base malgré `PENNANT_STORE=database`.
- **Correctif C** : publication explicite de `config/permission.php`.
- **Correctif D** : enregistrement explicite de `Nwidart\Modules\LaravelModulesServiceProvider`.
- **Correctif E** : `.env.example` — `FILESYSTEM_DISK` par défaut ramené à `public` en local.
- **Correctif F** : suppression de la commande `module:enable` à syntaxe invalide (composer.json +
  INSTALL.md), redondante avec `modules_statuses.json`.
- **Correctif G** : ajout de `tests/TestCase.php` et `phpunit.xml`, absents.
- **Correctif H** : ajout des `.gitignore` imbriqués `storage/**` et `bootstrap/cache/`.
- **Correctif I** : règle de communication inter-modules (Events uniquement) documentée dans
  `ARCHITECTURE.md`/`CLAUDE.md`, préventive contre les dépendances circulaires.
- **Correctif J** : pattern d'auto-enregistrement des Filament Resources par module (au lieu d'une
  découverte centrale dans `AdminPanelProvider`) — corrige une violation Open/Closed.
- **Correctif K** : clarification des responsabilités entre `EnsureUserIsAdmin` et
  `User::canAccessPanel()`.
- **Correctif L** : suppression de `laravel/sail` (dépendance morte) et de code mort dans
  `composer.json`.
- **Correctif M** : ajout de la clé `commands` dans `config/modules.php`.
- Ajout de `.github/workflows/ci.yml` (pipeline minimal).
- Nouveau document `AUDIT_PHASE_0.md` (scores qualité 8/10, architecture 9/10, maintenabilité 8/10,
  évolutivité 9/10, sécurité 7/10, conformité Laravel 9/10 — score global 8,3/10).

### Phase 0 — Socle (2026-07-15)

#### Ajouté
- Bootstrap Laravel 12 (`bootstrap/app.php`, `bootstrap/providers.php`, `artisan`, `public/index.php`).
- `composer.json` déclarant la stack validée : `laravel/pennant`, `filament/filament`,
  `nwidart/laravel-modules`, `spatie/laravel-permission`, `endroid/qr-code`, `intervention/image`,
  `league/flysystem-aws-s3-v3`.
- Configuration `config/features.php` (registre des Feature Flags), `config/whatsapp.php` (numéro et
  message préformaté centralisés), `config/filesystems.php` (disque `s3` OVH), `config/modules.php`
  (nwidart/laravel-modules, structure `app/`).
- 13 modules scaffoldés sous `Modules/` : 6 core actifs (Landing, Templates, Orders, Invitations,
  Admin, Media) et 7 futurs désactivés (RSVP, Guestbook, Timeline, Notifications, AI, Singles, Api),
  chacun avec `module.json`, `composer.json`, `ServiceProvider`, `routes/web.php`, `config/config.php`.
- `App\Support\WhatsApp\WhatsAppLinkBuilder` + helper global `whatsapp_link()`.
- `App\Providers\FeatureFlagServiceProvider` déclarant les flags Pennant depuis `config/features.php`.
- `App\Providers\Filament\AdminPanelProvider` (panneau back-office unique).
- `App\Http\Middleware\EnsureUserIsAdmin` + `App\Models\User` (rôles via spatie/laravel-permission,
  `FilamentUser`).
- Migrations socle Laravel (`users`, `sessions`, `cache`, `jobs`, `failed_jobs`).
- `database/seeders/DatabaseSeeder.php` (rôle admin + premier compte opérateur).
- Front-end : `package.json`, `vite.config.js` (Tailwind v4 + laravel-vite-plugin), `resources/css/app.css`,
  `resources/js/app.js` (Alpine : tracking clic WhatsApp + compte à rebours).
- Documentation vivante : `ARCHITECTURE.md`, `DATABASE.md`, `API.md`, `INSTALL.md`, `ROADMAP.md`,
  `TODO.md`, `CLAUDE.md`, `CHANGELOG.md` (ce fichier).

#### Décisions techniques notables
- Convention `nwidart/laravel-modules` adoptée pour l'arborescence des modules (`Modules/{Nom}/app/...`),
  ajustant la proposition initiale `app/Modules/` du rapport de cadrage sans changer aucune décision
  fonctionnelle (voir `ARCHITECTURE.md` §6).
- Module `Singles` créé en coquille structurelle uniquement (pas de migration, pas d'interface),
  conformément à la décision explicite du Product Owner.
- Aucune migration métier (templates, orders, invitations...) créée à ce stade : réservé à la Phase 1,
  après validation de ce socle.

#### Non vérifié
- `composer install` / `npm install` n'ont pas pu être exécutés dans l'environnement de rédaction
  (absence de PHP/Composer). À valider en priorité sur poste de développement — voir `TODO.md`.
