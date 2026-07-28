<?php

namespace Applications\DarHijama\Filament\Resources;

use Applications\DarHijama\Domain\Appointment;
use Applications\DarHijama\Filament\Resources\AppointmentResource\Pages;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Dar Hijama';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('reference')->disabled(),
            Select::make('patient_id')->relationship('patient', 'reference')->searchable()->required(),
            Select::make('practitioner_id')->relationship('practitioner', 'name')->searchable()->required(),
            Select::make('type')->options([
                'initial_consultation' => 'Initial consultation',
                'hijama_session' => 'Hijama session',
                'follow_up' => 'Follow-up',
                'home_visit' => 'Home visit',
            ])->required(),
            Select::make('visit_mode')->options(['clinic' => 'Clinic', 'home' => 'Home'])->required(),
            DateTimePicker::make('starts_at')->required(),
            TextInput::make('expected_duration_minutes')->numeric()->minValue(15)->maxValue(240),
            Select::make('source')->options([
                'administration' => 'Administration', 'phone' => 'Phone',
                'whatsapp' => 'WhatsApp', 'website' => 'Website', 'walk_in' => 'Walk-in',
            ])->required(),
            TextInput::make('governorate'),
            TextInput::make('city'),
            Textarea::make('address')->columnSpanFull(),
            Textarea::make('location_notes')->columnSpanFull(),
            Textarea::make('patient_visible_notes')->columnSpanFull(),
            Textarea::make('internal_notes')->columnSpanFull()
                ->visible(fn (): bool => auth()->user()?->can('dar-hijama.appointments.view-private-notes') ?? false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable()->copyable(),
                TextColumn::make('starts_at')->dateTime()->sortable(),
                TextColumn::make('patient.reference')->label('Patient')->searchable(),
                TextColumn::make('practitioner.name')->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('original_appointment_id')->label('Follow-up of'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft', 'pending' => 'Pending', 'confirmed' => 'Confirmed',
                    'practitioner_assigned' => 'Assigned', 'in_progress' => 'In progress',
                    'completed' => 'Completed', 'cancelled' => 'Cancelled',
                    'no_show' => 'No-show', 'rescheduled' => 'Rescheduled',
                ]),
                SelectFilter::make('type')->options([
                    'initial_consultation' => 'Initial consultation',
                    'hijama_session' => 'Hijama session',
                    'follow_up' => 'Follow-up',
                    'home_visit' => 'Home visit',
                ]),
            ])
            ->defaultSort('starts_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
