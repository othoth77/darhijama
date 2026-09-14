# Mythos OS reusable component audit

## Extracted into Core

| Capability | Core location | Public boundary |
|---|---|---|
| Identity and users | `Mythos/Core/Identity` | Laravel authentication contracts and `User` |
| Authorization | `Mythos/Core/Identity` | policies consume the Core user type |
| Media and uploads | `Mythos/Core/Media` | `MediaManager` |
| Notifications | `Mythos/Core/Notifications` | `NotificationDispatcher`, `NotificationQueue`, `NotificationChannel` |
| QR codes | `Mythos/Core/QrCode` | `QrCodeGenerator` |
| Public token links | `Mythos/Core/PublicLinks` | `PublicLinkGenerator` |
| Audit logging | `Mythos/Core/Audit` | `AuditLogger` |
| Analytics | `Mythos/Core/Analytics` | `AnalyticsRecorder` |
| Shared Blade/Filament UI | `Mythos/Core/UI` | anonymous Blade components and public component factories |
| Security middleware | `Mythos/Core/Security` | HTTP middleware and configuration |
| WhatsApp link infrastructure | `Mythos/Core/WhatsApp` | `WhatsAppLinkBuilder` |

Laravel cache, logging, queues, filesystems, search primitives and API routing remain framework infrastructure. Mythos Core configures or wraps them only when an application-independent policy exists.

## Retained in Notre Jour

- Invitation lifecycle, publication, preview, duplication and QR path policy.
- Wedding templates and public catalogue behavior.
- Guests and RSVP identity, correction, abuse protection and admin notifications.
- Wedding program, venue, social links and public invitation rendering.
- Orders and landing flows for the Notre Jour operating model.
- Business events and listeners adapting Notre Jour events to Core analytics, audit and notification contracts.

## Deferred extraction

- Generic settings and search do not yet have multiple real consumers; extracting them now would be premature.
- The empty future API module remains disabled. Shared API authentication, versioning and error contracts should be added to Core when the first production API is specified.
