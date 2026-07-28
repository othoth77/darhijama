# Mythos application manifest

Every application contains `mythos.json`.

## Reference

| Key | Type | Purpose |
|---|---|---|
| `name`, `slug`, `namespace`, `version` | string | Stable application identity |
| `description` | string | Developer-facing summary |
| `application_class` | class | Implements `MythosApplication` |
| `service_provider` | class | Laravel service provider |
| `core_capabilities` | string list | Explicit Core dependencies |
| `permissions` | string list | Namespaced permissions |
| `routes` | object list | Relative path and middleware |
| `migrations` | string list | Relative migration directories |
| `config` | object list | Config key and relative file |
| `view_namespace` | string | View and translation namespace |
| `views`, `translations` | path or null | Resource directories |
| `commands` | class list | Artisan commands |
| `event_listeners` | object list | Event/listener class pairs |
| `assets` | string list | Application-owned asset entries |
| `compatibility.mythos_core` | string | `^major.minor` requirement |

Unknown keys, missing keys, unsafe paths, duplicate capabilities or permissions, unknown capabilities, invalid classes and incompatible Core versions fail registration.

Production discovery uses only the explicit manifest allow-list in `config/mythos.php`. Filesystem scanning is prohibited.
