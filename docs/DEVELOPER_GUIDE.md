# Mythos OS developer guide

## Development model

Applications depend on stable Mythos Core contracts. Core depends only on Laravel and infrastructure libraries. Notre Jour business logic remains in its application modules.

## Workflow

1. Install locked dependencies and use the dedicated SQLite test database.
2. Make the smallest change inside the owning module.
3. Add unit, feature and architecture coverage.
4. Run PHPUnit, PHPStan and Pint.
5. Validate manifests, migrations, Blade and Vite when affected.

## Public APIs

Consult `API_STABILITY.md`. Inject stable contracts rather than Core implementations. Experimental APIs require an upgrade review; Internal classes must not be imported by applications.

## Applications

Use the SDK generator and explicit registry. Permissions must be namespaced. Routes, migrations, config and resources must remain inside the application root. Cross-application imports are prohibited.

## Releases

Follow Semantic Versioning, the deprecation policy, `UPGRADING.md`, the LTS policy and release checklist. Intentional stable-contract changes require a snapshot update and the appropriate version increase.
