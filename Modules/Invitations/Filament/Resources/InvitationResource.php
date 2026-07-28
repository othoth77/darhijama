<?php

namespace Modules\Invitations\Filament\Resources;

use App\Filament\Components\SharedTableColumns;
use App\Filament\Components\StatusComponents;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Filament\Resources\InvitationResource\Pages;
use Modules\Invitations\Filament\Resources\InvitationResource\RelationManagers\RsvpResponsesRelationManager;
use Modules\Invitations\Models\Invitation;
use Modules\Templates\Models\Template;

class InvitationResource extends Resource
{
    protected static ?string $model = Invitation::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope-open';

    protected static ?string $navigationGroup = 'Invitations';

    protected static ?string $navigationLabel = 'Invitations';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('order_id')
                ->label('Commande')
                ->relationship('order', 'reference')
                ->searchable()
                ->required()
                ->disabledOn('edit'),
            Select::make('template_id')
                ->label('Modèle')
                ->options(Template::query()->pluck('name', 'id'))
                ->searchable(),
            TextInput::make('title')->label('Titre'),
            TextInput::make('groom_name')->label('Nom du marié')->required(),
            TextInput::make('bride_name')->label('Nom de la mariée')->required(),
            DateTimePicker::make('wedding_date')->label('Date du mariage')->required(),
            TextInput::make('venue_name')->label('Lieu'),
            TextInput::make('venue_address')->label('Adresse du lieu'),
            TextInput::make('maps_embed_url')->label('URL Google Maps (iframe)'),
            Textarea::make('message')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Titre')->searchable(),
                TextColumn::make('order.reference')->label('Commande')->searchable(),
                TextColumn::make('public_token')->label('Token public')->copyable(),
                StatusComponents::column(),
                TextColumn::make('wedding_date')->label('Date du mariage')->date(),
                SharedTableColumns::publishedAt(),
            ])
            ->filters([StatusComponents::filter(InvitationStatus::class)])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RsvpResponsesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvitations::route('/'),
            'create' => Pages\CreateInvitation::route('/create'),
            'edit' => Pages\EditInvitation::route('/{record}/edit'),
        ];
    }
}
