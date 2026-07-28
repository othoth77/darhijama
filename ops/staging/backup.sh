#!/bin/sh
set -eu

: "${MYSQL_DATABASE:?MYSQL_DATABASE is required}"
: "${MYSQL_USER:?MYSQL_USER is required}"
: "${MYSQL_PASSWORD:?MYSQL_PASSWORD is required}"
: "${BACKUP_DIRECTORY:=/backups}"

timestamp="$(date -u +%Y%m%dT%H%M%SZ)"
destination="${BACKUP_DIRECTORY}/${timestamp}"
mkdir -p "${destination}"

mysqldump \
    --host=mysql \
    --user="${MYSQL_USER}" \
    --password="${MYSQL_PASSWORD}" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    "${MYSQL_DATABASE}" > "${destination}/database.sql"

tar -C /var/www/html -czf "${destination}/storage.tar.gz" storage/app
sha256sum "${destination}/database.sql" "${destination}/storage.tar.gz" \
    > "${destination}/SHA256SUMS"

find "${BACKUP_DIRECTORY}" -mindepth 1 -maxdepth 1 -type d -mtime +14 -exec rm -rf -- {} +
echo "${destination}"
