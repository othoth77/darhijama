<?php

namespace Modules\Invitations\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum InvitationStatus: string implements HasColor, HasLabel
{
    case Brouillon = 'brouillon';
    case Publie = 'publie';
    case Archive = 'archive';

    public function getLabel(): string
    {
        return match ($this) {
            self::Brouillon => 'Brouillon',
            self::Publie => 'Publiée',
            self::Archive => 'Archivée',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Brouillon => 'gray',
            self::Publie => 'success',
            self::Archive => 'warning',
        };
    }
}
