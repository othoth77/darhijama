# API.md — Notre Jour

## Statut

Aucune API publique au MVP. Le module `Api` (flag Pennant `public_api`) existe uniquement comme
coquille structurelle (`Modules/Api/`) et reste désactivé. Ce document sera complété au moment de son
développement effectif.

## Conventions déjà actées pour la future API REST

- Authentification via `laravel/sanctum` (tokens personnels), table `personal_access_tokens`.
- Toutes les routes sous `routes/api.php` du module `Api`, préfixe `/api/v1/`.
- Réponses au format JSON:API-like minimal : `{ "data": ..., "meta": ... }`.
- Aucune route API n'est enregistrée tant que `Feature::active('public_api')` est faux — voir
  `Modules/Api/app/Providers/ApiServiceProvider.php`.

## Endpoint interne existant (hors module Api)

### `POST /analytics/whatsapp-click`

Utilisé par `resources/js/app.js` (Alpine `whatsappCta`) pour tracker un clic sur un CTA WhatsApp.
Non authentifié, rate-limité, n'écrit que dans `whatsapp_click_events`. Détaillé et implémenté en
Phase 1 (module Admin / Dashboard Analytics).

| Champ (JSON) | Type | Description |
|---|---|---|
| source | string | `landing`, `template_demo`, `invitation` |
| template_id | int nullable | |
| invitation_id | int nullable | |
