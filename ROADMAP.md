# ROADMAP.md — Notre Jour

Chemin critique commercial : Phases 0 à 5. Chaque phase se termine par un arrêt, un rapport de
décisions techniques, et une validation du Product Owner avant la suivante (règle de gouvernance,
voir `CLAUDE.md`).

| Phase | Contenu | Statut |
|---|---|---|
| Phase 0 — Socle | Bootstrap Laravel 12, composer.json, config Feature Flags/Modules/S3/WhatsApp, squelette des 13 modules (6 core + 7 futurs), documentation vivante | **Terminée — en attente de validation** |
| Phase 1 — Modules core (logique métier) | Migrations et modèles Templates/Orders/Invitations, Repository+Service par module, back-office Filament (Resources), auth admin | À venir |
| Phase 2 — Landing + Templates | Landing publique (contenu `landing.txt`), galerie de modèles, page de démonstration, CRUD modèles en back-office, upload S3 | À venir |
| Phase 3 — Orders + Invitations + Admin | CRUD commandes, création/édition d'invitation par l'opérateur, page publique d'invitation, statuts, génération token | À venir |
| Phase 4 — QR / Maps / Compte à rebours / WhatsApp / Analytics | Génération QR (endroid), embed Maps, countdown Alpine, tracking clics WhatsApp + Dashboard Analytics | À venir |
| Phase 5 — Qualité & mise en ligne | Tests Feature, audit responsive/SEO/sécurité, hardening, déploiement, DNS | À venir |
| Phase 6 — Post-lancement | Développement effectif des modules futurs (RSVP, Guestbook, Timeline, Notifications, AI, Api) un par un, sur validation explicite. Singles reste en coquille jusqu'à spécification | Hors chemin critique |

## Décisions ouvertes reportées à des phases futures

- Spécification fonctionnelle du module Singles Corner (bloquant pour tout développement au-delà de
  la coquille structurelle).
- Interface Filament d'activation des Feature Flags (prévue Phase 1, dans le module Admin).
- Choix définitif entre embed Maps gratuit et API Maps payante (réévalué en Phase 4 selon besoin
  réel).
