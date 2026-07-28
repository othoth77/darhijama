#!/bin/sh
set -eu

: "${1:?Usage: restore.sh /backups/<timestamp>}"
: "${MYSQL_DATABASE:?MYSQL_DATABASE is required}"
: "${MYSQL_USER:?MYSQL_USER is required}"
: "${MYSQL_PASSWORD:?MYSQL_PASSWORD is required}"

backup_directory="$1"
cd "${backup_directory}"
sha256sum --check SHA256SUMS

php /var/www/html/artisan down --retry=60
mysql \
    --host=mysql \
    --user="${MYSQL_USER}" \
    --password="${MYSQL_PASSWORD}" \
    "${MYSQL_DATABASE}" < database.sql
tar -C /var/www/html -xzf storage.tar.gz
php /var/www/html/artisan optimize
php /var/www/html/artisan queue:restart
php /var/www/html/artisan up
