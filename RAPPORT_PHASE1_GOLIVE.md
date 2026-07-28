# Rapport complet — Phase 1 (Socle métier MVP) & GO LIVE
**Projet** : Notre Jour — plateforme d'invitations de mariage digitales
**Domaine** : notrejour.tn
**Période couverte** : 18–19 juillet 2026
**Statut final** : Site en production, fonctionnel, testé, sécurisé (HTTPS)

---

## 1. Résumé exécutif

Ce rapport documente, avec précision, l'ensemble des actions effectuées depuis l'implémentation locale de la Phase 1 (socle métier MVP) jusqu'à la mise en ligne complète du site en production (GO LIVE), incluant la page d'accueil publique.

Résultat final :
- Backend métier complet (clients, commandes, invitations, médias) déployé et fonctionnel sur `/var/www/notrejour`.
- 33 tests automatisés passés (31 Phase 1 + 2 Landing), exécutés sur une base MySQL de test isolée (`notrejour_test`), sans jamais toucher la base de production.
- Site accessible publiquement en HTTPS sur `https://notrejour.tn`, certificat Let's Encrypt actif, redirection HTTP→HTTPS forcée.
- Panneau d'administration Filament opérationnel sur `https://notrejour.tn/admin`.
- Parcours métier réel validé de bout en bout (client → commande → invitation → média) via les services de domaine réels, avec des données de démonstration.
- Aucune donnée de production supprimée ou réinitialisée à aucun moment. Aucune modification du `.env`, de la configuration PHP ou des permissions Linux existantes en dehors de ce qui était explicitement autorisé.

---

## 2. Phase 1 — Implémentation du socle métier MVP

### 2.1 Schéma de base de données (6 migrations)

| Table | Rôle | Points clés |
|---|---|---|
| `template_categories` | Catégories de modèles d'invitation | — |
| `templates` | Modèles d'invitation | Relation vers catégorie |
| `clients` | Clients (opérateur back-office uniquement) | `whatsapp_phone` (brut) + `whatsapp_phone_normalized` (unique, indexé) |
| `orders` | Commandes | `reference` unique (`NJ-{année}-{séquence sur 4 chiffres}`), `subtotal`/`discount`/`total`/`paid_amount` en `decimal(10,3)`, devise par défaut `TND` |
| `invitations` | Invitations liées à une commande | `public_token` ULID Base32 (26 caractères, unique), copie du `template_id` de la commande si non précisé ("copy-on-create") |
| `media` | Fichiers rattachés (polymorphique) | `mediable_type`/`mediable_id`, pas de clé étrangère native (morph map applicatif) |

### 2.2 Couche applicative

- **Modèles Eloquent** : `TemplateCategory`, `Template`, `Client`, `Order`, `Invitation`, `Media`, avec relations complètes et factories dédiées.
- **Enums** : `OrderStatus` (7 états), `InvitationStatus` (3 états), tous `HasColor`/`HasLabel` pour l'affichage Filament.
- **Observers** :
  - `ClientObserver` : normalisation du numéro WhatsApp à la sauvegarde.
  - `OrderObserver` : calcul et validation du `total` via `bccomp()`/`bcsub()` (précision décimale), rejet si la remise dépasse le sous-total.
  - `TemplateObserver` / `InvitationObserver` : suppression des lignes `Media` associées à la suppression du parent (jamais du fichier physique).
- **Services métier** (aucune couche Repository, Eloquent utilisé directement — décision documentée) :
  - `CreateOrderService::execute()` — transaction DB, déduplication client par téléphone normalisé, génération de référence unique avec retry sur collision.
  - `CreateInvitationService::execute()` — génération de token ULID avec retry sur collision, règle "copy-on-create" du modèle.
  - `PublishInvitationService` — idempotent (ne republie pas une invitation déjà publiée).
- **Policies** (Spatie Permission) : une par ressource (`ClientPolicy`, `OrderPolicy`, `TemplateCategoryPolicy`, `TemplatePolicy`, `InvitationPolicy`, `MediaPolicy`), toutes basées sur des permissions dédiées (`clients.manage`, `orders.manage`, etc.).
- **Filament Resources** : `ClientResource`, `OrderResource` (avec `InvitationsRelationManager` routé via `CreateInvitationService`), `TemplateCategoryResource`, `TemplateResource`, `InvitationResource` (action "Publier" personnalisée), `MediaResource` (lecture seule, pas de création directe).

### 2.3 Stratégie d'enregistrement Filament (décision technique validée)

