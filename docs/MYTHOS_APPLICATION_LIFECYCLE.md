# Mythos application lifecycle

The registry executes hooks in this order:

1. `registering`
2. service-provider and configuration registration
3. `registered`
4. `booting`
5. route, migration, view, translation, command and listener registration
6. `booted`

`enabling` and `disabling` are reserved hooks. No destructive enable/disable commands are provided because safe state transitions require application-specific transactional plans.

Hooks cannot change manifest validation, Core capability bindings, trusted-host rules or authorization middleware. A hook must be idempotent and must not perform irreversible work during application bootstrap.

Applications registered after framework boot are booted immediately and route lookups are refreshed safely.
