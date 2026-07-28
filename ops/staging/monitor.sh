#!/bin/sh
set -eu

: "${STAGING_URL:?STAGING_URL is required}"
: "${MIN_FREE_PERCENT:=15}"
: "${MAX_QUEUE_DEPTH:=100}"

free_percent="$(df -P /var/www/html/storage | awk 'NR==2 {gsub("%","",$5); print 100-$5}')"
if [ "${free_percent}" -lt "${MIN_FREE_PERCENT}" ]; then
    echo "CRITICAL disk_free_percent=${free_percent}" >&2
    exit 1
fi

php artisan queue:monitor notifications,default --max="${MAX_QUEUE_DEPTH}"
if php artisan migrate:status --no-interaction | grep -q "Pending"; then
    echo "CRITICAL pending_migrations=true" >&2
    exit 1
fi

curl --fail --silent --show-error --max-time 10 "${STAGING_URL}/up" >/dev/null
curl --fail --silent --show-error --max-time 20 \
    "${STAGING_URL}/mythos/dar-hijama/ready" >/dev/null

echo "OK disk_free_percent=${free_percent}"
