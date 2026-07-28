#!/bin/sh
set -eu

: "${1:?Usage: rollback.sh <known-good-commit-sha>}"

known_good_commit="$1"
"$(dirname "$0")/coolify-deploy.sh" "${known_good_commit}"

: "${STAGING_URL:?STAGING_URL is required}"
curl --fail --silent --show-error --max-time 20 \
    "${STAGING_URL}/mythos/dar-hijama/ready" >/dev/null
