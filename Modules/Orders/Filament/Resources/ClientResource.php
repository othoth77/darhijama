<?php

namespace Modules\Orders\Filament\Resources;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Orders\Filament\Resources\ClientResource\Pages;
use Modules\Orders\Models\Client;
use Mythos\Core\UI\Filament\SharedTableColumns;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Commandes';

    protected static ?string $navigationLabel = 'Clients';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Nom')
                ->required(),
            TextInput::make('whatsapp_phone')
                ->label('Téléphone WhatsApp')
                ->required()
                ->helperText('Saisie libre — normalisé automatiquement à l\'enregistrement.'),
            Textarea::make('notes')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nom')->searchable(),
                TextColumn::make('whatsapp_phone')->label('Téléphone'),
                TextColumn::make('whatsapp_phone_normalized')->label('Normalisé')->toggleable(),
                TextColumn::make('orders_count')->counts('orders')->label('Commandes'),
                SharedTableColumns::createdAt(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
