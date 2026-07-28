# PHASE_1.md — Socle métier MVP (schéma final, décisions, liste de fichiers)

Ce document consolide le schéma et les décisions techniques validées au fil des trois itérations de
cadrage (v1 → v2 → v3) avant l'implémentation, conformément à la règle de gouvernance du projet. Il
sert de référence de contrôle : toute divergence avec ce qui a été explicitement validé doit être
signalée.

**Périmètre** : local uniquement. Aucune modification de `.env` (local ou VPS), aucune migration
exécutée sur le VPS, aucun module optionnel activé, aucun accès à Nginx/PHP/Composer global/serveur.

---

## 1. Schéma de données final

### `clients` (module Orders)

| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar | |
| whatsapp_phone | varchar | valeur brute saisie par l'opérateur |
| whatsapp_phone_normalized | varchar unique indexé | chiffres uniquement + indicatif pays, convention E.164 sans `+` (ex. `21698123456`), identique à `config/whatsapp.php` `phone_e164`. Calculée par `ClientObserver::saving()`, jamais saisie directement. Sert de clé de déduplication. |
| notes | text nullable | |
| timestamps | | |

### `template_categories` (module Templates)

| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar | |
| slug | varchar unique | |
| order | int default 0 | ordre d'affichage |
| timestamps | | |

### `templates` (module Templates)

| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| template_category_id | FK `template_categories` `restrictOnDelete` | une catégorie utilisée ne peut pas être supprimée |
| name | varchar | |
| slug | varchar unique | |
| description | text nullable | |
| preview_image_path | varchar nullable | chemin disque (S3 en prod) |
| demo_data | json nullable | |
| is_active | boolean default true | |
| order | int default 0 | |
| timestamps | | |

### `orders` (module Orders)

| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| reference | varchar unique indexé | `NJ-{annee}-{sequence}`, génération race-safe (voir §4) |
| client_id | FK `clients` `restrictOnDelete` | remplace `customer_name`/`customer_phone` (v2 pt. 3) |
| template_id | FK `templates` nullable `nullOnDelete` | modèle discuté/choisi côté commande — **conservé aux côtés de `invitations.template_id`** (v2 pt. 6) |
| subtotal | decimal(10,3) | TND = 3 décimales (millimes) |
| discount | decimal(10,3) default 0 | |
| total | decimal(10,3) | validé = `subtotal - discount` par `OrderObserver` |
| paid_amount | decimal(10,3) default 0 | |
| currency | varchar(3) default `TND` | jamais `DT` en base (v2 pt. 2) |
| payment_method | varchar nullable | especes, virement, autre |
| paid_at | timestamp nullable | |
| status | varchar (enum `OrderStatus`) | nouveau, contact_whatsapp, paye, en_production, publie, livre, annule |
| notes | text nullable | |
| created_by | FK `users` nullable `nullOnDelete` | |
| timestamps | | |

### `invitations` (module Invitations)

| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| order_id | FK `orders` indexé, **non unique**, `cascadeOnDelete` | 1 commande → N invitations (v2 pt. 4) |
| template_id | FK `templates` nullable `nullOnDelete` | copié depuis `order->template_id` à la création si non fourni explicitement (règle « copy-on-create », v2 pt. 6) |
| public_token | varchar(26) unique indexé | `Str::ulid()->toBase32()`, non énumérable (v3 pt. 4) |
| slug | varchar nullable | |
| title | varchar nullable | ex. « Mariage de Leila & Karim » (v2 pt. 5) |
| event_type | varchar default `mariage` | extensibilité future (v2 pt. 5) |
| locale | varchar(5) default `fr` | (v2 pt. 5) |
| timezone | varchar default `Africa/Tunis` | (v2 pt. 5) |
| groom_name / bride_name | varchar | |
| wedding_date | datetime | |
| venue_name / venue_address | varchar nullable | |
| maps_embed_url | varchar nullable | |
| lat / lng | decimal(10,7) nullable | |
| message | text nullable | |
| qr_code_path | varchar nullable | |
| status | varchar (enum `InvitationStatus`) | brouillon, publie, archive |
| published_at | timestamp nullable | jamais réécrit si déjà publié (idempotence, v3 pt. 5) |
| timestamps | | |

