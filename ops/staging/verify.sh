#!/bin/sh
set -eu

: "${STAGING_URL:?STAGING_URL is required}"

php artisan about --only=environment
php artisan migrate:status --no-interaction
php artisan schedule:list
php artisan queue:monitor notifications,default --max=100

curl --fail --silent --show-error --max-time 10 "${STAGING_URL}/up" >/dev/null
curl --fail --silent --show-error --max-time 15 "${STAGING_URL}/mythos/dar-hijama/health" >/dev/null
curl --fail --silent --show-error --max-time 20 "${STAGING_URL}/mythos/dar-hijama/ready" >/dev/null
