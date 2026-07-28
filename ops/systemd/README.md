# Mythos OS systemd deployment

Create the unprivileged `mythos` account and `/var/log/mythos`, copy these units
to `/etc/systemd/system`, then run:

```bash
sudo install -d -o mythos -g mythos /var/log/mythos
sudo systemctl daemon-reload
sudo systemctl enable --now mythos-queue@1 mythos-queue@2 mythos-scheduler.timer
sudo systemctl status mythos-queue@1 mythos-scheduler.timer
```

Deployments reload workers gracefully after migrations:

```bash
sudo -u mythos php /var/www/mythos/current/artisan queue:restart
sudo systemctl reload-or-restart mythos-queue@1 mythos-queue@2
```

Observe and recover failures:

```bash
sudo -u mythos php /var/www/mythos/current/artisan queue:failed
sudo -u mythos php /var/www/mythos/current/artisan queue:retry <uuid>
journalctl -u mythos-queue@1 -u mythos-scheduler.service
```