### `media` (module Media, polymorphe)

| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| mediable_type / mediable_id | morph | `Relation::enforceMorphMap(['template' => Template::class, 'invitation' => Invitation::class])` — jamais le FQCN brut en base (v2 pt. 7) |
| disk | varchar | disque de stockage réel du fichier |
| path | varchar | |
| type | varchar | image, qr_code, video... |
| original_name | varchar nullable | |
| mime_type | varchar nullable | |
| size | unsigned int nullable | octets |
| order | int default 0 | |
| timestamps | | |

Index composite `[mediable_type, mediable_id]`.

---

## 2. Pas de couche Repository (Phase 1)

Conformément à la correction explicite v2 pt. 8, les services utilisent Eloquent directement. Aucune
interface/implémentation Repository n'est créée pour Templates/Orders/Invitations/Media en Phase 1.

## 3. Stratégie d'enregistrement des Filament Resources — vérifiée (v3 pt. 3)

Investigation menée directement sur le code source réel installé (`vendor/filament/filament/src/`,
Filament 3.3.54) :

- `FilamentManager::registerResources()` est **`@deprecated`**, délègue à
  `getDefaultPanel()->resources()`, et lève une exception si aucun panel par défaut n'est encore résolu.
- `PanelProvider::register()` appelle `Filament::registerPanel(fn (): Panel => $this->panel(Panel::make()))`.
- `Filament\Facades\Filament::registerPanel(Panel|Closure $panel)` (dans la Façade, pas dans
  `FilamentManager`) ne construit **pas** le Panel immédiatement : il enregistre un callback
  `app()->resolving(PanelRegistry::class, fn ($registry) => $registry->register(value($panel)))`, qui ne
  s'exécute que lors de la **première résolution** de `PanelRegistry::class` par le conteneur — un
  moment non garanti par rapport au `boot()` des Service Providers de modules (chargés après
  `AdminPanelProvider` dans `bootstrap/providers.php`).
- `PanelRegistry::register()` appelle immédiatement `$panel->register()`, qui appelle
  `registerLivewireComponents()` — cette méthode **fige** la liste des pages/relations/widgets Livewire
  de chaque Resource déjà présente dans `$panel->resources`. Toute Resource ajoutée après ce point via
  un `->resources()` tardif serait listée par `getResources()` mais **ses pages ne seraient pas
  enregistrées comme composants Livewire** → routes cassées. Dépendre de ce timing pour un
  enregistrement par module serait donc silencieusement dangereux, pas seulement « à vérifier ».

**Décision retenue** (stratégie explicite compatible `AdminPanelProvider`, conforme à la clause de
repli de la consigne v3 pt. 3) : chaque module expose ses Resources/Pages/Widgets sous
`Modules/{Nom}/Filament/{Resources,Pages,Widgets}` ; `AdminPanelProvider::panel()` les découvre
lui-même de façon **synchrone**, pendant la construction du Panel, via :

```php
->discoverResources(in: base_path('Modules/*/Filament/Resources'), for: 'Modules\\*\\Filament\\Resources')
->discoverPages(in: base_path('Modules/*/Filament/Pages'), for: 'Modules\\*\\Filament\\Pages')
->discoverWidgets(in: base_path('Modules/*/Filament/Widgets'), for: 'Modules\\*\\Filament\\Widgets')
```

Le motif `*` est résolu par Symfony Finder (utilisé en interne par `discoverComponents()`), qui accepte
les répertoires glob dans `in()` ; le segment variable du namespace est reconstruit par Filament
lui-même (mécanisme déjà utilisé pour les Clusters/Resources de plugins). Vérifié par lecture directe
de `HasComponents::discoverComponents()`. Aucun module ServiceProvider n'a besoin d'appeler une méthode
Filament : un nouveau module actif est détecté automatiquement (Open/Closed respecté), sans dépendre
d'aucun ordre de boot. `AdminPanelProvider` a été modifié en conséquence (3 lignes ajoutées, voir diff).

Corrige/remplace la documentation antérieure (`ARCHITECTURE.md` §6, Phase 0.5 correctif J) qui
supposait `Filament::registerResources()` fiable depuis les modules — cette hypothèse n'a pas résisté à
la vérification.

