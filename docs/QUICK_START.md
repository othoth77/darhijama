# Mythos OS quick start

## Requirements

- PHP 8.3+
- Composer
- Node.js 20+
- MySQL for development/production
- Required PHP extensions, including PDO MySQL and PDO SQLite

## Install

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm ci
npm run build
php artisan serve
```

Open the application URL and `/admin`. Set a strong `ADMIN_PASSWORD` before production seeding.

## Verify

```bash
php artisan mythos:applications
php artisan mythos:application:validate notre-jour
php artisan test
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/pint --test
```

Create an application scaffold with `php artisan mythos:make-application Example`; review its manifest before adding it to `config/mythos.php`.
