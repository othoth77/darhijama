<?php

namespace Applications\DarHijama\Filament\Resources;

use Applications\DarHijama\Domain\Practitioner;
use Applications\DarHijama\Filament\Resources\PractitionerResource\Pages;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PractitionerResource extends Resource
{
    protected static ?string $model = Practitioner::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Dar Hijama';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required(),
            TextInput::make('email')->email(),
            TextInput::make('phone')->tel(),
            TextInput::make('license_number')->unique(ignoreRecord: true),
            Toggle::make('active')->default(true),
            Toggle::make('home_visits'),
            TextInput::make('maximum_daily_appointments')->numeric()->minValue(1),
            TextInput::make('appointment_duration_minutes')->numeric()->minValue(15),
            TextInput::make('preparation_buffer_minutes')->numeric()->minValue(0),
            TextInput::make('travel_buffer_minutes')->numeric()->minValue(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('license_number')->searchable(),
            IconColumn::make('active')->boolean(),
            IconColumn::make('home_visits')->boolean(),
            TextColumn::make('maximum_daily_appointments'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPractitioners::route('/'),
            'create' => Pages\CreatePractitioner::route('/create'),
            'edit' => Pages\EditPractitioner::route('/{record}/edit'),
        ];
    }
}
