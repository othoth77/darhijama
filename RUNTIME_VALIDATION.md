# RUNTIME_VALIDATION.md — Phase 0.6 : Validation runtime réelle

**Statut : VALIDÉE (VPS de production), correctif déployé et confirmé.** L'environnement d'exécution
de référence pour ce projet est le **VPS**, pas le dossier local ni un quelconque sandbox utilisé
pendant le développement. Cette page documente l'état réel confirmé sur ce VPS, distinct de tout ce
qui a pu être observé ailleurs.

---

## 0. Environnement de référence

| | |
|---|---|
| Environnement | VPS de production Notre Jour |
| URL | https://notrejour.tn |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `DB_CONNECTION` / `DB_DATABASE` | `mysql` / `notrejour` |

Le `.env` du VPS est géré et validé directement dessus — il n'est ni répliqué ni écrasé depuis cet
environnement de travail. Le `.env` présent dans le dossier local (créé lors d'une session précédente,
`FILESYSTEM_DISK=public`, `ADMIN_EMAIL=admin@notrejour.tn`, etc.) est un fichier de **développement
local uniquement** et ne doit jamais être confondu avec la configuration de production du VPS.

## 1. Versions confirmées sur le VPS

| Composant | Version | Statut |
|---|---|---|
| PHP | 8.5.4 | ✅ satisfait `^8.3` (composer.json) |
| Composer | 2.10.2 | ✅ |
| Laravel Framework | 12.64 | ✅ |
| Livewire | 3.8.2 | ✅ (dépendance de Filament) |
| Filament | 3.3.54 | ✅ |
| Laravel Pennant | 1.24 | ✅ |
| Spatie Permission | 6.25 | ✅ |
| nwidart/laravel-modules | 11.1.10 | ✅ (voir §3 pour une correction de configuration) |

## 2. Étapes confirmées réussies sur le VPS

- `composer install` — réussi, `vendor/autoload.php` présent.
- Migrations principales exécutées (tables socle Laravel : `users`, `sessions`, `cache`, `jobs`...).
- Migration Spatie Permission exécutée (`roles`, `permissions`, `model_has_roles`...).
- Seeding exécuté (`DatabaseSeeder` — rôle `admin` + compte opérateur).
- `npm install` — réussi.
- `npm run build` — réussi (Tailwind v4 / Vite / Alpine.js compilés sans erreur).
- `php artisan optimize` — réussi.
- `nginx -t` — réussi.
- `php artisan storage:link` — réussi.

Ces résultats valident, en conditions réelles, l'ensemble des correctifs de la Phase 0.5 (autoload
PSR-4 à plat, `config/pennant.php`, `config/permission.php`, enregistrement explicite du provider
nwidart) : **rien de tout cela n'était vérifié avant aujourd'hui, c'est maintenant confirmé en
production.**

## 3. Analyse : chargement des modules, routes, migrations

### 3.1 `php artisan module:list` — cause identifiée et corrigée

Contrairement à l'hypothèse initiale, la classe de commande `module:list` **existe bel et bien** dans
`nwidart/laravel-modules` v11.1.10
(`vendor/nwidart/laravel-modules/src/Commands/Actions/ListCommand.php`, `protected $name =
'module:list'`) et fait partie de la liste par défaut retournée par
`Nwidart\Modules\Providers\ConsoleServiceProvider::defaultCommands()`.

La cause réelle : `ConsoleServiceProvider::register()` exécute
`$this->commands(config('modules.commands', self::defaultCommands()->toArray()))`. Le second argument
de `config()` n'est utilisé **que si la clé est totalement absente** — pas si elle vaut un tableau
vide. Or notre `config/modules.php` (correctif « M » de la Phase 0.5, ajouté pour « compléter la
configuration ») définissait `'commands' => []`. Résultat réel : **aucune commande `module:*`
n'était enregistrée** — ni `module:list`, ni `module:enable`, ni `module:disable`, ni les commandes
`module:make-*`. Ce n'est donc pas une absence de fonctionnalité du package, mais une régression
introduite par notre propre configuration.

**Correctif appliqué** (`config/modules.php`) : remplacement de `'commands' => []` par
`'commands' => ConsoleServiceProvider::defaultCommands()->merge([])->toArray()`, qui est exactement le
pattern du stub officiel du package (`vendor/nwidart/laravel-modules/config/config.php`). À vérifier
sur le VPS avec `php artisan module:list` après déploiement de ce correctif — devrait maintenant
afficher les 13 modules (6 `Enabled`, 7 `Disabled`).

### 3.2 Chargement réel des modules actifs — vérifié par lecture du code source du package

Analyse de `vendor/nwidart/laravel-modules/src/` (version réellement installée) :

1. `LaravelModulesServiceProvider::boot()` appelle `registerModules()`, qui charge
   `ModuleManifest::getProviders()`.
2. `ModuleManifest::getModulesData()` scanne `Modules/*/module.json` (chemin dérivé de
   `config('modules.paths.modules')` = `base_path('Modules')`) et filtre chaque module via
   `FileActivator::hasStatus($name, true)`, qui compare `modules_statuses.json` par clé exacte au champ
   `"name"` du `module.json`.
3. Nos 13 `module.json` déclarent un `"name"` identique à leur clé dans `modules_statuses.json`
   (`Landing`, `Templates`, ... `Api`) → filtrage correct, 6 modules retenus.
