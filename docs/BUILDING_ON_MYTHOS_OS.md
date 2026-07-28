# Building an application on Mythos OS

## Bootstrap

1. Map `Mythos\Core\` to `Mythos/Core/` with PSR-4.
2. Register `Mythos\Core\Providers\MythosCoreServiceProvider` before application providers.
3. Define application environment values for storage, queues, hosts and channels.
4. Add application modules under `Modules/` without importing Core implementations.

New applications should use the formal SDK under `Applications/` and be explicitly
listed in `config/mythos.php`. See `CREATING_A_MYTHOS_APPLICATION.md`.

## Consume a capability

Inject the public contract:

```php
use Mythos\Core\Media\Contracts\MediaManager;

final class StoreAsset
{
    public function __construct(private readonly MediaManager $media) {}
}
```

Application-specific adapters decide directories, route names, tokens and business lifecycle. Core handles validation, storage mechanics, idempotency and shared policies.

## Add a Core module

A Core module must include:

- a contract or documented framework boundary;
- an implementation hidden behind that boundary;
- configuration without application-specific defaults;
- unit tests;
- registration in `MythosCoreServiceProvider`;
- an entry in the reusable-component audit.

It must not import `App\` or `Modules\`, define Notre Jour routes, or reference wedding entities.

## Validation

Run PHPUnit, PHPStan, Pint, Composer audit, Blade compilation, Vite build and Laravel cache commands. The boundary test must pass before another application consumes the Core.
