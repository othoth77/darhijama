# Upgrading Mythos OS

## Before upgrading

1. Read `CHANGELOG.md`, release notes, deprecations and the target release manifest.
2. Back up the database and application media.
3. Verify application `compatibility.mythos_core`.
4. Run Composer validation, security audit, PHPUnit and PHPStan.
5. Test migration and rollback on a production-like database.

## Upgrade procedure

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan migrate --force
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

## Compatibility guarantees

- PATCH releases fix defects and security issues without adding required integration work.
- MINOR releases add backward-compatible capabilities and may deprecate APIs.
- MAJOR releases may remove deprecated APIs and include required migration steps.
- Stable v1 contracts, public routes, manifest keys and database compatibility remain supported through the v1 support window.

Application manifests that are incompatible fail before their providers execute.

## Rollback

Restore the previous code and lockfiles, restore the database backup when a migration is not backward-compatible, rebuild assets, clear and rebuild caches, restart workers, then verify `/up` and application health endpoints.