4. Chaque `providers` de `module.json` (ex. `Modules\Landing\Providers\LandingServiceProvider`) est
   résolu et enregistré par Laravel via `ProviderRepository::load()` — la correspondance namespace ↔
   chemin de fichier (`Modules/Landing/Providers/LandingServiceProvider.php`) est cohérente avec le
   mapping racine `"Modules\\": "Modules/"` de `composer.json` (structure à plat, correctif Phase 0.5).

**Conclusion : le chargement des 6 modules core est structurellement correct.** Aucune anomalie
détectée dans ce circuit.

### 3.3 Pourquoi `route:list` n'affiche que les routes Laravel et Filament — cause réelle, non un bug

Chaque module core définit bien un `ServiceProvider::boot()` qui appelle
`$this->loadRoutesFrom(module_path($this->name, 'routes/web.php'))` — ce mécanisme fonctionne (vérifié
au §3.2). Mais le contenu actuel de **chaque** `Modules/{Nom}/routes/web.php` est, par exemple pour
Landing :

```php
Route::middleware(['web'])->group(function () {
    // Route::get('/', [\Modules\Landing\Http\Controllers\LandingController::class, 'index']);
});
```

La seule ligne de route est **commentée** — c'est un stub de scaffolding, intentionnel à ce stade :
aucun contrôleur, aucune vue, aucune route réelle n'a encore été écrite dans aucun module, la logique
métier étant explicitely réservée à la Phase 1 (voir `ROADMAP.md`, non démarrée). Le groupe de route
s'exécute donc sans erreur mais ne déclare aucune route — c'est pour cela que `route:list` ne montre
que les routes internes de Laravel et celles, bien réelles, du panneau Filament (`/admin/*`).
**Ce n'est pas un défaut de chargement, c'est l'état attendu d'un socle sans logique métier.**

### 3.4 Migrations des modules — même explication

`LaravelModulesServiceProvider::registerMigrations()` ajoute correctement le chemin
`Modules/{Nom}/database/migrations` de chaque module actif au `Migrator`. Mais ces dossiers ne
contiennent chacun qu'un `.gitkeep` vide (confirmé par lecture directe) : aucune migration de table
métier (`templates`, `orders`, `invitations`...) n'a encore été écrite. C'est cohérent avec
`DATABASE.md`, qui documente ces tables comme schéma cible de la Phase 1, pas comme existant. Les
« migrations principales exécutées » sur le VPS sont donc, à raison, uniquement celles du socle Laravel
et de Spatie Permission.

### 3.5 Module `Singles` — conforme à la décision produit

Aucune route, aucune vue, aucune migration pour `Singles` (flag `singles_corner` inactif par défaut,
`ServiceProvider` gaté par `Feature::active()`) — confirmé par lecture directe du code, rien à corriger.

## 4. Correctif déployé et confirmé sur le VPS (2026-07-15)

`config/modules.php` corrigé directement sur le VPS (`/var/www/notrejour`) via script Python exécuté
en SSH (remplacement strict, avec vérification d'unicité de la ligne cible avant écriture), puis :

```bash
php artisan config:clear
php artisan module:list
```

Résultat réel obtenu sur le VPS :

```
[Enabled]  Admin         Modules/Admin [0]
[Enabled]  Landing       Modules/Landing [1]
[Enabled]  Templates     Modules/Templates [2]
[Enabled]  Orders        Modules/Orders [3]
[Enabled]  Invitations   Modules/Invitations [4]
[Enabled]  Media         Modules/Media [5]
[Disabled] RSVP          Modules/RSVP [10]
[Disabled] Guestbook     Modules/Guestbook [11]
[Disabled] Timeline      Modules/Timeline [12]
[Disabled] Notifications Modules/Notifications [13]
[Disabled] AI            Modules/AI [14]
[Disabled] Singles       Modules/Singles [15]
[Disabled] Api           Modules/Api [16]
```

Conforme à 100 % à `modules_statuses.json` : 6 modules core `Enabled`, 7 modules futurs `Disabled`.
**La chaîne complète (autoload PSR-4 → scan `module.json` → filtrage par activator → enregistrement
des Service Providers) est validée de bout en bout sur l'environnement de production réel**, pas
seulement par revue de code.

`php artisan route:list` reste volontairement limité aux routes Laravel + Filament (§3.3, aucune
route de module futur exposée — confirmé conforme, pas une anomalie).

## 5. Score de validation runtime

**9/10.** Tous les critères obligatoires de la consigne d'origine sont satisfaits et confirmés sur le
VPS : `composer install` réussi, migrations réussies, seeding réussi, `npm run build` réussi,
`php artisan module:list` réussi (13/13 modules, statuts corrects), aucune erreur bloquante, aucun
module futur exposé (ni route, ni vue, ni donnée). Le seul point retiré : l'absence persistante de
tests métier (`php artisan test` n'a encore aucune assertion sur de la logique applicative, puisque
aucune n'existe — normal en Phase 0, mais ne peut pas compter comme preuve de robustesse
fonctionnelle future).

---

*Phase 1 (logique métier) reste non démarrée, conformément à la consigne. Le correctif de §3.1 est une
correction de configuration ponctuelle, déployée et vérifiée, pas un changement d'architecture.*
