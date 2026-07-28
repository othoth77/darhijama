<?php

namespace App\Filament\Components;

use Filament\Actions\Action;

class ConfirmationDialog
{
    public static function apply(
        Action $action,
        string $heading,
        string $description,
    ): Action {
        return $action
            ->requiresConfirmation()
            ->modalHeading($heading)
            ->modalDescription($description)
            ->modalSubmitActionLabel('Confirmer');
    }
}