## 4. Normalisation et déduplication du téléphone WhatsApp (v3 pt. 1)

`ClientObserver::saving()` calcule `whatsapp_phone_normalized` à partir de `whatsapp_phone` :
suppression de tout caractère non numérique, ajout de l'indicatif `216` si absent (numéro local à 8
chiffres), cohérent avec `config('whatsapp.phone_e164')`. Colonne `unique` en base : toute tentative de
créer un second client avec le même numéro normalisé échoue au niveau DB (`QueryException`), remontée
telle quelle par `CreateOrderService` (pas de capture silencieuse) — le test dédié vérifie ce
comportement plutôt que de dupliquer la logique de déduplication applicative.

## 5. Validation des montants de commande — choix Observer (v3 pt. 2)

Choix retenu : **`OrderObserver::saving()`** (classe dédiée et testable), plutôt qu'un mutateur ou une
méthode `calculateTotal()` implicite sur le modèle. Justification : la validation d'une commande
implique plusieurs champs simultanément (`subtotal`, `discount`, `total`, `currency`) — un mutateur par
attribut ne peut pas voir l'état complet du modèle au moment de l'écriture, alors qu'un Observer
`saving()` reçoit le modèle entier juste avant la requête SQL, avec accès à tous les attributs modifiés
ou non, et suit le même pattern que `ClientObserver`/`TemplateObserver`/`InvitationObserver` (cohérence
architecturale, cf. `ARCHITECTURE.md`). Règles appliquées :

