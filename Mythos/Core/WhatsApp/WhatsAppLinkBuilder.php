<?php

namespace Mythos\Core\WhatsApp;

/**
 * Point d'entrée unique pour construire un lien wa.me.
 *
 * Objectif business n°1 du MVP : transformer chaque visiteur en conversation
 * WhatsApp. Toutes les vues (Landing, Templates, Invitations) doivent passer
 * par ce builder plutôt que de recomposer l'URL à la main, afin de garder le
 * numéro et le message centralisés dans config/whatsapp.php.
 */
class WhatsAppLinkBuilder
{
    public function __construct(
        private readonly string $phoneE164,
        private readonly string $defaultMessage,
    ) {}

    public static function make(): self
    {
        return new self(
            phoneE164: (string) config('whatsapp.phone_e164'),
            defaultMessage: (string) config('whatsapp.default_message'),
        );
    }

    /**
     * @param  string|null  $message  Message personnalisé (ex. contexte invitation). Sinon message par défaut.
     */
    public function link(?string $message = null): string
    {
        $text = rawurlencode($message ?? $this->defaultMessage);

        return "https://wa.me/{$this->phoneE164}?text={$text}";
    }

    public function messageForTemplate(string $templateName): string
    {
        return "Bonjour Notre Jour, je souhaite commander une invitation digitale à 49 DT avec le modèle « {$templateName} ».";
    }
}
