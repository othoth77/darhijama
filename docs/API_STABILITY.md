# API stability

Mythos OS uses three API statuses:

- **Stable** — compatible throughout the v1 release line. Removal or incompatible signature changes require v2.
- **Experimental** — may change in a MINOR release with an upgrade note and migration path.
- **Internal** — implementation detail; applications must not import it.

## Stable v1 contracts

- `ApplicationRegistry`
- `MythosApplication`
- `AnalyticsRecorder`
- `AuditLogger`
- `MediaManager`
- `NotificationChannel`
- `NotificationDispatcher`
- `NotificationQueue`
- `PublicLinkGenerator`
- `QrCodeGenerator`

Stable supporting value objects include `ApplicationManifest`, `PermissionDefinition`, `NavigationDefinition`, `CoreCapability`, `StoredMediaFile` and `NotificationMessage`.

Core service implementations, Eloquent models, jobs, providers, console command internals and registry validators are Internal. Shared Filament factories are Experimental visual-development APIs; existing behavior remains compatible in v1.

The `PublicApi` attribute records status and introduction version. `release/contracts-v1.0.json` freezes stable contract source signatures and is enforced by tests.
