#!/bin/sh
set -eu

: "${COOLIFY_API_URL:?COOLIFY_API_URL is required}"
: "${COOLIFY_API_TOKEN:?COOLIFY_API_TOKEN is required}"
: "${COOLIFY_RESOURCE_UUID:?COOLIFY_RESOURCE_UUID is required}"

commit="${1:-}"
api="${COOLIFY_API_URL%/}/api/v1"
authorization="Authorization: Bearer ${COOLIFY_API_TOKEN}"

if [ -n "${commit}" ]; then
    payload="$(printf '{"git_commit_sha":"%s"}' "${commit}")"
    curl --fail --silent --show-error \
        --request PATCH \
        --header "${authorization}" \
        --header "Content-Type: application/json" \
        --data "${payload}" \
        "${api}/applications/${COOLIFY_RESOURCE_UUID}" >/dev/null
fi

response="$(curl --fail --silent --show-error \
    --header "${authorization}" \
    "${api}/deploy?uuid=${COOLIFY_RESOURCE_UUID}&force=true")"
deployment_uuid="$(printf '%s' "${response}" | jq -r '.deployments[0].deployment_uuid')"

if [ -z "${deployment_uuid}" ] || [ "${deployment_uuid}" = "null" ]; then
    echo "Coolify did not return a deployment UUID." >&2
    exit 1
fi

deadline="$(( $(date +%s) + 1800 ))"
while [ "$(date +%s)" -lt "${deadline}" ]; do
    deployment="$(curl --fail --silent --show-error \
        --header "${authorization}" \
        "${api}/deployments/${deployment_uuid}")"
    status="$(printf '%s' "${deployment}" | jq -r '.status')"
    case "${status}" in
        finished)
            echo "${deployment_uuid}"
            exit 0
            ;;
        failed|cancelled|cancelled-by-user)
            printf '%s\n' "${deployment}" | jq .
            exit 1
            ;;
    esac
    sleep 10
done

echo "Deployment ${deployment_uuid} timed out." >&2
exit 1
