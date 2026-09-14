# INSTALL.md — Notre Jour

## Prérequis

- PHP 8.3 (extensions : mbstring, xml, curl, zip, gd ou imagick, pdo_mysql, bcmath)
- Composer 2.x
- Node.js 20+ / npm
- MySQL 8+
- Un compte OVH Object Storage (S3-compatible) pour la production (facultatif en local, voir plus bas)

> **Important (statut Phase 0.6)** : la validation runtime de référence de ce projet se fait sur le
> **VPS de production** (https://notrejour.tn), pas sur un poste local — voir `RUNTIME_VALIDATION.md`
> pour l'état confirmé (PHP 8.5.4, composer install, migrations, seeding, build front tous réussis) et
> le détail d'une régression de configuration identifiée et corrigée (`config/modules.php`). Les
> instructions ci-dessous restent valables pour un environnement de développement local classique.

## Installation locale

```bash
cd notre-jour

# 1. Dépendances PHP
composer install

# 2. Dépendances front
npm ci

# 3. Environnement
cp .env.example .env
php artisan key:generate

# 4. Base de données (créer la base "notre_jour" au préalable dans MySQL)
php artisan migrate --seed

# 5. Lien symbolique storage public (visuels de modèles en local, avant bascule S3)
php artisan storage:link

# 6. Front-end
npm run dev      # développement
npm run build    # production

# 7. Lancer le serveur
php artisan serve
```

> Les modules core (Landing, Templates, Orders, Invitations, Admin, Media) sont déjà marqués actifs
> dans `modules_statuses.json` — aucune commande `module:enable` n'est nécessaire après un clone frais.
> `php artisan module:enable {Module}` (un seul nom à la fois, voir `AUDIT_PHASE_0.md` correctif F)
> ne sert qu'à activer un module futur ponctuellement, sur validation du Product Owner.

## Compte admin par défaut

Défini par le seeder (`database/seeders/DatabaseSeeder.php`) via les variables d'environnement
`ADMIN_EMAIL` / `ADMIN_PASSWORD` (à définir dans `.env` avant le premier `migrate --seed` en
production — ne jamais laisser les valeurs par défaut `admin@notrejour.tn` / `change-me-immediately`
en production).

## Stockage médias (OVH Object Storage / S3)

`.env.example` démarre avec `FILESYSTEM_DISK=public` et `MEDIA_DISK=public` pour permettre un premier lancement local sans
compte OVH. Avant la mise en production, renseigner `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`,
`AWS_BUCKET`, `AWS_ENDPOINT` (ex. `https://s3.gra.io.cloud.ovh.net`), `AWS_DEFAULT_REGION` (ex. `gra`),
`AWS_USE_PATH_STYLE_ENDPOINT=true`, puis basculer `FILESYSTEM_DISK=s3` et `MEDIA_DISK=s3`. Ne jamais déployer en
production avec `FILESYSTEM_DISK=public` (voir `AUDIT_PHASE_0.md` correctif E).

## Feature Flags (Laravel Pennant)

Les flags des modules futurs sont déclarés dans `config/features.php` et enregistrés au boot par
`App\Providers\FeatureFlagServiceProvider`. Pour activer un module futur (jamais en production sans
validation du Product Owner) :

```bash
php artisan tinker
>>> Laravel\Pennant\Feature::activate('rsvp');
```

Une interface d'activation depuis le back-office Filament est prévue en Phase 1 (Dashboard Analytics
+ écran Feature Flags du module Admin).

## Vérification après installation

```bash
php artisan module:list      # doit lister les 13 modules, 6 enabled + 7 disabled
php artisan route:list       # routes des modules core uniquement
composer validate --strict --no-check-publish
composer dump-autoload -o   # aucune alerte PSR-4 attendue
php artisan test             # 56 tests / 96 assertions
vendor/bin/pint --test
npm run build
```

Merci de signaler tout écart entre ces instructions et le résultat réel de `composer install` —
elles seront corrigées en priorité. Voir `RUNTIME_VALIDATION.md` pour le protocole complet de
validation runtime (Phase 0.6) et son statut actuel.
## Base de test et reproductibilité

La suite standard utilise MySQL et refuse toute base dont le nom ne se termine pas par `_test`.
Créer `notrejour_test`, puis vérifier que `pdo_mysql` est actif avant `composer test`. La CI utilise
MySQL 8 avec le même nom de base. Pour un diagnostic local sans MySQL, une base SQLite isolée dont le
chemin se termine par `notrejour_test` peut être injectée via `DB_CONNECTION` et `DB_DATABASE`; cette
exécution ne remplace pas la validation MySQL de la CI.

Le dépôt contient `package-lock.json`; utiliser `npm ci` pour toute installation reproductible et
réserver `npm install` aux mises à jour intentionnelles de dépendances.

## Durcissement production

Avant déploiement, suivre `docs/PRODUCTION_CHECKLIST.md`. La limite d'upload commune est 100 MB : définir `upload_max_filesize=100M` et `post_max_size=100M` dans le `php.ini` de PHP-FPM. Configurer `TRUSTED_HOSTS`, `TRUSTED_PROXIES` et `SESSION_SECURE_COOKIE=true`, puis valider la configuration HTTPS fournie avec `nginx -t`.

Les listeners analytics et notifications nécessitent un worker :

```bash
php artisan queue:work --queue=default,notifications --tries=3
```
