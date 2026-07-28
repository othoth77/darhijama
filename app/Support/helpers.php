<?php

use Mythos\Core\WhatsApp\WhatsAppLinkBuilder;

if (! function_exists('whatsapp_link')) {
    /**
     * Raccourci global pour générer un lien wa.me depuis n'importe quelle vue Blade.
     * Exemple : {{ whatsapp_link() }} ou {{ whatsapp_link('Message personnalisé') }}.
     */
    function whatsapp_link(?string $message = null): string
    {
        return WhatsAppLinkBuilder::make()->link($message);
    }
}
