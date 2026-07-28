# Production deployment verification

## Release gate

Production deployment is blocked until every item below has evidence attached to
the release record. Never place secrets or patient data in that record.

### Runtime and environment

- PHP 8.3 or later with `bcmath`, `curl`, `dom`, `fileinfo`, `gd`, `intl`,
  `mbstring`, `openssl`, `pdo_mysql`, `xml`, and `zip`.
- MySQL 8.4 with `utf8mb4`, strict mode, InnoDB, UTC timestamps, encrypted
  transport, automated backups, and a least-privilege application account.
- `APP_ENV=production`, `APP_DEBUG=false`, HTTPS `APP_URL`, a unique generated
  `APP_KEY`, explicit `TRUSTED_HOSTS`, and only known proxy addresses in
  `TRUSTED_PROXIES`.
- Database-backed or Redis cache, sessions, queues, and failed jobs. Do not use
  `sync`, `array`, or file sessions in a multi-node deployment.
- `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`,
  `SESSION_SAME_SITE=lax`, and `SESSION_ENCRYPT=true`.

Validate without printing secrets:

```bash
php -v
php -m
php artisan about --only=environment
php artisan config:show database.default
php artisan config:show queue.default
php artisan config:show session.driver
php artisan config:show cache.default
php artisan schedule:list
```

### Deployment sequence

```bash
php artisan down --render="errors::503"
composer install --no-dev --classmap-authoritative --no-interaction
npm ci
npm run build
php artisan migrate --force
php artisan dar-hijama:install
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
php artisan up
```

Run at least two queue workers for `notifications,default` with
`--tries=3 --backoff=10,60,300 --timeout=90 --max-time=3600`. Use systemd,
Supervisor, or the platform process manager with automatic restart. Horizon is
not installed and is not required for the database queue.

Run `php artisan schedule:run` every minute from one elected scheduler node.
Confirm `dar-hijama:dispatch-reminders` appears in `schedule:list`.

### Security verification

From an external network:

```bash
curl -I http://example.com/
curl -I https://example.com/
```

Confirm HTTP redirects to HTTPS and HTTPS includes HSTS,
`X-Content-Type-Options`, `X-Frame-Options`, CSP, Referrer Policy, and
Permissions Policy. Confirm cookies contain `Secure`, `HttpOnly`, and
`SameSite=Lax`. Run TLS and CSP scanners against the deployed hostname.

The checked-in Nginx CSP currently permits `unsafe-inline` and `unsafe-eval`.
Reduce those directives only after validating Filament and Vite assets with a
nonce or hash policy.

### Database verification

On an isolated MySQL 8.4 clone:

```bash
php artisan migrate:fresh --seed --force
php artisan dar-hijama:install
php artisan migrate:rollback --step=1 --force
php artisan migrate --force
php artisan test Applications/DarHijama/Tests/Feature
```

Capture `SHOW CREATE TABLE` and `SHOW INDEX` for Dar Hijama patient,
practitioner, appointment, transition, session, analytics, and access-log
tables. Confirm foreign keys use the documented restrict, null, and cascade
rules.

The current availability check is transactional but does not use a database
range constraint or practitioner advisory lock. A concurrent same-slot race
must be load-tested on MySQL before live booking is enabled.

### Storage verification

- Public media: upload, fetch through the public URL, delete, and confirm object
  removal on the configured S3-compatible disk.
- Dar Hijama private media: confirm records use the `local` private disk,
  files are outside the web root, and no direct public URL exists.
- Deny directory listing and direct access to `storage/app/private`.
- Run a daily orphan report comparing `media.disk/path` with object listings.
  Review before deletion; never delete automatically from the first report.
- Back up both private local media and the S3 bucket with encryption and
  versioning.

### Queue and scheduler verification

```bash
php artisan queue:work --queue=notifications,default --once --tries=3
php artisan dar-hijama:dispatch-reminders --date=YYYY-MM-DD
php artisan queue:failed
php artisan queue:retry all
php artisan schedule:list
```

Verify notification deliveries move through pending, delivered, and failed
states; failed providers do not roll back appointments; retries are
idempotent; and queue depth returns to zero.

### Health and observability

Monitor:

- `/up` and `/mythos/dar-hijama/health`;
- HTTP 5xx rate and latency;
- MySQL connectivity, locks, slow queries, and storage;
- queue depth, oldest queued job, failed jobs, and worker restarts;
- scheduler last-success timestamp;
- local/S3 free space and failed media operations;
- notification delivery failures;
- application logs with patient data redaction.

Static health endpoints prove process routing only. Infrastructure monitoring
must perform authenticated synthetic DB, cache, queue, scheduler, and storage
checks.
