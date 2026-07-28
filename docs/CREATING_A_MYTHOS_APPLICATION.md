# Creating a Mythos application

```bash
php artisan mythos:make-application Example
```

The generator creates:

```text
Applications/Example/
├── Application.php
├── Providers/
├── Contracts/
├── Domain/
├── Application/
├── Infrastructure/
├── Http/
├── Policies/
├── routes/
├── database/migrations/
├── resources/views/
├── resources/lang/
├── config/
├── tests/Feature/
└── mythos.json
```

After review, add `base_path('Applications/Example/mythos.json')` to `config/mythos.php`.

Validate and inspect:

```bash
php artisan mythos:applications
php artisan mythos:application:validate example
php artisan mythos:application:inspect example
```

Declare only required capabilities. Inject their contracts, keep domain code inside the application namespace, namespace permissions with the application slug, and add application health and architecture tests.

Do not copy Notre Jour code, import Core implementations, import `App\` or `Modules\`, or access another application namespace.
