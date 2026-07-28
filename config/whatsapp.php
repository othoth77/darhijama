<?php

/**
 * Source unique de vérité pour le canal WhatsApp — objectif business n°1 du MVP :
 * transformer chaque visiteur en conversation WhatsApp.
 *
 * Toute vue ou contrôleur doit passer par App\Support\WhatsApp\WhatsAppLinkBuilder
 * plutôt que de reconstruire un lien wa.me à la main.
 */

return [

    // Numéro au format E.164 sans le "+" (format attendu par wa.me).
    'phone_e164' => env('WHATSAPP_PHONE_E164', '21698999660'),

    // Message préformaté par défaut (encodé automatiquement par le builder).
    'default_message' => env(
        'WHATSAPP_DEFAULT_MESSAGE',
        'Bonjour Notre Jour, Je souhaite commander une invitation digitale à 49 DT.'
    ),

    // Offre unique du MVP — affichée sur la landing et les CTA.
    'offer' => [
        'price' => (float) env('OFFER_PRICE_DT', 49),
        'currency' => env('OFFER_CURRENCY', 'DT'),
    ],

];
