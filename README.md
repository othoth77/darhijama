# Notre Jour

Application Laravel modulaire d'invitations digitales. Les modules de production actifs sont Landing, Templates, Orders, Invitations, Admin et Media.

## Installation locale

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm ci
npm run build
php artisan serve
```

MySQL reste la base de développement et de production. La suite locale automatisée utilise exclusivement `database/notrejour_test.sqlite`.

## Qualité

```bash
php artisan test
vendor/bin/pint --test
vendor/bin/phpstan analyse --memory-limit=1G
composer audit --locked
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

La CI exécute la suite complète sur SQLite et vérifie séparément migrations, rollback, contraintes et workflows Invitation/RSVP sur MySQL 8.4.

## Production

Consulter [INSTALL.md](INSTALL.md), [ARCHITECTURE.md](ARCHITECTURE.md) et [docs/PRODUCTION_CHECKLIST.md](docs/PRODUCTION_CHECKLIST.md) avant tout déploiement.

## Mythos OS v1.0

- [Quick Start](docs/QUICK_START.md)
- [Developer Guide](docs/DEVELOPER_GUIDE.md)
- [Administrator Guide](docs/ADMINISTRATOR_GUIDE.md)
- [Architecture Overview](docs/ARCHITECTURE_OVERVIEW.md)
- [Upgrading](docs/UPGRADING.md)
- [Release Checklist](docs/RELEASE_CHECKLIST.md)
