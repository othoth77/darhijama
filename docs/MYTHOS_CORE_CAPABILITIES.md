# Mythos Core capabilities

| Manifest value | Public boundary |
|---|---|
| `identity` | Core `User` identity boundary |
| `authorization` | Laravel gates/policies and Core identity |
| `media` | `MediaManager` |
| `notifications` | `NotificationDispatcher`, queue and channel contracts |
| `analytics` | `AnalyticsRecorder` |
| `audit` | `AuditLogger` |
| `public-links` | `PublicLinkGenerator` |
| `qr` | `QrCodeGenerator` |
| `whatsapp` | `WhatsAppLinkBuilder` |
| `shared-ui` | registered Blade components and Filament factories |

A manifest must explicitly declare every capability it consumes. Services are resolved through the container using contracts. Internal implementations such as `MediaService`, `QrCodeService` and `AnalyticsService` are not application APIs.
