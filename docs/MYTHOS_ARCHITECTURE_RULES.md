# Mythos architecture rules

## Dependency direction

```text
Application → Mythos Core contracts → Laravel/framework
```

## Enforced rules

- Core production code cannot import `Applications`, `App` or `Modules`.
- Application production code cannot import `App`, `Modules`, or another application's namespace.
- Infrastructure capabilities are injected through Core contracts.
- Application slugs are globally unique.
- Manifests are strict and compatible before providers execute.
- Production application discovery is an explicit allow-list.
- Routes, migrations and resources use validated relative paths.
- Generated applications contain no Notre Jour references.

## Testing requirements

Every application requires:

- manifest validation;
- a health test;
- architecture-boundary tests;
- route and migration loading verification when declared;
- tests for requested Core contracts;
- the complete project quality gate.

Notre Jour remains the reference application. Its existing `Modules` code is treated as legacy application implementation behind `Applications\NotreJour`, and may not be imported by future applications.
