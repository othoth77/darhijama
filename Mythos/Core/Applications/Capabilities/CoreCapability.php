<?php

namespace Mythos\Core\Applications\Capabilities;

enum CoreCapability: string
{
    case Identity = 'identity';
    case Authorization = 'authorization';
    case Media = 'media';
    case Notifications = 'notifications';
    case Analytics = 'analytics';
    case Audit = 'audit';
    case PublicLinks = 'public-links';
    case QrCode = 'qr';
    case WhatsApp = 'whatsapp';
    case SharedUi = 'shared-ui';
}
