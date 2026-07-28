# AUDIT_PHASE_0.md — Audit et hardening du socle (Phase 0.5)

Date : 15 juillet 2026
Périmètre : intégralité du code généré en Phase 0 (bootstrap Laravel 12, 13 modules, configuration,
documentation). Aucune logique métier n'existe encore à ce stade — cet audit porte sur la solidité du
squelette, pas sur des règles métier.

Méthode : revue de code exhaustive fichier par fichier (aucun accès PHP/Composer réel dans cet
environnement — voir « Limite connue » en fin de document), vérification croisée des 20 points demandés,
recherche systématique de dette technique, risques, doublons, responsabilités mal placées, dépendances
circulaires et violations SOLID/Clean Architecture.

---

## 1. Problèmes détectés et corrections effectuées

### A. [CRITIQUE] Incohérence d'autoload PSR-4 entre la structure des modules et `composer.json`

**Problème.** La Phase 0 avait créé chaque module avec un dossier `app/` intermédiaire
(`Modules/Templates/app/Providers/...`) alors que le `composer.json` racine ne déclarait qu'un mapping
simple `"Modules\\": "Modules/"`. Ce mapping suppose une structure à plat
(`Modules/Templates/Providers/...`) : avec le dossier `app/` en plus, **aucune classe de module n'aurait
été trouvée par l'autoloader**, empêchant l'application de démarrer dès la première requête.

**Correction.** Restructuration à plat des 13 modules (`Http`, `Models`, `Providers`, `Services`,
`Repositories`, `Policies` remontés à la racine de chaque module, dossier `app/` supprimé). Le mapping
`"Modules\\": "Modules/"` déjà présent devient alors correct sans dépendance supplémentaire. Alternative
écartée : ajouter `wikimedia/composer-merge-plugin` pour fusionner un `composer.json` par module — jugée
plus complexe et moins robuste pour un gain nul (voir §4, décision D1). `config/modules.php` (chemins du
générateur `artisan module:make-*`) et `ARCHITECTURE.md`/`CLAUDE.md` mis à jour en conséquence.

**Fichiers modifiés.** 13× `Modules/*/{Http,Models,Providers,Services,Repositories,Policies}`,
`config/modules.php`, `ARCHITECTURE.md`, `CLAUDE.md`.

### B. [CRITIQUE] Feature Flags non persistés malgré `PENNANT_STORE=database`

**Problème.** `.env.example` déclarait `PENNANT_STORE=database`, mais aucun `config/pennant.php` n'existait
pour traduire cette variable en configuration réelle du package. Sans ce fichier, Laravel Pennant utilise
silencieusement son store par défaut `array` (en mémoire, jamais persisté) — **l'activation d'un module
futur depuis le back-office n'aurait donc jamais survécu à une requête suivante**, contredisant
directement l'exigence « pilotable depuis le back-office sans redéploiement ».

**Correction.** Création de `config/pennant.php` avec `default => env('PENNANT_STORE', 'database')` et
déclaration explicite du store `database` (table `features`).

### C. Dépendance à des défauts implicites non documentés (spatie/laravel-permission)

**Problème.** Aucun `config/permission.php` n'était publié : le package fonctionne sur ses valeurs par
défaut internes (non versionnées, invisibles dans le dépôt), ce qui contredit l'exigence « aucune
configuration implicite non documentée » d'un projet Enterprise et complique tout audit futur.

**Correction.** Publication explicite de `config/permission.php` (modèles Role/Permission, noms de
tables, cache, `teams => false` documenté comme décision consciente pour le MVP mono-organisation).

### D. Enregistrement du Service Provider `nwidart/laravel-modules` non explicite

**Problème.** Le fonctionnement du chargement des modules reposait entièrement sur la package discovery
automatique de Laravel, sans garde-fou si celle-ci était désactivée ou mal configurée dans l'environnement
de déploiement cible.

**Correction.** Ajout explicite de `Nwidart\Modules\LaravelModulesServiceProvider::class` dans
`bootstrap/providers.php`, avec commentaire justifiant ce choix défensif.

### E. `FILESYSTEM_DISK=s3` par défaut dans `.env.example` sans identifiants

**Problème.** Un environnement local fraîchement cloné n'a par définition aucun identifiant OVH. Démarrer
avec `FILESYSTEM_DISK=s3` par défaut expose au premier lancement local à des erreurs de connexion S3 pour
la moindre opération fichier, dégradant l'expérience développeur dès l'installation.

