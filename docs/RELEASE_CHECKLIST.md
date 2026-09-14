# Production release checklist

## Before release

- Freeze version, changelog, release notes, contract snapshot and release manifest.
- Run PHPUnit, architecture/SDK/generator tests, PHPStan, Pint, Composer validate/audit and Vite build.
- Verify SQLite migrations/rollback and MySQL CI.
- Review dependency, secret, authorization, upload and external URL security.
- Create tested database and media backups.
- Record current deployment version and rollback artifact.

## Deployment

- Enable maintenance mode when required.
- Install locked production dependencies.
- Apply migrations with `--force`.
- Build or deploy immutable frontend assets.
- Cache configuration, routes and views.
- Restart queue workers.
- Reload PHP-FPM and Nginx after configuration changes.

## Infrastructure

- Confirm TLS certificate validity, HTTPS redirect, HSTS and security headers.
- Confirm database connectivity, indexes, foreign keys and available storage.
- Confirm cache, sessions, queues and failed-job storage.
- Confirm S3-compatible media read/write/delete behavior.

## Health and monitoring

- Verify `/up`, public landing, template catalogue, invitation page, QR response and RSVP submission.
- Verify admin login and authorization.
- Monitor HTTP errors, queue depth, failed jobs, database load and storage failures.
- Confirm analytics, audit and database notifications.

## Rollback

- Stop new workers and enable maintenance mode.
- Restore the previous immutable release.
- Roll back only migrations explicitly verified as reversible, otherwise restore the backup.
- Rebuild caches, restart workers, disable maintenance mode and repeat health checks.
