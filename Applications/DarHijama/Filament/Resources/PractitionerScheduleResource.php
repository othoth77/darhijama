<?php

namespace Applications\DarHijama\Filament\Resources;

use Applications\DarHijama\Domain\PractitionerSchedule;
use Applications\DarHijama\Filament\Resources\PractitionerScheduleResource\Pages;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PractitionerScheduleResource extends Resource
{
    protected static ?string $model = PractitionerSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Dar Hijama';

    protected static ?string $navigationLabel = 'Availability';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('practitioner_id')->relationship('practitioner', 'name')->required(),
            Select::make('day_of_week')->options([
                1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday',
                5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday',
            ])->required(),
            TextInput::make('starts_at')->type('time')->required(),
            TextInput::make('ends_at')->type('time')->required(),
            TextInput::make('break_starts_at')->type('time'),
            TextInput::make('break_ends_at')->type('time'),
            TextInput::make('service_area'),
            Toggle::make('home_visits'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('practitioner.name')->searchable(),
            TextColumn::make('day_of_week'),
            TextColumn::make('starts_at'),
            TextColumn::make('ends_at'),
            TextColumn::make('service_area'),
            IconColumn::make('home_visits')->boolean(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPractitionerSchedules::route('/'),
            'create' => Pages\CreatePractitionerSchedule::route('/create'),
            'edit' => Pages\EditPractitionerSchedule::route('/{record}/edit'),
        ];
    }
}
