# Mythos OS architecture overview

```text
Applications
    ↓ stable contracts
Mythos OS Core
    ↓
Laravel / Filament / storage / queues / databases
```

## Core

Core provides Analytics, Audit, Identity, Authorization, Media, Notifications, Public Links, QR, Security, Shared UI, WhatsApp infrastructure and the Application SDK.

Stable interfaces are application APIs. Implementations, Eloquent persistence, providers and jobs are internal.

## Applications

Applications have strict manifests and an explicit registry. They own business workflows, routes, policies and resources. Notre Jour is the first registered application and owns invitations, RSVP, templates, wedding workflows, landing and orders.

## Enforcement

Architecture tests prevent reverse dependencies and cross-application imports. Contract snapshots prevent accidental v1 API breaks. Manifest validation occurs before application providers run.

Detailed rules are in `MYTHOS_OS_ARCHITECTURE.md`, `MYTHOS_ARCHITECTURE_RULES.md` and `MYTHOS_APPLICATION_CONTRACT.md`.
