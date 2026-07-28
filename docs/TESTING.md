# Testing guide

## SQLite locale

`phpunit.xml` fixe `DB_CONNECTION=sqlite` et `DB_DATABASE=database/notrejour_test.sqlite`. Cette base est dédiée aux tests et ne doit contenir aucune donnée réelle.

```bash
php artisan test
```

## MySQL de CI

Le job `mysql-production` de `.github/workflows/ci.yml` crée `notrejour_test` sur MySQL 8.4, exécute une migration complète, un rollback suivi d'une nouvelle migration, puis les tests critiques Invitation et RSVP. Il ne se connecte à aucune base distante.

## Vérifications complémentaires

```bash
vendor/bin/pint --test
vendor/bin/phpstan analyse --memory-limit=1G
composer audit --locked
npm run build
php artisan view:cache
```
