#!/bin/sh
set -eu

: "${COOLIFY_API_URL:?COOLIFY_API_URL is required}"
: "${COOLIFY_API_TOKEN:?COOLIFY_API_TOKEN is required}"
: "${COOLIFY_PROJECT_UUID:?COOLIFY_PROJECT_UUID is required}"
: "${COOLIFY_SERVER_UUID:?COOLIFY_SERVER_UUID is required}"
: "${STAGING_GIT_REPOSITORY:?STAGING_GIT_REPOSITORY is required}"

environment_name="${COOLIFY_ENVIRONMENT_NAME:-staging}"
git_branch="${STAGING_GIT_BRANCH:-main}"
hostname="${STAGING_HOSTNAME:-staging.notrejour.tn}"

payload="$(jq -n \
    --arg project_uuid "${COOLIFY_PROJECT_UUID}" \
    --arg server_uuid "${COOLIFY_SERVER_UUID}" \
    --arg environment_name "${environment_name}" \
    --arg repository "${STAGING_GIT_REPOSITORY}" \
    --arg branch "${git_branch}" \
    --arg domain "https://${hostname}" \
    '{
        project_uuid: $project_uuid,
        server_uuid: $server_uuid,
        environment_name: $environment_name,
        git_repository: $repository,
        git_branch: $branch,
        build_pack: "dockercompose",
        name: "mythos-dar-hijama-staging",
        domains: $domain,
        docker_compose_location: "/docker-compose.staging.yml",
        ports_exposes: "80",
        is_auto_deploy_enabled: false,
        is_force_https_enabled: true,
        health_check_enabled: true,
        health_check_path: "/up",
        health_check_port: "80",
        post_deployment_command: "sh ops/staging/release.sh",
        post_deployment_command_container: "app",
        instant_deploy: false
    }')"

response="$(curl --fail-with-body --silent --show-error \
    --request POST \
    --header "Authorization: Bearer ${COOLIFY_API_TOKEN}" \
    --header "Content-Type: application/json" \
    --data "${payload}" \
    "${COOLIFY_API_URL%/}/api/v1/applications/public")"

printf '%s\n' "${response}" | jq .