Investigation du code source de Filament 3.3.54 avant implémentation : `Filament::registerResources()` est dépréciée et sa fiabilité dépend d'un ordre de résolution du conteneur non garanti. Décision retenue : `discoverResources(in: base_path('Modules/*/Filament/Resources'), for: 'Modules\\*\\Filament\\Resources')` dans `AdminPanelProvider`. Vérifiée fonctionnelle en production via `route:list --path=admin` (18 routes admin détectées) et un test dédié (`FilamentResourcesRegisteredTest`).

### 2.4 Tests (31 tests, développés et exécutés localement puis sur le VPS)

Couverture : normalisation téléphone, validation des montants (calcul/rejet), policies (accès autorisé/refusé) pour chaque ressource, services de création (commande, invitation), relation polymorphique média, enregistrement effectif des Filament Resources.

---

## 3. Déploiement de la Phase 1 sur le VPS de production

Décision explicite : déploiement direct sur `/var/www/notrejour` (changement de stratégie initiale "local uniquement"), sous protocole strict fourni : vérification préalable, sauvegarde systématique avant écrasement, jamais de `.env`/Nginx/PHP/permissions modifiés sans autorisation, jamais de commande destructive (`migrate:fresh`, `db:wipe`).

### 3.1 Sauvegarde effectuée avant toute modification

`storage/backups/phase1-20260718-175246/` — copie des 10 fichiers existants qui allaient être modifiés (`AdminPanelProvider.php`, `DatabaseSeeder.php`, 4 `*ServiceProvider.php` de modules, `ARCHITECTURE.md`, `DATABASE.md`, `TODO.md`, `CHANGELOG.md`).

### 3.2 Régressions détectées et corrigées en production (impossibles à anticiper en local)

| # | Problème | Cause | Correction |
|---|---|---|---|
| 1 | Classes `Factories`/`Seeders` introuvables (PSR-4) | Windows (local) insensible à la casse : les dossiers `Database/Factories` créés localement avaient fusionné silencieusement avec des dossiers `database/factories` préexistants en minuscules | `mv` vers la casse correcte sur le VPS (Linux, sensible à la casse), vérifié par `php -l` et `class_exists()` |
| 2 | `DirectoryNotFoundException` sur `composer dump-autoload` | `discoverPages`/`discoverWidgets` pointaient vers des dossiers `Modules/*/Filament/Pages` et `Widgets` inexistants (aucun module n'en a) | Suppression des deux lignes dans `AdminPanelProvider.php` |
| 3 | `No morph map defined for model [App\Models\User]` au seed | `Relation::enforceMorphMap()` s'applique à **toutes** les relations polymorphiques de l'app, cassant `model_has_roles` de Spatie | Remplacé par `Relation::morphMap()` (non contraignant) |
| 4 | `pdo_sqlite` absent du serveur | Extension non installée | Décision utilisateur : base MySQL de test séparée (`notrejour_test`) plutôt qu'installer SQLite |
| 5 | `Command "test" is not defined` | Dépendances `require-dev` (PHPUnit et autres) absentes (installation `--no-dev` en production) | `composer install` (sans `--no-dev`) pour ajouter les 38 paquets de dev nécessaires aux tests |
| 6 | `Call to undefined function bccomp()` | Extension PHP `bcmath` absente — **bug de production réel**, pas seulement un problème de test, car `OrderObserver` (déjà déployé) en dépend | Installation validée par l'utilisateur : `sudo apt install php8.5-bcmath` + `systemctl restart php8.5-fpm` |
| 7 | `Class "Database\Factories\UserFactory" not found` | Fichier jamais créé depuis la Phase 0 (gap historique, jamais détecté avant l'exécution des tests Phase 1) | Création du fichier standard Laravel |
| 8 | Suite de tests bloquée à 29/31 | `FilamentResourcesRegisteredTest.php` (fichier racine) omis lors des instructions de transfert FileZilla | Création directe sur le VPS |

### 3.3 Résultat final des tests Phase 1

```
Tests:    31 passed (44 assertions)
Duration: 2.38s
```
Base de test MySQL dédiée (`notrejour_test`), configurée dans `phpunit.xml` (`DB_CONNECTION=mysql`, `DB_DATABASE=notrejour_test`), utilisateur `notrejour` avec privilèges accordés spécifiquement sur cette base. **Base de production jamais touchée par la suite de tests.**

### 3.4 Migrations et seed exécutés en production

```
php artisan migrate   → 6/6 migrations Phase 1 exécutées (DONE)
php artisan db:seed   → rôle admin, 4 permissions, 1 utilisateur admin, 3 catégories, 3 modèles créés
```

---

## 4. Phase GO LIVE — Mise en ligne du domaine

### 4.1 Vérification de l'infrastructure VPS (avant bascule)

Nginx (actif, 4 autres sites déjà hébergés sur ce serveur), PHP-FPM 8.5 (socket `/run/php/php8.5-fpm.sock`), MariaDB 11.8.6 (active), Laravel (about propre), Filament (routes admin fonctionnelles), permissions `storage/`/`bootstrap/cache` (775, `ubuntu:www-data`), stockage (`public/storage` lié), Certbot (déjà présent, gère 4 certificats existants).

**Constat initial** : aucun VirtualHost Nginx ni certificat SSL n'existaient encore pour `notrejour.tn`.

### 4.2 VirtualHost Nginx créé

Fichier `/etc/nginx/sites-available/notrejour.tn` (symlinké dans `sites-enabled/`), conforme au pattern Laravel standard (`root` sur `public/`, `try_files` vers `index.php`, `fastcgi_pass` vers le socket PHP-FPM 8.5). Création via transfert FileZilla direct (contournement d'un bug récurrent de corruption de heredoc en SSH/Termius rencontré à plusieurs reprises dans ce projet).

### 4.3 Valeurs DNS fournies et propagation confirmée

| Type | Nom | Valeur |
|---|---|---|
| A | `@` | 51.68.226.211 |
| A | `www` | 51.68.226.211 |
| AAAA | `@` | 2001:41d0:367:338::1 |
| AAAA | `www` | 2001:41d0:367:338::1 |

Propagation vérifiée via Google DNS public (`dns.google/resolve`) — confirmée correcte avant toute action SSL. Ces valeurs étaient déjà configurées côté registrar OVH par l'utilisateur.

### 4.4 Certificat SSL et HTTPS forcé

```
sudo certbot --nginx -d notrejour.tn -d www.notrejour.tn --redirect --agree-tos -m othmanhaddad@gmail.com --no-eff-email -n
```
Résultat : certificat émis, déployé automatiquement dans la configuration Nginx, expiration 2026-10-17 (89 jours à la mise en ligne). Redirection HTTP→HTTPS confirmée (`HTTP 301` → `Location: https://notrejour.tn/`). Renouvellement automatique testé (`certbot renew --dry-run` → succès pour `notrejour.tn`, sans impact sur les 4 autres domaines du serveur).