**Correction.** Défaut ramené à `FILESYSTEM_DISK=public` dans `.env.example`, avec avertissement explicite
de bascule obligatoire vers `s3` avant toute mise en production (`INSTALL.md` mis à jour).

### F. Commande `module:enable` invalide dans `composer.json` et `INSTALL.md`

**Problème.** `php artisan module:enable Landing,Templates,Orders,Invitations,Admin,Media` (syntaxe
virgule) et sa variante espacée documentée dans `INSTALL.md` sont **incompatibles avec la signature réelle
de la commande** (`module:enable {module?}`, un seul nom à la fois) — la commande aurait échoué ou n'aurait
activé qu'un seul module au premier déploiement automatisé.

**Correction.** Suppression de cette étape : `modules_statuses.json` encode déjà l'état initial correct
(6 modules actifs / 7 inactifs), rendant cette commande redondante en plus d'être buggée. `INSTALL.md`
et `composer.json` (`scripts.post-create-project-cmd`, jamais déclenché par le flux réel `composer
install` documenté — code mort) nettoyés en conséquence.

### G. Absence totale de scaffolding de tests

**Problème.** `tests/Feature` et `tests/Unit` existaient comme dossiers vides, mais ni `tests/TestCase.php`
ni `phpunit.xml` n'avaient été créés. **`php artisan test` aurait échoué immédiatement**, rendant
impossible toute vérification automatisée dès la Phase 1.

**Correction.** Ajout de `tests/TestCase.php` (classe de base standard Laravel) et `phpunit.xml`
(suites Unit/Feature/Modules, environnement de test isolé : SQLite mémoire, cache/session/queue en array
ou sync, Pennant en store `array` pour ne pas polluer la base de test).

### H. Dossiers `storage/` et `bootstrap/cache/` invisibles pour Git

**Problème.** Ces dossiers, indispensables au fonctionnement de Laravel (cache de configuration, sessions
fichier, logs), étaient vides après scaffolding. Git ne versionne pas les dossiers vides : un clone frais
du dépôt les aurait tout simplement absents, provoquant des erreurs « chemin introuvable » au premier
`php artisan` exécuté avant que Laravel ne les recrée lui-même (comportement non garanti selon les
commandes).

**Correction.** Ajout des `.gitignore` imbriqués standards Laravel (`storage/app/.gitignore`,
`storage/framework/**/.gitignore`, `storage/logs/.gitignore`, `bootstrap/cache/.gitignore`), avec
négations correctement chaînées (`!public/`, `!private/`, `!cache/`, etc.) pour que Git conserve la
structure de dossiers sans versionner leur contenu.

### I. [Risque futur] Aucune règle explicite contre le couplage inter-modules

