# Deprecation policy

A stable API is deprecated before removal.

Every deprecation must include:

- PHP `@deprecated` documentation;
- `#[Deprecated(since:, replacement:, removalVersion:, reason:)]`;
- a working replacement;
- an upgrade-guide entry;
- at least one MINOR release of notice;
- removal only in the declared MAJOR release.

Generate the current catalogue:

```bash
php artisan mythos:deprecations
php artisan mythos:deprecations --write
```

The generated `docs/DEPRECATIONS.md` is a release artifact. Mythos OS v1.0.0 has no deprecated public APIs.
