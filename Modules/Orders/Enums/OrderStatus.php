<?php

namespace Modules\Orders\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasColor, HasLabel
{
    case Nouveau = 'nouveau';
    case ContactWhatsapp = 'contact_whatsapp';
    case Paye = 'paye';
    case EnProduction = 'en_production';
    case Publie = 'publie';
    case Livre = 'livre';
    case Annule = 'annule';

    public function getLabel(): string
    {
        return match ($this) {
            self::Nouveau => 'Nouveau',
            self::ContactWhatsapp => 'Contact WhatsApp',
            self::Paye => 'Payé',
            self::EnProduction => 'En production',
            self::Publie => 'Publié',
            self::Livre => 'Livré',
            self::Annule => 'Annulé',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Nouveau => 'gray',
            self::ContactWhatsapp => 'info',
            self::Paye => 'success',
            self::EnProduction => 'warning',
            self::Publie => 'success',
            self::Livre => 'success',
            self::Annule => 'danger',
        };
    }
}
