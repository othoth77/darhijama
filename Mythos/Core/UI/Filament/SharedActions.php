<?php

namespace Mythos\Core\UI\Filament;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\RestoreAction;
use Illuminate\Database\Eloquent\Model;

class SharedActions
{
    public static function publish(Closure $callback, ?Closure $visible = null): Action
    {
        $action = Action::make('publish')
            ->label('Publier')
            ->icon('heroicon-o-globe-alt')
            ->color('success')
            ->action($callback);

        if ($visible) {
            $action->visible($visible);
        }

        return ConfirmationDialog::apply(
            $action,
            'Publier cet élément ?',
            'Le lien public deviendra accessible immédiatement.',
        );
    }

    public static function archive(Closure $callback, ?Closure $visible = null): Action
    {
        $action = Action::make('archive')
            ->label('Archiver')
            ->icon('heroicon-o-archive-box')
            ->color('warning')
            ->action($callback);

        if ($visible) {
            $action->visible($visible);
        }

        return ConfirmationDialog::apply(
            $action,
            'Archiver cet élément ?',
            'Il ne sera plus accessible publiquement.',
        );
    }

    public static function restore(): RestoreAction
    {
        return RestoreAction::make()
            ->label('Restaurer')
            ->requiresConfirmation()
            ->modalHeading('Restaurer cet élément ?')
            ->modalSubmitActionLabel('Restaurer');
    }

    public static function copyPublicLink(Closure $url): Action
    {
        return Action::make('copyPublicLink')
            ->label('Copier le lien public')
            ->icon('heroicon-o-link')
            ->action(fn (): null => null)
            ->extraAttributes(fn (Model $record): array => [
                'x-on:click' => 'navigator.clipboard.writeText('.json_encode($url($record), JSON_THROW_ON_ERROR).')',
            ]);
    }
}
