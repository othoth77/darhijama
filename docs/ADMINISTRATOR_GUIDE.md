# Mythos OS administrator guide

## Daily operation

- Use `/admin` with an authorized account.
- Monitor `/up`, queue depth, failed jobs, application logs and database health.
- Keep workers running for `default,notifications`.
- Review audit logs and database notifications.

## Security

- Use HTTPS, HSTS and the supplied security headers.
- Configure trusted hosts/proxies and secure cookies.
- Rotate secrets and object-storage credentials.
- Keep `APP_DEBUG=false` in production.
- Run Composer audit before every release.

## Data protection

- Back up MySQL and media storage.
- Test restoration regularly.
- Never use production or VPS databases for automated tests.
- Preserve public tokens and media paths during recovery.

## Maintenance

Use immutable releases, locked dependencies and the release checklist. Restart queue workers after deployment. Rebuild config, route and view caches. Verify public invitations, QR codes, RSVP and admin authorization after each release.
