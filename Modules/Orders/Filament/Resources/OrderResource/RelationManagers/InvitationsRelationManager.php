<?php

namespace Modules\Orders\Filament\Resources\OrderResource\RelationManagers;

use App\Filament\Components\SharedActions;
use App\Filament\Components\SharedTableColumns;
use App\Filament\Components\StatusComponents;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Services\CreateInvitationService;
use Modules\Invitations\Services\PublishInvitationService;
use Modules\Orders\Models\Order;

/**
 * 1 commande -> N invitations (v2 pt. 4). La création passe par
 * CreateInvitationService (token ULID race-safe + copy-on-create du modèle,
 * voir PHASE_1.md §6-7), pas par la création Eloquent par défaut de Filament.
 */
class InvitationsRelationManager extends RelationManager
{
    protected static string $relationship = 'invitations';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')->label('Titre'),
            TextInput::make('groom_name')->label('Nom du marié')->required(),
            TextInput::make('bride_name')->label('Nom de la mariée')->required(),
            DateTimePicker::make('wedding_date')->label('Date du mariage')->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')->label('Titre'),
                TextColumn::make('public_token')->label('Token public'),
                StatusComponents::column(),
                SharedTableColumns::publishedAt(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data, RelationManager $livewire): Model {
                        /** @var Order $order */
                        $order = $livewire->getOwnerRecord();

                        return app(CreateInvitationService::class)->execute($order, $data);
                    }),
            ])
            ->actions([
                EditAction::make(),
                SharedActions::publish(
                    fn (Invitation $record) => app(PublishInvitationService::class)->execute($record),
                    fn (Invitation $record) => ! $record->isPublished(),
                ),
            ]);
    }
}
