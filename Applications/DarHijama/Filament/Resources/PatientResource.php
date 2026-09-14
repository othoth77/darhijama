<?php

namespace Applications\DarHijama\Filament\Resources;

use Applications\DarHijama\Domain\Patient;
use Applications\DarHijama\Filament\Resources\PatientResource\Pages;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Dar Hijama';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Patient profile')->schema([
                TextInput::make('reference')->disabled(),
                TextInput::make('first_name')->required()->maxLength(100),
                TextInput::make('last_name')->required()->maxLength(100),
                Select::make('gender')->options([
                    'female' => 'Female', 'male' => 'Male',
                    'other' => 'Other', 'undisclosed' => 'Undisclosed',
                ]),
                DatePicker::make('date_of_birth')->maxDate(now()),
                TextInput::make('phone')->tel(),
                TextInput::make('secondary_phone')->tel(),
                TextInput::make('email')->email(),
                TextInput::make('governorate'),
                TextInput::make('city'),
                Textarea::make('address')->columnSpanFull(),
                Select::make('preferred_language')->options([
                    'ar' => 'Arabic', 'fr' => 'French', 'en' => 'English',
                ])->default('fr'),
                Toggle::make('active')->default(true),
                Select::make('consent_status')->options([
                    'pending' => 'Pending', 'granted' => 'Granted', 'withdrawn' => 'Withdrawn',
                ]),
            ])->columns(2),
            Section::make('Private notes')->schema([
                Textarea::make('notes')->columnSpanFull(),
                TextInput::make('emergency_contact_name'),
                TextInput::make('emergency_contact_phone')->tel(),
            ])->visible(fn (): bool => auth()->user()?->can('dar-hijama.patients.view-private-notes') ?? false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('reference')->searchable()->copyable(),
            TextColumn::make('first_name')->searchable(),
            TextColumn::make('last_name')->searchable(),
            TextColumn::make('phone')->searchable(),
            TextColumn::make('city')->searchable(),
            IconColumn::make('active')->boolean(),
            TextColumn::make('consent_status')->badge(),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }
}
