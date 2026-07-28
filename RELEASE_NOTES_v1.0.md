# Mythos OS v1.0.0 release notes

Mythos OS v1.0.0 is the first stable platform release and the foundation of the production Notre Jour application.

## Highlights

- Reusable Analytics, Audit, Identity, Media, Notifications, Public Links, QR, Security, UI and WhatsApp capabilities.
- Contract-first dependency direction from applications to Core.
- Strict `mythos.json` application manifests and explicit production registry.
- Deterministic `mythos:make-application` scaffolding.
- Architecture enforcement preventing Core reverse dependencies and application namespace leakage.
- SQLite test isolation and dedicated MySQL 8.4 CI verification.
- Production security, queue, upload, storage and deployment hardening.

## Compatibility

No Notre Jour business behavior, public route, route name, database table, morph alias, public token, QR path, RSVP flow or order workflow changes in this release.

Public contracts marked Stable are covered by a release snapshot. Changes that break those signatures require Mythos OS 2.0.

## Requirements

- PHP 8.3 or newer within the supported Composer constraint.
- Laravel 12.
- Node.js 20 for asset builds.
- MySQL 8.4 in production verification; SQLite for local automated tests.
- A configured queue worker for `default,notifications`.

## Known limitations

- Application enable/disable mutation commands remain intentionally unavailable until transactional state plans are defined.
- Local MySQL verification requires an installed server or container runtime; CI performs the authoritative MySQL gate.
