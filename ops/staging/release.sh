#!/bin/sh
set -eu

php artisan migrate --force --no-interaction
php artisan dar-hijama:install --no-interaction
php artisan storage:link --no-interaction || true
php artisan optimize
php artisan queue:restart

php artisan migrate:status --no-interaction
php artisan route:list --path=mythos/dar-hijama/health --except-vendor