- si `total` n'est pas explicitement fourni, il est calculé (`subtotal - discount`) ;
- si `total` est fourni, il doit être égal à `subtotal - discount` (tolérance 0, montants en millimes
  donc pas d'arrondi flottant) — sinon `InvalidArgumentException` ;
- `discount` ne peut pas dépasser `subtotal` ;
- `currency` par défaut `TND` si absent.

Doublé de règles de validation Filament (formulaire `OrderResource`) pour un retour utilisateur avant
la sauvegarde, et d'un test dédié sur l'Observer lui-même.

## 6. Token public d'invitation (v3 pt. 4)

`public_token = (string) Str::ulid()->toBase32()` — 26 caractères Crockford base32, généré côté
`CreateInvitationService` (pas de valeur par défaut au niveau migration/modèle, pour garder la
génération et sa logique de retry au même endroit que l'écriture, voir §7). 80 bits d'aléa par génération
(hors préfixe temporel) : non énumérable en pratique. Colonne `unique` indexée en base — dernier filet
de sécurité en cas de collision (négligeable statistiquement mais gérée explicitement, voir §7).

## 7. Règles de transaction par service (v2 pt. 9, v3 pt. 6)

| Service | `DB::transaction()` | Détail |
|---|---|---|
| `CreateOrderService::execute()` | **Oui** | Recherche/déduplication du client + calcul de référence + création de la commande sont une seule opération logique multi-écriture. |
| `CreateInvitationService::execute()` | **Non** | Une seule écriture (insertion). La sécurité contre les collisions de `public_token` est assurée par une boucle de tentative (jusqu'à 5) qui regénère un nouvel ULID et recommence l'insertion si l'index unique rejette la ligne (`QueryException` code `23000`), sans transaction explicite — chaque tentative est une opération atomique unique en elle-même. |
| `PublishInvitationService::execute()` | **Non** | Une seule écriture conditionnelle et idempotente : si l'invitation est déjà `publie` avec `published_at` renseigné, aucune écriture n'est déclenchée (retour immédiat du modèle inchangé). |

Génération de référence de commande (`CreateOrderService`) : motif `NJ-{annee}-{sequence sur 4
chiffres}`, calcul du prochain numéro de séquence par comptage des commandes de l'année en cours,
avec re-tentative (jusqu'à 10) en cas de collision détectée soit par pré-vérification, soit par
`QueryException` sur l'insertion (filet de sécurité réellement race-safe, la pré-vérification seule
étant sujette à une fenêtre de concurrence TOCTOU).

## 8. Suppression des médias (v3 pt. 5)

`TemplateObserver::deleting()` et `InvitationObserver::deleting()` suppriment **uniquement les lignes**
`media` liées (`$model->media()->delete()`), jamais les fichiers physiques sur disque. Aucune
suppression de fichier S3/local n'est déclenchée en Phase 1 — réservé à un futur `MediaService` dédié
(non créé maintenant, décision consciente consignée dans `TODO.md`). Ce comportement reste valable même
si un soft-delete est introduit plus tard sur `Template`/`Invitation` (l'Observer se déclenche sur
`deleting()`, pas sur un `forceDeleting()` spécifique — à réévaluer explicitement le jour où le
soft-delete sera ajouté, ce n'est pas fait en Phase 1).

## 9. Liste exacte des fichiers créés/modifiés

### Modifiés
- `app/Providers/Filament/AdminPanelProvider.php` (stratégie de découverte, §3)
- `database/seeders/DatabaseSeeder.php` (4 permissions + rôle admin)
- `TODO.md`, `CHANGELOG.md`, `ARCHITECTURE.md`, `DATABASE.md`

### Modules/Orders
- `database/migrations/xxxx_create_clients_table.php`
- `database/migrations/xxxx_create_orders_table.php`
- `Models/Client.php`, `Models/Order.php`
- `Enums/OrderStatus.php`
- `Observers/ClientObserver.php`, `Observers/OrderObserver.php`
- `database/factories/ClientFactory.php`, `database/factories/OrderFactory.php`
- `Services/CreateOrderService.php`
- `Policies/ClientPolicy.php`, `Policies/OrderPolicy.php`
- `Filament/Resources/ClientResource.php` (+ Pages), `Filament/Resources/OrderResource.php` (+ Pages, RelationManager Invitations)
- `Providers/OrdersServiceProvider.php` (enregistrement Observers)
- `tests/Unit/ClientPhoneNormalizationTest.php`, `tests/Unit/OrderAmountValidationTest.php`
- `tests/Feature/CreateOrderServiceTest.php`
- `tests/Unit/OrderPolicyTest.php`, `tests/Unit/ClientPolicyTest.php`

### Modules/Templates
- `database/migrations/xxxx_create_template_categories_table.php`
- `database/migrations/xxxx_create_templates_table.php`
- `Models/TemplateCategory.php`, `Models/Template.php`
- `Observers/TemplateObserver.php`
- `database/factories/TemplateCategoryFactory.php`, `database/factories/TemplateFactory.php`
- `database/seeders/TemplatesDatabaseSeeder.php`
- `Policies/TemplateCategoryPolicy.php`, `Policies/TemplatePolicy.php`
- `Filament/Resources/TemplateCategoryResource.php` (Simple Resource), `Filament/Resources/TemplateResource.php` (+ Pages)
- `Providers/TemplatesServiceProvider.php` (enregistrement Observer)
- `tests/Unit/TemplatePolicyTest.php`

### Modules/Invitations
- `database/migrations/xxxx_create_invitations_table.php`
- `Models/Invitation.php`
- `Enums/InvitationStatus.php`
- `Observers/InvitationObserver.php`
- `database/factories/InvitationFactory.php`
- `Services/CreateInvitationService.php`, `Services/PublishInvitationService.php`
- `Policies/InvitationPolicy.php`
- `Filament/Resources/InvitationResource.php` (+ Pages)
- `Providers/InvitationsServiceProvider.php` (enregistrement Observer)
- `tests/Feature/CreateInvitationServiceTest.php`, `tests/Feature/PublishInvitationServiceTest.php`
- `tests/Unit/InvitationPolicyTest.php`

### Modules/Media
- `database/migrations/xxxx_create_media_table.php`
- `Models/Media.php`
- `database/factories/MediaFactory.php`
- `Policies/MediaPolicy.php`
- `Filament/Resources/MediaResource.php` (+ Pages)
- `Providers/MediaServiceProvider.php` (morph map)
- `tests/Unit/MediaMorphRelationTest.php`, `tests/Unit/MediaPolicyTest.php`

### tests (racine)
- `tests/Feature/FilamentResourcesRegisteredTest.php`

---

*Ce document est la référence de contrôle de la Phase 1. Tout écart constaté à l'exécution locale
(tests, migrations) doit être signalé et corrigé avant validation finale.*
