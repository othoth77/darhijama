<?php

namespace Mythos\Core\UI\Filament;

use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class StatusComponents
{
    /**
     * @param  class-string<BackedEnum>  $enum
     */
    public static function field(string $enum, BackedEnum|string|null $default = null): Select
    {
        return Select::make('status')
            ->label('Statut')
            ->options(self::options($enum))
            ->default($default instanceof BackedEnum ? $default->value : $default)
            ->required();
    }

    public static function column(): TextColumn
    {
        return TextColumn::make('status')->label('Statut')->badge();
    }

    /**
     * @param  class-string<BackedEnum>  $enum
     */
    public static function filter(string $enum): SelectFilter
    {
        return SelectFilter::make('status')
            ->label('Statut')
            ->options(self::options($enum));
    }

    /**
     * @param  class-string<BackedEnum>  $enum
     */
    private static function options(string $enum): array
    {
        return collect($enum::cases())->mapWithKeys(
            fn (BackedEnum $status) => [
                $status->value => method_exists($status, 'getLabel')
                    ? $status->getLabel()
                    : ucfirst((string) $status->value),
            ],
        )->all();
    }
}
