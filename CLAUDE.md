# CLAUDE.md — Guide pour agents IA travaillant sur ce dépôt

Ce fichier oriente tout agent (Claude Code ou autre) qui intervient sur le code de Notre Jour.
Les documents sources de vérité produit restent `../Documentation/`, `../Prompts/PROMPT_MAITRE.txt`,
`../Technique/architecture.txt` et `../Contenu/landing.txt` (dossier parent du projet). Le rapport
`../Documentation/Notre_Jour_Rapport_Architecture.docx` contient l'analyse complète validée par le
Product Owner. Ne jamais contredire ces documents sans validation explicite.

## Contexte produit (à ne jamais perdre de vue)

- MVP mono-offre à 49 DT, zéro paiement en ligne, zéro compte client.
- Objectif n°1 : transformer chaque visiteur en conversation WhatsApp (+216 98 999 660).
- Tout le cycle de vente/modification se fait par WhatsApp ; le back-office est utilisé par des
  opérateurs internes, pas par les clients.
- Le code doit être pensé "Enterprise" : aucun raccourci, aucune dette technique volontaire,
  compatible plusieurs années sans réécriture majeure.

## Règle de gouvernance impérative

**À chaque étape de développement importante : s'arrêter, produire un rapport expliquant les
décisions techniques prises, et attendre la validation du Product Owner avant de continuer.**
Ne jamais enchaîner plusieurs phases du `ROADMAP.md` sans validation intermédiaire.

## Stack

Laravel 12 / PHP 8.3 / MySQL / Blade / Tailwind CSS v4 / Alpine.js / Vite.
Packages validés : `laravel/pennant`, `filament/filament`, `nwidart/laravel-modules`,
`spatie/laravel-permission`, `endroid/qr-code`, `intervention/image`, `league/flysystem-aws-s3-v3`.

## Architecture modulaire (nwidart/laravel-modules)

Chaque domaine métier vit sous `Modules/{Nom}/` avec sa propre structure **à plat** (sans dossier `app/`
intermédiaire — correction Phase 0.5, voir `AUDIT_PHASE_0.md`) : `Http`, `Models`, `Services`,
`Repositories/Contracts`, `Policies`, `Providers`, `Database/Migrations`, `Database/Factories`, `Database/Seeders`, `routes/web.php`,
`resources/views`. Voir `ARCHITECTURE.md` pour le détail des couches (Http → Services → Repositories →
Eloquent) et `DATABASE.md` pour le schéma.

Un module ne référence jamais directement une classe interne d'un autre module (Events/Listeners
uniquement — voir `ARCHITECTURE.md` §5). Les écrans back-office d'un module s'auto-enregistrent auprès
de Filament depuis leur propre `ServiceProvider` (voir `ARCHITECTURE.md` §6) — ne jamais modifier
`AdminPanelProvider` pour ajouter un module.

- **Modules core (toujours actifs)** : Landing, Templates, Orders, Invitations, Admin, Media.
- **Modules futurs (désactivés par défaut)** : RSVP, Guestbook, Timeline, Notifications, AI, Singles, Api.
  Leur code existe (structure + ServiceProvider + migrations si nécessaire) mais reste invisible tant
  que leur Feature Flag Pennant n'est pas actif — voir `config/features.php`.
- Le statut d'activation "structurel" (module chargé ou non par nwidart) est dans
  `modules_statuses.json` ; le statut "fonctionnel" (visible ou non) est dans Pennant. Ne jamais
  activer un module futur sans validation explicite du Product Owner.

## Règles de code non négociables

- Un Contrôleur ne contient pas de logique métier : il appelle un Service.
- Un Service ne parle jamais directement à Eloquent : il passe par une interface de Repository
  (`Repositories/Contracts/*Interface.php`), liée à son implémentation dans le ServiceProvider du
  module.
- Toute construction de lien WhatsApp passe par `App\Support\WhatsApp\WhatsAppLinkBuilder` (ou le
  helper `whatsapp_link()`) — jamais de `wa.me/...` codé en dur dans une vue.
- Tout upload de média passe par le module `Media` (S3/OVH), jamais d'écriture disque locale en
  production.
- Les URLs publiques d'invitation utilisent un token non énumérable (`public_token`), jamais un id
  auto-incrémenté seul.
- Le module `Singles` reste une coquille vide (structure + flag + migration éventuelle) tant que le
  Product Owner n'a pas fourni de spécification fonctionnelle.

## Commandes utiles (à exécuter sur une machine disposant de PHP 8.3 / Composer / Node — voir INSTALL.md)

```bash
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan module:list
npm run dev
```

## Documentation vivante à maintenir à jour

`CHANGELOG.md`, `TODO.md`, `ROADMAP.md`, `ARCHITECTURE.md`, `API.md`, `INSTALL.md`, `DATABASE.md`.
Toute modification structurante (nouveau module, nouvelle table, nouveau flag) doit être répercutée
dans ces fichiers dans le même lot de changements.
