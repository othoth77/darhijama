# HANDOFF — Notre Jour — Phase 3 (Invitations publiques)
**Document de transfert vers Claude Code — état exact au 21/07/2026, ~17h30**

Ce document est destiné à permettre la reprise immédiate du travail dans Claude Code, sans perte de contexte. Il documente : l'état exact du VPS (y compris un bug non résolu à corriger en priorité absolue), tout le code Phase 3 déjà écrit localement, les conventions du projet, et la liste précise des tâches faites/restantes.

---

## 0. Contexte général du projet

**Projet** : Notre Jour — plateforme SaaS-évolutive d'invitations de mariage digitales (Laravel 12, architecture modulaire via `nwidart/laravel-modules`).

**MVP** : invitation digitale à 49 DT (TND), tunnel commercial 100% WhatsApp, aucun paiement en ligne, aucun compte client.

**Domaine** : `notrejour.tn`, en production (GO LIVE terminé, SSL actif).

**VPS** : IP `51.68.226.211` (IPv6 `2001:41d0:367:338::1`), hôte partagé avec 4 autres sites non liés. Racine de l'application : `/var/www/notrejour`.

**Accès VPS** : SSH exclusivement via Termius (utilisateur `ubuntu@vps-4722f0a9`). **Claude Code n'a pas d'accès SSH direct au VPS dans cette configuration actuelle** — tout passe par relais humain (l'utilisateur colle les commandes dans Termius et retourne la sortie). Si Claude Code a un accès shell direct différent, il faut le confirmer avec l'utilisateur avant d'agir.

**Transfert de fichiers vers le VPS** : FileZilla (le heredoc SSH corrompt le contenu dans le terminal de l'utilisateur — bug déjà rencontré et contourné, ne pas y revenir).

**Racine locale du projet** :
`C:\Users\Othman\Desktop\site\Notrejour\NotreJour_MVP_Evolutif\notre-jour`

**Gouvernance stricte imposée par l'utilisateur (Product Owner) — à respecter sans exception :**
- Toujours diagnostiquer la cause précise avant de corriger quoi que ce soit (jamais de correctif à l'aveugle).
- Jamais d'opération destructrice sur la base de données (`migrate:fresh`, `db:wipe` interdits — migrations toujours additives).
- Toujours sauvegarder avant d'écraser des fichiers sur le VPS.
- Corrections minimales, pas de sur-ingénierie.
- Ne jamais casser une fonctionnalité ou un test déjà validé dans une phase précédente.
- Toute nouvelle fonctionnalité doit être accompagnée de nouveaux tests.
- Rapport détaillé à la fin de chaque étape significative.

---

## 1. ⚠️ BLOCAGE ACTUEL SUR LE VPS — À CORRIGER EN PREMIER, AVANT TOUTE AUTRE ACTION

### 1.1 Résumé du problème

Le VPS est actuellement dans un **état intermédiaire cassé**. Si `composer test` est lancé maintenant sur le VPS, il échouera — pas seulement sur les tests Phase 3, mais potentiellement sur des tests Phase 1 déjà validés (`CreateInvitationServiceTest`, `PublishInvitationServiceTest`, etc.), car la classe `InvitationFactory` n'est actuellement plus chargeable.

### 1.2 Cause racine exacte (diagnostiquée avec certitude, pas une supposition)

Les factories du module Invitations déclarent le namespace :
```php
namespace Modules\Invitations\Database\Factories;
```
(avec `D` et `F` majuscules — convention PSR-4/PSR-12 standard).

Mais le dossier physique réel sur le VPS est :
```
Modules/Invitations/database/factories/
```
(avec `d` et `f` minuscules).

Sur Linux (système de fichiers sensible à la casse), Composer — quand il génère la classmap optimisée (`composer dump-autoload -o`) — calcule le chemin attendu **en respectant exactement la casse du namespace déclaré dans le fichier**, via la règle PSR-4 racine du `composer.json` (`"Modules\\": "Modules/"`). Le chemin calculé est donc `Modules/Invitations/Database/Factories/...` (majuscules), qui ne correspond pas au dossier physique réel (minuscules) → la classe est introuvable.

**Pourquoi ça « marchait » avant pour `InvitationFactory` ?** Un ancien dossier `Modules/Invitations/Database/Factories/` (majuscules) — résidu du déploiement Phase 1 initial (daté du 18 juillet) — contenait encore une copie de `InvitationFactory.php`. Composer indexait cette copie-là (au bon chemin, casse correcte), ce qui masquait complètement le bug. Ce dossier legacy a été identifié et renommé en `_legacy_Database_backup` pendant le diagnostic (pas supprimé, réversible) — ce qui a **révélé** que le vrai mécanisme ne fonctionnait pas correctement.

**Ce même type de bug de casse est un bug déjà connu sur ce projet** (mentionné dans les décisions d'architecture Phase 0/1 comme risque identifié) — il affecte potentiellement d'autres modules (Media, Orders, Templates...) de la même façon, mais ceux-ci fonctionnent probablement aussi *par accident*, via leurs propres résidus legacy jamais nettoyés. **Ne pas toucher aux autres modules maintenant** — ils fonctionnent, on ne corrige pas ce qui n'est pas cassé sans un mandat explicite. Mais il faut être conscient que c'est une dette technique latente sur tout le projet, à traiter un jour méthodiquement (voir section 6).

### 1.3 État exact des fichiers sur le VPS en ce moment précis

```
/var/www/notrejour/Modules/Invitations/database/factories/
  ├── InvitationFactory.php       (présent, non indexé par Composer actuellement)
  ├── ProgramStepFactory.php      (présent, non indexé par Composer actuellement)
  └── RsvpResponseFactory.php     (présent, non indexé par Composer actuellement)

/var/www/notrejour/Modules/Invitations/_legacy_Database_backup/
  └── Factories/
      └── InvitationFactory.php   (ancienne copie, renommée pendant le diagnostic — à supprimer une fois le fix confirmé, ou à garder en backup sans risque)

/var/www/notrejour/Modules/Invitations/database/migrations/   ← INTACT, NON CONCERNÉ PAR LE BUG
  (les migrations ne passent pas par le mécanisme namespace/autoload PSR-4 — chargées via
  loadMigrationsFrom() par chemin direct, aucun souci de casse ici, ne rien y toucher)
```

### 1.4 Correction à exécuter en premier (commandes exactes, prêtes à coller dans Termius)

```bash
mkdir -p /var/www/notrejour/Modules/Invitations/Database/Factories
mv /var/www/notrejour/Modules/Invitations/database/factories/InvitationFactory.php /var/www/notrejour/Modules/Invitations/Database/Factories/
mv /var/www/notrejour/Modules/Invitations/database/factories/ProgramStepFactory.php /var/www/notrejour/Modules/Invitations/Database/Factories/
mv /var/www/notrejour/Modules/Invitations/database/factories/RsvpResponseFactory.php /var/www/notrejour/Modules/Invitations/Database/Factories/
rmdir /var/www/notrejour/Modules/Invitations/database/factories
composer dump-autoload -o
grep -n "ProgramStepFactory\|RsvpResponseFactory\|InvitationFactory" vendor/composer/autoload_classmap.php
```

**Résultat attendu du `grep`** : 3 lignes, chacune pointant vers `/Modules/Invitations/Database/Factories/{Nom}Factory.php` (majuscules, cohérent).

Puis vérifier avec la suite complète :
```bash
composer test
```

**Résultat attendu : 100% des tests passent** (34 tests Phase 1/2/Stabilité + tous les nouveaux tests Phase 3 listés en section 3.6). Si un échec persiste, ne pas corriger à l'aveugle — diagnostiquer précisément comme fait jusqu'ici dans cette session (lire le message d'erreur exact, vérifier les chemins, ne pas supposer).

**Une fois confirmé 100% vert**, mettre à jour le suivi de tâche `#44` et rédiger le rapport final `RAPPORT_PHASE3_INVITATIONS.md` (structure identique aux rapports précédents du projet : `RAPPORT_PHASE1_GOLIVE.md`, `RAPPORT_PHASE2_LANDING.md`, `RAPPORT_STABILITE.md`, tous à la racine du projet local, déjà transférés à l'utilisateur).

### 1.5 Étape encore après (importante, à ne pas oublier)

Le dossier local du projet (Windows) a probablement toujours `database/factories/` en minuscules — Windows étant insensible à la casse, cette incohérence ne s'y manifeste jamais, ce qui fait que **le bug reviendra à chaque nouveau déploiement tant que le dossier local n'est pas renommé pour correspondre exactement à la casse attendue** (`Database/Factories/`). À faire dans Claude Code : renommer localement `Modules/Invitations/database/factories/` → `Modules/Invitations/Database/Factories/` (et vérifier si `database/seeders/` a le même souci potentiel avec un futur seeder, même si aucun seeder n'existe encore dans ce module). Idéalement, transférer aussi cette version corrigée vers le VPS pour que le prochain déploiement (ou la mise en place de Coolify/GitHub, voir section 7) reflète la structure correcte dès le départ.

---

## 2. Ce qui a été fait sur le VPS — journal exact de cette session de déploiement

| # | Commande | Résultat |
|---|---|---|
| 1 | Sauvegarde (`storage/backups/phase3-{timestamp}/`) | ✅ OK |
| 2 | Transfert FileZilla de `Modules/Invitations` (dossier complet, overwrite) + `CHANGELOG.md` | ✅ OK (51 fichiers transférés, 5 échecs sur des dossiers vides sans rapport avec Phase 3 — `Http/Middleware`, `Http/Requests`, `Repositories/Contracts`, `database/seeders`, `app` — scaffolds génériques inutilisés, sans impact) |
| 3 | `composer dump-autoload -o` (1ère fois) | ✅ 10 726 classes générées |
| 4 | `php -m \| grep -i gd` | ✅ `gd` présent — l'extension requise par `endroid/qr-code` (génération QR Code) est installée |
| 5 | `php artisan migrate --force` | ✅ 3 migrations Phase 3 appliquées sans erreur (`add_public_page_fields_to_invitations_table`, `create_program_steps_table`, `create_rsvp_responses_table`) |
| 6 | `npm run build` | ✅ Build réussi (`app-D5Lfll2_.css` 50.51 kB, `app-Oq7LabYJ.js` 46.51 kB) |
| 7 | `composer test` (1ère fois) | ❌ 2 échecs : `RsvpResponsePolicyTest > user with permission can delete a rsvp response` et `InvitationPublicPageTest > it displays the program steps` — erreur `Class "...Factory" not found` |
| 8 | `ls -la .../database/factories/` | Confirmé : les 3 fichiers factory sont bien présents physiquement (donc pas un problème de transfert) |
| 9 | `composer dump-autoload -o` (2e fois, sans rien changer d'autre) | ❌ Toujours 2 échecs identiques — donc pas un problème de cache autoload périmé |
| 10 | `php -l` sur les 2 fichiers factory en échec | ✅ Aucune erreur de syntaxe — donc pas un problème de corruption de fichier |
| 11 | `grep` dans `vendor/composer/autoload_classmap.php` et `autoload_static.php` | Révèle que seul `InvitationFactory` est indexé, avec un chemin `.../Database/Factories/...` (majuscules) — indice du vrai problème |
| 12 | `Modules/Invitations/composer.json` (lu localement) | Confirme que ce fichier n'est **pas** utilisé pour l'autoload réel (note explicite dans le fichier lui-même) — écarté comme cause |
| 13 | `ls -la .../Modules/Invitations/Database/Factories/` (majuscules) | Confirme l'existence d'un dossier legacy avec l'ancien `InvitationFactory.php` (daté du 18 juillet), séparé du nouveau dossier minuscule (21 juillet) |
| 14 | `find .../Database -type f` | Confirme que ce dossier legacy ne contient **que** ce seul fichier, rien d'autre — sans risque à déplacer |
| 15 | `mv .../Database → .../_legacy_Database_backup` (renommage réversible, pas suppression) + `composer dump-autoload -o` (3e fois) | ✅ 10 725 classes (une de moins — cohérent avec le retrait du doublon) |
| 16 | `grep` de vérification finale | **Résultat vide — aucune des 3 classes n'est indexée, y compris `InvitationFactory`** → confirme la vraie cause (section 1.2) et révèle que la correction complète reste à appliquer (section 1.4) |

**C'est ici que la session s'est arrêtée**, avant l'exécution des commandes de correction listées en 1.4.

---

## 3. Travail Phase 3 déjà complet en local (code prêt, non modifié depuis, à ne pas refaire)

### 3.1 Objectif de la Phase 3 (rappel du brief original de l'utilisateur)

Créer le système complet des invitations publiques : chaque client possède un lien public élégant `https://notrejour.tn/i/{public_token}` à partager avec ses invités. Contenu : noms des mariés, photo, date/heure, adresse + Google Maps, compte à rebours, galerie photo (lightbox), vidéo optionnelle, musique (lecture manuelle), programme du mariage (étapes), dress code, message de bienvenue, infos complémentaires, coordonnées de contact, RSVP (Présent/Absent + nom/téléphone/accompagnants/commentaire), QR Code, partage (WhatsApp/Facebook/copie de lien), SEO complet (meta/OG/Twitter/canonical/Schema.org Event), accessibilité (ARIA, clavier, contraste). Design : même palette/typographie que la Landing (Playfair Display, Inter, rose poudré/blanc cassé/beige/charbon/doré). Contrainte absolue : ne rien casser des phases précédentes, 100% des tests (anciens + nouveaux) doivent passer.

### 3.2 Schéma de base de données (additif uniquement)

**Colonnes ajoutées à `invitations`** (migration `2026_07_20_100000_add_public_page_fields_to_invitations_table.php`) :
`dress_code` (string, nullable), `additional_info` (text, nullable), `contact_name` (string, nullable), `contact_phone` (string, nullable).

**Nouvelle table `program_steps`** (migration `2026_07_20_100001_create_program_steps_table.php`) :
`id`, `invitation_id` (FK cascade delete), `time` (time, nullable), `title` (string), `description` (text, nullable), `order` (unsignedSmallInteger, défaut 0), timestamps.

**Nouvelle table `rsvp_responses`** (migration `2026_07_20_100002_create_rsvp_responses_table.php`) :
`id`, `invitation_id` (FK cascade delete), `status` (string), `name` (string), `phone` (string, nullable), `guests_count` (unsignedSmallInteger, défaut 0), `comment` (text, nullable), timestamps.

**Colonne réutilisée sans duplication** : `invitations.qr_code_path` existait déjà depuis Phase 1 (jamais utilisée jusqu'ici) — maintenant utilisée pour cacher le chemin du QR Code généré à la demande.

### 3.3 Modèles et enum

- `Modules/Invitations/Enums/RsvpStatus.php` — cases `Present = 'present'`, `Absent = 'absent'`, implémente `HasColor`/`HasLabel` (même convention que `InvitationStatus`).
- `Modules/Invitations/Models/RsvpResponse.php` — `belongsTo(Invitation::class)`, cast `status` → `RsvpStatus::class`.
- `Modules/Invitations/Models/ProgramStep.php` — `belongsTo(Invitation::class)`.
- `Modules/Invitations/Models/Invitation.php` (modifié, additif) : ajout de `dress_code`, `additional_info`, `contact_name`, `contact_phone` à `$fillable` ; ajout des relations `programSteps(): HasMany` (triée par `order`) et `rsvpResponses(): HasMany`. **Aucune méthode existante modifiée.**

**Réutilisation stricte de l'existant (zéro duplication de colonnes) :**
- Galerie photo / vidéo / musique : lues depuis la relation polymorphe déjà existante `invitation->media()`, filtrée par la colonne `type` du modèle `Media` (`image`/`video`/`audio`) — alias morphMap `'invitation'` (déjà enregistré dans `MediaServiceProvider`, non modifié).
- QR Code : `endroid/qr-code` (déjà présent dans `composer.json`, jamais utilisé avant), génération lazy au premier accès à `/i/{token}/qr`, résultat mis en cache via `invitations.qr_code_path`.
- Compte à rebours : réutilise le composant Alpine.js `countdown()` déjà existant dans `resources/js/app.js` (non modifié) — le calcul des années se fait côté vue via `Math.floor(days / 365)`.
- Lien de partage WhatsApp : réutilise `Mythos\Core\WhatsApp\WhatsAppLinkBuilder::make()->link(...)` existant.

### 3.4 Services et policy

- `Modules/Invitations/Services/SubmitRsvpService.php` — `execute(Invitation $invitation, array $data): RsvpResponse`. Pas de `DB::transaction()` (même convention que `CreateInvitationService`/`PublishInvitationService` : une seule écriture). Aucune contrainte d'unicité — un invité peut soumettre plusieurs réponses (pas de notion de compte visiteur dans le MVP).
- `Modules/Invitations/Policies/RsvpResponsePolicy.php` — réutilise la permission Spatie existante `invitations.manage` (aucune nouvelle permission créée). Méthodes `viewAny`, `view`, `delete`.
- Enregistrement dans `Modules/Invitations/Providers/InvitationsServiceProvider.php` (modifié, additif) : ligne ajoutée `Gate::policy(RsvpResponse::class, RsvpResponsePolicy::class);` juste après la ligne existante pour `Invitation::class` (non touchée).

### 3.5 Contrôleur public et routes

`Modules/Invitations/Http/Controllers/InvitationPublicController.php` — 3 actions :
- `show(string $token): View` — cherche l'invitation par `public_token` **ET** `status === InvitationStatus::Publie` (`firstOrFail()` → 404 Laravel natif si non trouvé ou non publiée, aucune fuite d'existence). Charge `media` (triée par `order`) et `programSteps`. Prépare `heroUrl`, `gallery` (URLs), `videoUrl`, `musicUrl` via `Storage::disk($media->disk)->url($media->path)`.
- `rsvp(Request $request, string $token): RedirectResponse` — valide (`status` via `Rule::enum(RsvpStatus::class)`, `name` requis, `phone`/`guests_count`/`comment` optionnels), délègue à `SubmitRsvpService`, `back()->with('rsvp_success', true)->withFragment('rsvp')`.
- `qrCode(string $token): StreamedResponse` — génère le PNG via `Endroid\QrCode\QrCode::create(...)` + `PngWriter` si absent du disque, le stocke dans `storage/app/public/qrcodes/{token}.png`, met à jour `qr_code_path`, sert via `Storage::disk('public')->response(...)`.

Routes ajoutées dans `Modules/Invitations/routes/web.php` (fichier modifié, additif — l'ancien contenu placeholder est conservé en commentaire) :
```php
Route::middleware(['web'])->prefix('i')->name('invitations.public.')->group(function () {
    Route::get('/{token}', [InvitationPublicController::class, 'show'])->name('show');
    Route::post('/{token}/rsvp', [InvitationPublicController::class, 'rsvp'])->name('rsvp');
    Route::get('/{token}/qr', [InvitationPublicController::class, 'qrCode'])->name('qr');
});
```
(Chargé automatiquement — `loadRoutesFrom()` déjà présent dans le ServiceProvider, non modifié.)

### 3.6 Vue publique (Blade) — `Modules/Invitations/resources/views/public/`

- `show.blade.php` — shell HTML complet avec SEO (meta description dynamique, canonical, Open Graph, Twitter Cards, Schema.org `Event` en JSON-LD avec `startDate`, `location`, `image`), `@vite(['resources/css/app.css', 'resources/js/app.js'])` (même build que Landing), inclut les 9 partials ci-dessous.
- `partials/hero.blade.php` — photo de fond (si dispo), noms des mariés, date/heure formatée en français (`Carbon::locale('fr')->isoFormat(...)`), message de bienvenue, **compte à rebours** (réutilise `countdown()` Alpine), CTA "Confirmer ma présence" (ancre `#rsvp`).
- `partials/details.blade.php` — cartes : date/heure, lieu, dress code, contact, informations complémentaires (chacune conditionnelle selon si le champ est rempli).
- `partials/map.blade.php` — iframe Google Maps (si `maps_embed_url`) + bouton "Ouvrir dans Google Maps" (lien direct construit depuis `lat`/`lng` si pas d'embed).
- `partials/program.blade.php` — timeline verticale des `programSteps` (heure/titre/description).
- `partials/gallery.blade.php` — grille de photos + **lightbox Alpine.js 100% maison** (aucune dépendance/plugin ajouté), navigation clavier (flèches, Échap), ARIA (`role="dialog"`, `aria-modal`).
- `partials/media-extra.blade.php` — vidéo optionnelle (`<video controls>`) + bouton flottant musique play/pause (aucune lecture automatique, `<audio>` natif piloté par Alpine).
- `partials/rsvp.blade.php` — formulaire complet (radio Présent/Absent, nom, téléphone, accompagnants, commentaire), affichage des erreurs de validation et du message de succès (`session('rsvp_success')`).
- `partials/share.blade.php` — boutons WhatsApp (réutilise `whatsappCta()` Alpine pour le tracking), Facebook, copier le lien (clipboard API), + image du QR Code.
- `partials/footer.blade.php` — minimal, lien vers `route('landing.index')`.

Palette/typographie : identiques à la Landing, aucune nouvelle classe CSS custom nécessaire (tokens `brand-rose`, `brand-charcoal`, `brand-gold`, etc. déjà dans `resources/css/app.css`, non modifié).

### 3.7 Interface admin (Filament)

`Modules/Invitations/Filament/Resources/InvitationResource/RelationManagers/RsvpResponsesRelationManager.php` — table en lecture seule (nom, statut badge, téléphone, accompagnants, commentaire, date de réception) + action de suppression. Enregistré de façon additive dans `InvitationResource::getRelations()` (méthode ajoutée, rien d'autre modifié dans ce fichier).

### 3.8 Tests (6 fichiers, tous dans `Modules/Invitations/tests/`)

- `Feature/InvitationPublicPageTest.php` — 6 tests : affichage si publiée, 404 si brouillon, 404 si archivée, 404 si token inconnu, affichage du programme, affichage de la galerie.
- `Feature/SubmitRsvpServiceTest.php` — 4 tests : création présent, création absent, `guests_count` par défaut à 0, plusieurs réponses par invitation.
- `Feature/InvitationRsvpControllerTest.php` — 4 tests : soumission réussie (redirect + DB), validation du nom requis, validation du statut, 404 si invitation non publiée.
- `Feature/InvitationQrCodeTest.php` — 2 tests : génération + service du PNG (vérifie `Content-Type: image/png` et présence du fichier via `Storage::fake('public')`), 404 si non publiée.
- `Unit/RsvpStatusTest.php` — 3 tests : couleurs/labels de l'enum, nombre de cases.
- `Unit/RsvpResponsePolicyTest.php` — 3 tests : refus sans permission, autorisation `viewAny` avec permission, autorisation `delete` avec permission (**c'est ce test qui échoue actuellement à cause du bug de casse, section 1**).

Tous suivent exactement les conventions déjà établies dans le projet (`namespace Modules\Invitations\Tests\...`, `use Tests\TestCase;`, `use RefreshDatabase;`, style des tests Phase 1 existants).

---

## 4. Suivi des tâches (Task Tracker) — état exact

| # | Tâche | Statut |
|---|---|---|
| 1–36 | Phase 0, Phase 1, GO LIVE, Landing, Phase 2, Stabilité | ✅ Toutes terminées (non concernées par cette session) |
| 37 | Phase 3 — Lecture de l'existant (Invitations, Media, routes) | ✅ Terminé |
| 38 | Phase 3 — Schéma additif (migrations, models, enum RSVP) | ✅ Terminé (code local) |
| 39 | Phase 3 — Service RSVP + Policy | ✅ Terminé (code local) |
| 40 | Phase 3 — Contrôleur public + routes `/i/{token}` | ✅ Terminé (code local) |
| 41 | Phase 3 — Vue publique premium (partials réutilisables) | ✅ Terminé (code local) |
| 42 | Phase 3 — Filament RelationManager RSVP | ✅ Terminé (code local) |
| 43 | Phase 3 — Tests Feature + Unit | ✅ Terminé (code local, 22 tests écrits) |
| **44** | **Phase 3 — Déploiement VPS + rapport final** | 🔴 **En cours, bloqué sur le bug de casse (section 1) — à débloquer en premier** |

**Reste à faire pour clôturer la tâche #44 :**
1. Exécuter la correction de la section 1.4.
2. Confirmer `composer test` → 100% (34 tests antérieurs + 22 nouveaux = 56 tests attendus au total, à vérifier).
3. Vérifier les routes : `php artisan route:list --path=i`.
4. Vérification fonctionnelle réelle : créer une invitation de test via Filament, la publier, ouvrir `https://notrejour.tn/i/{token}` dans un navigateur, tester le RSVP, tester le QR Code.
5. Renommer localement `database/factories` → `Database/Factories` dans le projet (section 1.5) et re-synchroniser vers le VPS.
6. Rédiger `RAPPORT_PHASE3_INVITATIONS.md` (même structure que les rapports précédents) et le livrer à l'utilisateur.

---

## 5. Conventions techniques établies du projet (à respecter pour toute suite)

- **Stack** : Laravel 12, PHP 8.5 (VPS) / `^8.3` (composer.json), MariaDB 11.8.6, Nginx + PHP-FPM, `nwidart/laravel-modules` v11.1 (structure plate `Modules/{Nom}/...`, sans dossier `app/`).
- **Filament v3.3** — resources découvertes via wildcard dans `AdminPanelProvider` (`discoverResources` seulement — ne jamais ajouter `discoverPages`/`discoverWidgets` en wildcard, cause une `DirectoryNotFoundException`).
- **Tokens publics** : ULID via `Str::ulid()->toBase32()` (26 caractères, Crockford base32).
- **morphMap** : `Relation::morphMap()` (jamais `enforceMorphMap()`, casse `spatie/laravel-permission`) — alias `'template'`, `'invitation'` enregistrés dans `Modules/Media/Providers/MediaServiceProvider.php`.
- **Tailwind CSS v4** — utilise `bg-linear-to-*` (pas `bg-gradient-to-*`, syntaxe v3 obsolète), `@theme` dans `resources/css/app.css` pour les tokens de marque.
- **Alpine.js** — cœur uniquement, **aucun plugin installé** (pas de `x-intersect`, pas de `x-collapse` — utiliser `x-show`/`x-transition` natifs).
- **Tests** : `composer test` = `artisan config:clear` puis `artisan test` (fix de stabilité, voir `RAPPORT_STABILITE.md`) — **ne jamais lancer `php artisan test` seul sans `config:clear` avant**, sous peine de retomber sur le bug de cache de configuration déjà résolu. `tests/TestCase.php` contient un garde-fou qui refuse d'exécuter tout test si la base ne se termine pas par `_test`.
- **Déploiement** : jamais `migrate:fresh` en production, toujours `migrate --force` (additif). Toujours sauvegarder avant transfert FileZilla. Toujours `composer dump-autoload -o` après ajout de nouvelles classes.
- **Documentation** : tous les rapports et le `CHANGELOG.md` sont rédigés en français, avec une rigueur de justification à chaque décision technique (pourquoi telle approche plutôt qu'une autre) — à poursuivre dans ce style pour la suite.

---

## 6. Dette technique identifiée (à traiter plus tard, pas maintenant)

Le bug de casse de la section 1 est probablement présent, de façon dormante, dans les autres modules (`Media`, `Orders`, `Templates`) qui suivent la même convention (`namespace ...\Database\Factories` + dossier physique `database/factories` minuscule). Ils fonctionnent aujourd'hui uniquement parce que chaque module a, comme `Invitations`, un dossier legacy à la bonne casse resté d'un déploiement antérieur. C'est une bombe à retardement : si quelqu'un nettoie ces dossiers legacy sans comprendre pourquoi ils sont là, ou si le projet est un jour redéployé from scratch (nouveau VPS, ou via Coolify/Git — voir section 7), le bug réapparaîtra partout d'un coup. À traiter un jour de façon méthodique et coordonnée (probablement : renommer tous les dossiers `database/factories`/`database/seeders` en `Database/Factories`/`Database/Seeders` dans tous les modules, à la fois en local et sur le VPS, en une seule opération contrôlée, avec sauvegarde et vérification complète de la suite de tests avant/après). **Ne pas faire ça sans validation explicite de l'utilisateur au préalable**, ce n'est pas un correctif « minimal » au sens de la gouvernance du projet.

---

## 7. Note sur le passage prévu à Coolify + déploiement automatique via GitHub

L'utilisateur prévoit de mettre en place Coolify pour automatiser le déploiement via GitHub, afin de gagner du temps par rapport au relais manuel Termius/FileZilla actuel. Points d'attention à soulever avec l'utilisateur avant toute mise en œuvre :

1. **Le VPS héberge 4 autres sites non liés à ce projet.** Coolify prend généralement le contrôle du reverse proxy (Traefik ou Nginx) au niveau du serveur entier — il faut vérifier avec précision que ça ne casse pas la configuration Nginx existante des autres sites avant toute installation.
2. **Le projet n'est probablement pas encore versionné avec Git** (aucune mention de dépôt Git dans toute la session jusqu'ici) — à confirmer avec l'utilisateur, et si ce n'est pas le cas, initialiser le dépôt proprement (avec un `.gitignore` adapté à Laravel : `vendor/`, `node_modules/`, `.env`, `storage/*.key`, etc.) avant de le connecter à Coolify.
3. **Corriger la dette technique de casse (section 1 et 6) doit se faire avant de basculer vers un déploiement Git automatisé** — sinon le bug se reproduira systématiquement à chaque déploiement automatique, sans possibilité de diagnostic manuel étape par étape comme cela a été fait ici.
4. Toute mise en place de Coolify doit suivre la même gouvernance stricte que le reste du projet (validation explicite avant chaque changement impactant, sauvegarde avant toute modification).

---

## 8. Prochaine étape immédiate (résumé en une phrase)

**Exécuter les commandes de la section 1.4 sur le VPS via Termius, confirmer `composer test` à 100%, puis rédiger et livrer `RAPPORT_PHASE3_INVITATIONS.md`.**
