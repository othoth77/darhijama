<?php

namespace Modules\Orders\Filament\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Filament\Resources\OrderResource\Pages;
use Modules\Orders\Filament\Resources\OrderResource\RelationManagers\InvitationsRelationManager;
use Modules\Orders\Models\Order;
use Modules\Templates\Models\Template;
use Mythos\Core\UI\Filament\SharedTableColumns;
use Mythos\Core\UI\Filament\StatusComponents;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Commandes';

    protected static ?string $navigationLabel = 'Commandes';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('client_id')
                ->label('Client')
                ->relationship('client', 'name')
                ->searchable()
                ->required()
                ->createOptionForm([
                    TextInput::make('name')->label('Nom')->required(),
                    TextInput::make('whatsapp_phone')->label('Téléphone WhatsApp')->required(),
                ]),
            Select::make('template_id')
                ->label('Modèle choisi')
                ->options(Template::query()->pluck('name', 'id'))
                ->searchable(),
            TextInput::make('subtotal')
                ->label('Sous-total (TND)')
                ->numeric()
                ->required()
                ->default(fn () => config('whatsapp.offer.price', 49)),
            TextInput::make('discount')
                ->label('Remise (TND)')
                ->numeric()
                ->default(0),
            Select::make('payment_method')
                ->label('Moyen de paiement')
                ->options([
                    'especes' => 'Espèces',
                    'virement' => 'Virement',
                    'autre' => 'Autre',
                ]),
            StatusComponents::field(OrderStatus::class, OrderStatus::Nouveau),

            Textarea::make('notes')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->label('Référence')->searchable(),
                TextColumn::make('client.name')->label('Client')->searchable(),
                TextColumn::make('total')->label('Total')->money('TND'),
                StatusComponents::column(),
                SharedTableColumns::createdAt('Créée le'),
            ])
            ->filters([StatusComponents::filter(OrderStatus::class)])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            InvitationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