### 4.5 Vérifications finales Artisan

`php artisan optimize`, `about`, `module:list`, `route:list --path=admin` — tous exécutés avec succès en production, aucun état anormal détecté.

---

## 5. Page d'accueil publique (module Landing)

### 5.1 Contexte

Le test de VirtualHost avait révélé que `/` renvoyait un 404 (page Laravel native, pas une erreur serveur) : le module `Landing`, scaffoldé dès la Phase 0 et actif, n'avait jamais reçu de contrôleur ni de vue.

### 5.2 Travail réalisé (en autonomie, sur autorisation explicite de l'utilisateur)

Fichiers créés :
- `Modules/Landing/Http/Controllers/LandingController.php`
- `Modules/Landing/resources/views/index.blade.php` — page de vente (argumentaire, offre 49 DT, CTA WhatsApp, section "Comment ça marche"), reprend la charte graphique déjà définie en Phase 0 (`resources/css/app.css` : palette rose/ivoire/charbon, Playfair Display + Inter)
- `tests/Feature/LandingPageTest.php`

Fichier modifié :
- `Modules/Landing/routes/web.php` (route `GET /` activée)

Réutilisation stricte de l'existant : le `WhatsAppLinkBuilder` (`app/Support/WhatsApp/`) et le helper global `whatsapp_link()`, tous deux déjà présents depuis la Phase 0, n'ont pas été modifiés — seulement consommés via leur API réelle (`WhatsAppLinkBuilder::make()->link()`).

**Périmètre volontairement exclu** : la page de rendu public d'une invitation individuelle (accessible via `public_token`, ce que verront réellement les invités d'un mariage) n'a pas été construite — elle nécessite des décisions de contenu/design propres à chaque modèle, non tranchées à ce stade.

### 5.3 Bug détecté et corrigé lors de la mise en ligne

Le build Tailwind présent sur le VPS (`public/build/`) datait du 18 juillet 09:21, soit avant la création de la page Landing : le scanner JIT de Tailwind n'avait jamais vu les classes utilisées dans le nouveau fichier, produisant une page sans aucun style visuel. Corrigé par `npm run build` (nouveau hash de fichiers : `app-BNSo60kL.css`, 19.4 Ko contre 14.6 Ko avant). Design confirmé visuellement par l'utilisateur après reconstruction.

