<?php

/**
 * Registre déclaratif des Feature Flags de la plateforme (piloté par Laravel Pennant).
 *
 * Ce fichier est la source de vérité "humaine" : chaque module futur y est déclaré
 * avec sa clé de flag, son statut par défaut et une description courte. La valeur
 * réelle (activé/désactivé) est stockée en base (table `features`) et modifiable
 * depuis le back-office Filament sans redéploiement.
 *
 * Convention : un module "core" du MVP n'a pas besoin de flag (toujours actif).
 * Un module "futur" est toujours créé avec `default => false`.
 */

return [

    // Modules core du MVP — non gatés, toujours actifs. Clé purement documentaire/UI
    // (consommée par le futur écran Feature Flags du module Admin en Phase 1 pour
    // afficher "toujours actif" plutôt qu'un toggle) : ne conditionne aucun chargement
    // réel, qui reste piloté par modules_statuses.json (nwidart) — voir AUDIT_PHASE_0.md.
    'core_modules' => [
        'landing', 'templates', 'orders', 'invitations', 'admin', 'media',
    ],

    // Modules futurs — présents dans le code, désactivés par défaut.
    'flags' => [
        'rsvp' => [
            'label' => 'RSVP',
            'description' => 'Confirmation de présence des invités.',
            'default' => false,
        ],
        'guestbook' => [
            'label' => "Livre d'or",
            'description' => 'Messages laissés par les invités.',
            'default' => false,
        ],
        'timeline' => [
            'label' => 'Timeline',
            'description' => 'Déroulé de la journée / histoire du couple.',
            'default' => false,
        ],
        'notifications' => [
            'label' => 'Notifications',
            'description' => 'Notifications automatisées (email/SMS/WhatsApp).',
            'default' => false,
        ],
        'ai_album' => [
            'label' => 'Album IA',
            'description' => 'Génération et retouche assistées par IA.',
            'default' => false,
        ],
        'singles_corner' => [
            'label' => 'Singles Corner',
            'description' => 'Module hors mariage — périmètre à définir ultérieurement. Structure vide uniquement.',
            'default' => false,
        ],
        'public_api' => [
            'label' => 'API REST',
            'description' => 'Ouverture de la plateforme à des intégrations tierces (Sanctum).',
            'default' => false,
        ],
    ],

];
