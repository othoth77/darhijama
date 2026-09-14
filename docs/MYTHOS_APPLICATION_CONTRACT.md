# Mythos application contract

A Mythos OS application is represented by `MythosApplication` and an immutable `ApplicationManifest`.

## Required identity

- name, slug, namespace and semantic version;
- application class and Laravel service provider;
- description and Mythos Core compatibility constraint.

## Registration surface

An application may declare routes, migrations, configuration, permissions, navigation, views, translations, commands, event listeners and assets. Paths are relative to the application root and are validated before registration.

`permissions()` returns `PermissionDefinition` objects. `navigation()` returns `NavigationDefinition` objects for navigation groups, items and dashboard widgets. Registration mechanisms expose metadata; they do not grant permissions or bypass Filament authorization.

## Dependency rule

Applications consume `Mythos\Core\...\Contracts` for infrastructure capabilities. Application-specific providers may compose Laravel and their own namespace, but cannot import another application's internals. Core never imports `Applications`, `App` or `Modules`.

Notre Jour is represented by `Applications\NotreJour\Application`; existing business modules remain unchanged.