**Problème.** Rien n'empêchait, structurellement, qu'un futur `Invitations\Services\X` importe directement
`Modules\Orders\Models\Order`, ouvrant la porte à des dépendances circulaires entre modules à mesure que
la logique métier grossira (risque identifié de façon préventive, aucune occurrence actuelle puisque
aucune classe métier n'existe encore).

**Correction.** Règle documentée formellement dans `ARCHITECTURE.md` §5 et rappelée dans `CLAUDE.md` :
communication inter-modules uniquement via Events/Listeners ou contrats partagés `app/Contracts/`, jamais
par import direct d'une classe interne d'un autre module.

### J. [Violation SOLID — Open/Closed] Responsabilité de découverte des écrans Filament mal placée

**Problème.** `AdminPanelProvider` était conçu pour scanner un unique dossier central
`app/Filament/Resources`. Dans une architecture modulaire où chaque module doit pouvoir posséder ses
propres écrans back-office (`Modules/Orders/Filament/Resources/OrderResource.php`, etc.), cette
conception centralisée aurait obligé à modifier `AdminPanelProvider` à chaque nouveau module — violation
du principe Ouvert/Fermé et responsabilité mal placée (le panel ne devrait pas avoir à connaître chaque
module individuellement).

**Correction.** Pattern documenté et retenu : chaque module s'auto-enregistre auprès de Filament
(`Filament::registerResources([...])`) depuis son propre `ServiceProvider::boot()`, à l'intérieur du
garde-fou `Feature::active()` pour les modules futurs. `AdminPanelProvider` ne conserve la découverte
centrale que pour les écrans réellement transverses (Dashboard Analytics global). Documenté dans
`ARCHITECTURE.md` §6.

### K. [Doublon de responsabilité] Deux sources de vérité pour « qui est admin »

**Problème.** `App\Http\Middleware\EnsureUserIsAdmin` et `App\Models\User::canAccessPanel()` réalisaient
la même vérification (`hasRole('admin')`) sans qu'aucun des deux ne soit désigné comme source de vérité
pour le panneau Filament — risque de divergence future si l'une des deux règles est modifiée sans l'autre.

**Correction.** Rôles clarifiés par commentaire explicite : `canAccessPanel()` reste la seule garde du
panneau Filament ; `EnsureUserIsAdmin` est réservé aux futures routes hors Filament (module Api,
webhooks). Aucune route actuelle n'utilise encore ce middleware (aucune logique métier développée).

### L. Dépendances inutilisées ou configuration morte dans `composer.json`

**Problème.** `laravel/sail` était requis sans qu'aucun `docker-compose.yml` n'existe (dépendance morte) ;
`allow-plugins.pestphp/pest-plugin` autorisait un plugin pour un framework de test (Pest) non utilisé
(le projet utilise PHPUnit) ; les hooks `post-root-package-install` et `post-create-project-cmd` ne se
déclenchent que via `composer create-project`, jamais via le flux `composer install` documenté dans
`INSTALL.md` — code mort trompeur.

**Correction.** `laravel/sail` retiré (Docker explicitement différé, tracé dans `TODO.md`) ; entrée
`pestphp/pest-plugin` retirée ; hooks morts supprimés.

### M. `config/modules.php` : clé `commands` absente

**Problème.** Clé standard du package non déclarée — sans incidence fonctionnelle immédiate (aucune
commande personnalisée n'existe), mais incomplétude par rapport à la configuration de référence du
package, source de confusion lors d'un futur `php artisan vendor:publish --tag=modules-config` qui
écraserait silencieusement ce fichier sans que la différence soit visible en revue de code.

**Correction.** Clé `commands => []` ajoutée avec commentaire.

---

## 2. Points vérifiés sans anomalie (confirmation explicite)

| # | Point audité | Résultat |
|---|---|---|
| 1 | Namespaces | Conformes après correctif A ; `Modules\{Nom}\...` correspond exactement à chaque chemin de fichier |
| 3 | Modules | 13/13 présents, chacun justifié dans `ARCHITECTURE.md` — aucun module inutile détecté |
| 5 | Dépendances Composer | Versions cohérentes avec Laravel 12 / PHP 8.3 (voir « Limite connue » — non exécuté réellement) |
| 9 | Conventions Laravel 12 | `bootstrap/app.php` (nouveau format), routing centralisé, `bootstrap/providers.php` : conformes |
| 10 | PHP 8.3 | Aucune syntaxe incompatible (propriétés typées/`readonly`, promotion de constructeur) |
| 12 | Filament | API v3 correcte (`FilamentUser`, `canAccessPanel`, `PanelProvider`) ; ownership corrigé (§J) |
| 14 | Tailwind v4 | `@import "tailwindcss"` + `@theme` (pas de `tailwind.config.js` legacy) — conforme v4 |
| 15 | Vite | `@tailwindcss/vite` + `laravel-vite-plugin`, versions alignées sur le skeleton Laravel 12 actuel |
| 19 | Structure API (future) | Module `Api` + `API.md` cohérents, aucune route exposée tant que `public_api` est inactif |
| — | Duplication de modules | Aucun module redondant ; `Media` (infra) et futurs modules à médias (Guestbook, Timeline, AI) ont des responsabilités distinctes et complémentaires |
| — | Dépendances circulaires | Aucune actuellement (pas de code métier) ; garde-fou posé préventivement (§I) |

---

## 3. Points explicitement différés (dette assumée, non bloquante)

| Point audité | Statut | Justification |
|---|---|---|
| 17. Structure Docker | Différée | Aucun besoin d'équipe multi-poste identifié au MVP ; dépendance `laravel/sail` retirée plutôt que laissée morte. Tracée dans `TODO.md` |
| 18. Structure CI/CD | Partielle | `.github/workflows/ci.yml` minimal ajouté (validate, Pint, `module:list`, build front, tests) ; enrichissement (analyse statique, audit sécurité) prévu Phase 5 |
| 20. Object Storage | Config prête, non testée | `config/filesystems.php` correct structurellement ; jamais connecté à un vrai bucket OVH dans cet environnement (voir limite connue) |

---

## 4. Décisions techniques (résumé)

- **D1.** Structure de module « à plat » retenue plutôt que `app/` + `composer-merge-plugin`, pour
  minimiser les dépendances et la surface d'erreur (cohérent avec l'exigence « Enterprise, aucun
  raccourci » : moins de mécanique implicite, pas moins de rigueur).
- **D2.** Communication inter-modules restreinte à Events/Listeners ou contrats partagés — posé avant
  toute logique métier pour éviter d'avoir à refactorer après coup.
- **D3.** Chaque module possède la responsabilité de ses propres écrans Filament (auto-enregistrement),
  jamais le `AdminPanelProvider` central.
- **D4.** Docker/Sail explicitement différé plutôt que laissé en dépendance mal utilisée.
- **D5.** `Singles` reste une coquille strictement vide (aucune migration ajoutée pendant cet audit),
  conformément à la décision produit — vérifié qu'aucun correctif n'a introduit de logique cachée.

---

## 5. Scores

| Score | Note /10 | Justification |
|---|---|---|
| **Qualité du code** | 8/10 | Conventions respectées, code commenté et intentionnel ; -2 car non exécuté sur un vrai interpréteur PHP (voir limite connue) |
| **Architecture** | 9/10 | Séparation des couches, Repository/Service Layer, Feature Flags, garde-fous anti-couplage posés avant tout code métier |
| **Maintenabilité** | 8/10 | Documentation vivante complète et à jour, structure modulaire lisible ; -2 en attente de la première vraie exécution pour valider l'absence d'angles morts |
| **Évolutivité** | 9/10 | 13 modules déjà scaffoldés, Feature Flags fonctionnels (post-correctif B), aucun module ne bloque l'ajout du suivant |
| **Sécurité** | 7/10 | Token d'invitation non énumérable posé en conception, rôles/permissions prêts, HTTPS forcé en prod ; note prudente tant qu'aucun test d'intrusion ni revue Phase 5 n'a eu lieu |
| **Conformité Laravel** | 9/10 | Structure Laravel 12 (nouveau `bootstrap/app.php`), packages officiels/reconnus, aucune convention détournée |

**Score global : 8,3/10** — socle sain et corrigé, sous réserve de la vérification réelle listée ci-dessous.

---

## 6. Limite connue de cet audit

Cet environnement de travail ne dispose pas de PHP/Composer/MySQL réels : chaque correctif a été appliqué
par revue de code manuelle (connaissance précise des APIs Laravel 12, Filament v3, Pennant, nwidart/
laravel-modules, spatie/permission) et non par exécution effective (`composer install`, `php artisan
module:list`, `phpunit`). La priorité n°1 avant la Phase 1 reste donc, comme indiqué dans `INSTALL.md` et
`TODO.md` : exécuter ce socle sur une machine de développement réelle et signaler tout écart, qui sera
corrigé immédiatement et en priorité absolue.

---

## 7. Addendum (2026-07-15) — une régression de cet audit confirmée puis corrigée en VPS

La validation runtime réelle sur le VPS de production (voir `RUNTIME_VALIDATION.md`) a révélé que le
correctif M de cet audit (« ajout de la clé `commands` dans `config/modules.php`, absente ») avait en
réalité introduit une régression : `'commands' => []` désactivait silencieusement toutes les commandes
`module:*` du package (`list`, `enable`, `disable`, `make-*`), au lieu de simplement « compléter la
configuration » comme visé. La revue de code manuelle de cet audit n'avait pas anticipé cet effet de
bord précis de `config('modules.commands', $default)`. Corrigé dans `RUNTIME_VALIDATION.md` §3.1 en
reprenant le pattern du stub officiel du package. Cet incident illustre concrètement la limite décrite
ci-dessus : la revue statique, aussi rigoureuse soit-elle, ne remplace pas une exécution réelle.

*Ce document ne couvre que la Phase 0 (socle). Le développement de la Phase 1 (logique métier) reste
suspendu dans l'attente de votre validation, conformément à la règle de gouvernance définie dans
`CLAUDE.md`.*
