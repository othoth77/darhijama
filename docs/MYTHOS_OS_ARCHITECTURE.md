# Mythos OS architecture

## Layers

```text
Notre Jour application
        ↓ contracts
Mythos OS Core
        ↓
Laravel / Filament / infrastructure drivers
```

`Mythos\Core` owns reusable policies and implementations. `Modules` and `App` form the Notre Jour application and composition layer. Core production code must never import `App\` or `Modules\`.

## Core modules

- Analytics
- Audit
- Identity and authorization
- Media and uploads
- Notifications
- Public links
- QR codes
- Security
- Shared UI
- WhatsApp infrastructure

Each module contains configuration, a public contract or public framework boundary, an implementation, and focused tests where behavior exists. `MythosCoreServiceProvider` is the single binding registry.

## Application modules

- Invitations, guests, RSVP and wedding workflows
- Templates
- Orders
- Landing
- Notre Jour admin composition

Application adapters may translate business events into Core contracts. Core cannot subscribe to or import Notre Jour events directly.

## Dependency rules

1. Application code injects contracts for analytics, audit, media, notifications, public links and QR generation.
2. Internal Core implementation classes are resolved only by `MythosCoreServiceProvider` and Core tests.
3. Cross-application data is represented by scalars, DTOs or framework contracts.
4. Business models never move into Core solely to avoid an adapter.
5. New reusable behavior requires a stable contract and at least one real application consumer.
6. Boundary tests reject Core imports from application namespaces.

## Compatibility

Routes, database tables, morph aliases, public tokens, storage paths and user-facing behavior remain unchanged. The refactor changes namespaces and dependency bindings only.
