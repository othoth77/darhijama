# Production checklist

## Environnement

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://notrejour.tn`.
- Définir une `APP_KEY` unique et un `ADMIN_PASSWORD` fort.
- Configurer MySQL, le cache, les sessions et la queue sans valeurs locales.
- Définir `TRUSTED_HOSTS=notrejour.tn,www.notrejour.tn` et uniquement les adresses des reverse proxies dans `TRUSTED_PROXIES`.
- Définir `SESSION_SECURE_COOKIE=true`.
- Configurer le disque S3-compatible et vérifier lecture, écriture et suppression.

## Uploads

La limite de production est de 100 MB. Elle doit rester alignée entre :

- Laravel/Filament/Livewire : `102400` KiB ;
- Nginx : `client_max_body_size 100M`;
- PHP-FPM : `upload_max_filesize=100M` et `post_max_size=100M`.

## HTTPS et sécurité

- Installer le certificat aux chemins indiqués dans `notrejour.tn.nginx.conf`.
- Valider `nginx -t`, puis recharger Nginx.
- Vérifier la redirection HTTP vers HTTPS, HSTS, CSP et les en-têtes de sécurité.
- Examiner la CSP après chaque ajout de fournisseur externe.
- Exécuter `composer audit --locked`.

## Déploiement

```bash
composer install --no-dev --classmap-authoritative
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan queue:restart
```

Un worker doit traiter les queues `default,notifications`. Surveiller `failed_jobs`, les logs, `/up`, les réponses RSVP et les écritures analytics.