### 5.4 Tests

```
Tests:    2 passed (5 assertions)
```

---

## 6. Validation fonctionnelle bout en bout (données réelles)

Un script de vérification (`database/seeders/DemoDataSeeder.php`) a exercé le parcours métier complet **via les services de domaine réels** (pas de raccourci Eloquent direct), sur la base de production, avec l'autorisation explicite de l'utilisateur :

```
Commande créée : NJ-2026-0001 (ID 1, total 49.000 TND)
Invitation créée : token 01KXXKD6KH4BFPG8A7XPPQ0G1M (ID 1)
Media créé : ID 1 (rattaché à l'invitation 1)
```

Validé : déduplication/création client par téléphone normalisé, génération de référence de commande, calcul automatique du total par l'Observer (bcmath), génération d'un token ULID de 26 caractères, règle "copy-on-create" du modèle, relation polymorphique Media via l'alias du morph map (`invitation`, pas le nom de classe brut).

---

## 7. État final du système

| Composant | État |
|---|---|
| DNS | Propagé (A + AAAA, `@` et `www`) |
| SSL | Actif, Let's Encrypt, expire 2026-10-17, renouvellement auto validé |
| HTTPS | Forcé (redirection 301 depuis HTTP) |
| Laravel | Production, debug OFF, cache actif |
| Filament (`/admin`) | Opérationnel, HTTP 200 |
| Page d'accueil (`/`) | Opérationnelle, HTTP 200, design conforme |
| MySQL/MariaDB | Active, 10 migrations `Ran` |
| Modules actifs | Admin, Invitations, Landing, Media, Orders, Templates |
| Permissions fichiers | `storage/`, `bootstrap/cache/` en 775 `ubuntu:www-data` |
| Sauvegardes | `storage/backups/phase1-20260718-175246/`, `storage/backups/landing-20260719-160329/` |
| Tests automatisés | 33/33 passés (31 Phase 1 + 2 Landing) |
| Base de production | Jamais réinitialisée ni supprimée |

---

## 8. Liste complète des fichiers créés ou modifiés

### Phase 1 (nouveaux)
`Modules/Templates/{database/migrations,Models,Observers,Database/Factories,Database/Seeders,Policies,Filament/Resources}/...`
`Modules/Orders/{database/migrations,Models,Enums,Observers,Database/Factories,Services,Policies,Filament/Resources}/...`
`Modules/Invitations/{database/migrations,Models,Enums,Observers,Database/Factories,Services,Policies,Filament/Resources}/...`
`Modules/Media/{database/migrations,Models,Database/Factories,Policies,Filament/Resources}/...`
`database/factories/UserFactory.php` (comblement d'un gap Phase 0)
`tests/Feature/FilamentResourcesRegisteredTest.php`
`PHASE_1.md`

### Phase 1 (modifiés)
`app/Providers/Filament/AdminPanelProvider.php`, `database/seeders/DatabaseSeeder.php`, `phpunit.xml`, `ARCHITECTURE.md`, `DATABASE.md`, `TODO.md`, `CHANGELOG.md`, 4× `*ServiceProvider.php` de modules

### GO LIVE / Landing (nouveaux)
`Modules/Landing/Http/Controllers/LandingController.php`, `Modules/Landing/resources/views/index.blade.php`, `tests/Feature/LandingPageTest.php`, `/etc/nginx/sites-available/notrejour.tn` (VPS uniquement)

### GO LIVE / Landing (modifiés)
`Modules/Landing/routes/web.php`, `CHANGELOG.md`

### Vérification (temporaire, non permanent)
`database/seeders/DemoDataSeeder.php` — script de validation fonctionnelle, peut être supprimé du projet s'il n'est plus utile.

---

## 9. Points ouverts / recommandations

1. **Mot de passe admin par défaut** (`admin@notrejour.tn` / `change-me-immediately`) — visible en clair dans le code source, à changer immédiatement si ce n'est pas déjà fait.
2. **Page publique d'une invitation** (rendu via `public_token`) — non traitée, c'est pourtant la fonctionnalité que verront réellement les invités. Nécessite des décisions de design/contenu par modèle.
3. **`PHASE_1.md`** — jamais transféré sur le VPS (non bloquant, documentation locale uniquement).
4. **`DemoDataSeeder.php`** — script de test laissé en place sur le VPS ; à supprimer si non désiré, ou à conserver pour de futures vérifications.
5. **Modules désactivés** (AI, Api, Guestbook, Notifications, RSVP, Singles, Timeline) — hors périmètre MVP, à activer au cas par cas selon la roadmap.
