<?php

namespace Modules\Invitations\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RsvpStatus: string implements HasColor, HasLabel
{
    case Present = 'present';
    case Absent = 'absent';

    public function getLabel(): string
    {
        return match ($this) {
            self::Present => 'Présent',
            self::Absent => 'Absent',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Present => 'success',
            self::Absent => 'danger',
        };
    }
}
