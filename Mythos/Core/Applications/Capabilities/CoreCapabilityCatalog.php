<?php

namespace Mythos\Core\Applications\Capabilities;

use Mythos\Core\Analytics\Contracts\AnalyticsRecorder;
use Mythos\Core\Audit\Contracts\AuditLogger;
use Mythos\Core\Identity\Models\User;
use Mythos\Core\Media\Contracts\MediaManager;
use Mythos\Core\Notifications\Contracts\NotificationDispatcher;
use Mythos\Core\PublicLinks\Contracts\PublicLinkGenerator;
use Mythos\Core\QrCode\Contracts\QrCodeGenerator;
use Mythos\Core\WhatsApp\WhatsAppLinkBuilder;

final class CoreCapabilityCatalog
{
    /** @return array<string, class-string|null> */
    public function contracts(): array
    {
        return [
            CoreCapability::Identity->value => User::class,
            CoreCapability::Authorization->value => null,
            CoreCapability::Media->value => MediaManager::class,
            CoreCapability::Notifications->value => NotificationDispatcher::class,
            CoreCapability::Analytics->value => AnalyticsRecorder::class,
            CoreCapability::Audit->value => AuditLogger::class,
            CoreCapability::PublicLinks->value => PublicLinkGenerator::class,
            CoreCapability::QrCode->value => QrCodeGenerator::class,
            CoreCapability::WhatsApp->value => WhatsAppLinkBuilder::class,
            CoreCapability::SharedUi->value => null,
        ];
    }

    public function supports(string $capability): bool
    {
        return array_key_exists($capability, $this->contracts());
    }

    public function contract(string $capability): ?string
    {
        return $this->contracts()[$capability] ?? null;
    }
}
