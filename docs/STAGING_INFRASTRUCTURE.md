# Mythos OS staging infrastructure

## Topology decision

The recommended topology is a separate Ubuntu 24.04 LTS VPS registered as a
remote server in the existing Coolify control plane.

| Option | Benefits | Risks |
|---|---|---|
| Separate VPS managed by Coolify | Complete CPU, memory, disk, network and failure isolation; exact Ubuntu 24.04 host; staging load cannot affect production | Additional VPS cost and one-time server registration |
| Separate Coolify application on the production VPS | Fastest and cheapest | Shared kernel, disk, Docker daemon and capacity; load or storage exhaustion can affect production; host is Ubuntu 26.04 rather than the certification target |

The existing production VPS must only host the Coolify control plane. The new
VPS is a Coolify destination and contains staging workloads only.

After registering the staging server in Coolify, set the project and server
UUIDs in the protected deployment environment and create the application with
`sh ops/staging/coolify-provision.sh`. The script creates the Compose resource
without deploying it. Configure the generated variables and secrets before the
first deployment.

## Required topology

```text
Internet
  -> staging.notrejour.tn
  -> Coolify proxy / Let's Encrypt
  -> staging web (Nginx)
  -> staging app (PHP 8.3-FPM)
       -> MySQL 8.4
       -> isolated Redis queue
       -> isolated Redis cache
       -> isolated Redis sessions
       -> isolated storage volume
  -> queue worker
  -> scheduler
  -> backup volume
```

No service in this stack uses a production hostname, database, Redis instance,
bucket or volume.

## Server provisioning

Provision a new VPS with:

- Ubuntu 24.04 LTS;
- at least 4 vCPU, 8 GB RAM and 80 GB SSD for certification load tests;
- SSH key authentication;
- ports 22, 80 and 443 only;
- automatic security updates;
- NTP enabled;
- a non-root Coolify deployment account with Docker access.

Register it under Coolify **Servers**, validate it, configure
`staging.notrejour.tn` as its wildcard/domain target, and enable server disk,
CPU and memory notifications.

## Coolify application

Create a project environment named `staging` and a Docker Compose application:

- repository: `othoth77/notre-jour`;
- branch: a protected staging/release branch;
- compose file: `/docker-compose.staging.yml`;
- public service: `web`, port `80`;
- domain: `https://staging.notrejour.tn`;
- health checks: enabled;
- rolling deployment: enabled;
- post-deployment command on container `app`:
  `sh ops/staging/release.sh`;
- auto-deploy: disabled until certification, then limited to the protected
  staging branch.

The release command does not enable application-wide maintenance mode, allowing
the previous healthy container to keep serving during a rolling deployment.

Coolify reads required variables from the Compose `${VAR:?}` declarations and
must refuse deployment while any secret is empty.

Set these locked, runtime-only secrets:

- `APP_KEY`;
- `MYSQL_PASSWORD`;
- `MYSQL_ROOT_PASSWORD`;
- `REDIS_QUEUE_PASSWORD`;
- `REDIS_CACHE_PASSWORD`;
- `REDIS_SESSION_PASSWORD`;
- `ADMIN_EMAIL`;
- `ADMIN_PASSWORD`.

Generate every password independently. Never copy production values. Use
`.env.staging.example` as the non-secret reference.

## DNS and TLS

Create:

```text
Type: A
Name: staging
Value: <new-staging-vps-ip>
TTL: 300 during provisioning, then 3600
```

Add an `AAAA` record only when IPv6 routing and firewalling are verified.
Coolify must issue and renew the Let's Encrypt certificate after DNS resolves.
Keep forced HTTPS enabled. Validate certificate chain, expiry, HTTP-to-HTTPS
redirect and renewal from an external network.

## Database

The Compose stack provisions `mysql:8.4` with a dedicated database, user and
named volume. The database has no published host port.

Deployment order:

1. capture database and media backups;
2. deploy the immutable commit;
3. execute `php artisan migrate --force`;
4. execute `php artisan dar-hijama:install`;
5. build Laravel caches;
6. verify readiness;
7. release traffic.

Run database backups at least daily and before every deployment:

```bash
docker compose -f docker-compose.staging.yml \
  --profile operations run --rm backup \
  /var/www/html/ops/staging/backup.sh
```

Copy backup archives to an encrypted off-server destination. Retain daily
backups for 14 days and run a restore drill before certification.

## Redis

Queue, cache and session state use three private Redis services with independent
passwords and connections. Queue and session Redis use append-only persistence.
Cache Redis is intentionally disposable. None publish port `6379`.

## Storage

`staging-storage` is unique to the staging Compose project. It contains private
patient media and public staging media. Production volumes and S3 buckets are
never mounted. Backups include `storage/app`; restore requires checksum
validation before extraction.

## Deployment and rollback

The `Staging deployment` GitHub Actions workflow requires the protected GitHub
`staging` environment and these secrets:

- `COOLIFY_API_URL`;
- `COOLIFY_API_TOKEN`;
- `COOLIFY_RESOURCE_UUID`.

Set the environment variable `STAGING_URL=https://staging.notrejour.tn`.
The workflow pins `git_commit_sha`, triggers Coolify through its authenticated
API, waits for completion, and verifies readiness and required headers.

Rollback selects a previously certified commit:

```bash
ops/staging/rollback.sh <known-good-commit-sha>
```

Database migrations must remain backward-compatible across one release. If a
rollback requires data reversal, restore the pre-deployment database and media
backup before releasing traffic.

## Monitoring and alerts

Configure Coolify notifications for:

- container unhealthy or restarting;
- deployment failure;
- CPU above 85% for 10 minutes;
- memory above 85% for 10 minutes;
- disk free below 15%;
- backup job failure.

Run `ops/staging/monitor.sh` every minute as a Coolify scheduled task in the
`app` container. Alert on non-zero exit. It verifies disk, queue depth, pending
migrations, liveness and readiness. Retain Nginx, PHP, Laravel, worker,
scheduler, MySQL and Redis logs for at least 14 days.

The detailed diagnostics endpoint remains protected by authentication and
`dar-hijama.settings.manage`.

## Security baseline

Coolify terminates TLS and forwards to the internal Nginx service. Nginx adds
HSTS, CSP, Permissions-Policy, Referrer-Policy, X-Frame-Options and
X-Content-Type-Options. PHP hides its version and disables displayed errors.
Only the web service is public. MySQL, Redis, PHP-FPM and storage remain on the
private Compose network.

The CSP retains `unsafe-inline` and `unsafe-eval` for current Filament
compatibility and must remain a separately tracked hardening item.

## External prerequisites

Deployment remains blocked until:

1. the separate Ubuntu 24.04 VPS exists;
2. Coolify can validate it over SSH;
3. the staging DNS record points to it;
4. a least-privilege Coolify API token and resource UUID are configured;
5. independent staging secrets are generated;
6. off-server backup storage and alert destinations are configured.

Coolify Compose, environment, storage, health-check and API behavior follows
the official documentation:

- https://coolify.io/docs/knowledge-base/docker/compose
- https://coolify.io/docs/knowledge-base/health-checks
- https://coolify.io/docs/knowledge-base/domains
- https://coolify.io/docs/api-reference/api/deployments/deploy-by-tag-or-uuid
- https://coolify.io/docs/api-reference/api/applications/update-application-by-uuid
