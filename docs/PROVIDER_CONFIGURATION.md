# Production provider configuration

External providers are intentionally disabled until credentials and contracts
are approved. Never store credentials in the repository.

## Email

Configure Laravel SMTP or an approved transactional mail transport using the
`MAIL_*` variables in `.env.production.example`. Verify SPF, DKIM, DMARC,
bounce handling, suppression lists, TLS, sender identity, and provider rate
limits. Enable the email notification channel only after a sandbox delivery,
retry, duplicate, and failure-observability test succeeds.

## WhatsApp

Select an approved WhatsApp Business Solution Provider. Store its access token,
phone-number ID, webhook verification secret, and signing secret in the
deployment secret manager. Verify webhook signatures, template approval,
consent, opt-out handling, delivery callbacks, retries, idempotency, and data
retention. Do not enable `whatsapp` in
`DAR_HIJAMA_NOTIFICATION_CHANNELS` until a Core-compatible channel adapter is
implemented and reviewed.

## Payment sandbox

No Dar Hijama payment workflow or callback route exists in this release.
Before introducing one, select a PCI-compliant hosted-checkout provider and
document sandbox credentials, webhook signatures, idempotency keys, replay
protection, amount/currency verification, refund handling, reconciliation, and
secret rotation. Never collect or store card data in Mythos OS.
