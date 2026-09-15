#!/bin/sh
set -eu

mkdir -p \
    storage/app/private \
    storage/app/public \
    storage/app/purifier \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ "$(id -u)" = "0" ]; then
    chown -R www-data:www-data storage bootstrap/cache
    # The web container's nginx (different uid) serves /storage/ from the shared
    # volume: it needs traverse-only on the parents and read on the public disk.
    chmod 711 storage storage/app
    chmod -R o+rX storage/app/public
fi

php artisan storage:link --no-interaction >/dev/null 2>&1 || true

exec "$@"
