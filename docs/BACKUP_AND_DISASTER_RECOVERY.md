# Backup and disaster recovery

## Policy

- MySQL: encrypted full backup daily and binlog/PITR retention for 35 days.
- Private media and S3 objects: encrypted daily incremental backup with object
  versioning; retain 35 daily and 12 monthly recovery points.
- Application releases, `.env` secret references, and infrastructure
  configuration: immutable versioned artifacts.
- Keep one copy in a separate account and region. Restrict restore access and
  audit every backup operation.

## Integrity and restore drill

For every backup, record checksum, size, start/end time, source version, and
encryption key identifier. Weekly, restore into an isolated network, run
`mysqlcheck`, count critical tables, verify representative private/public media
checksums, run migrations in pretend mode, and execute health and workflow
tests. A backup is not verified until this drill succeeds.

Targets: RPO 24 hours without binlogs or 15 minutes with PITR; RTO 4 hours.

## Failed deployment

1. Stop traffic, schedulers, and new queue workers; enable maintenance mode.
2. Preserve logs, failed jobs, and the release identifier.
3. If migrations are backward-compatible, deploy the previous immutable
   release and rebuild caches.
4. If data shape is incompatible, restore the pre-deployment MySQL snapshot and
   matching media recovery point. Never guess with destructive SQL.
5. Restart workers only after database and code versions match.
6. Verify `/up`, Dar Hijama health, authentication, DB, storage, queues, and a
   synthetic booking in the isolated environment.

## Queue recovery

Pause workers, fix the provider or code condition, inspect failed jobs for
sensitive data, retry a small sample, confirm idempotency, then drain in bounded
batches while monitoring provider rate limits and database load.

## Storage recovery

Restore private files outside the web root and preserve permissions. Restore S3
object versions without changing keys. Reconcile the `media` table against
restored objects and quarantine orphans for manual review.
