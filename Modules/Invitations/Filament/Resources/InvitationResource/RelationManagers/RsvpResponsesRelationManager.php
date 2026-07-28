<?php

namespace Modules\Invitations\Filament\Resources\InvitationResource\RelationManagers;

use App\Filament\Components\SharedTableColumns;
use App\Filament\Components\StatusComponents;
use Filament\Actions\DeleteAction;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Lecture seule côté admin (Phase 3) : les réponses RSVP sont exclusivement
 * soumises par les invités depuis la page publique (SubmitRsvpService), jamais
 * créées ou modifiées depuis Filament — seule la suppression est proposée
 * (ex. réponse test ou indésirable).
 */
class RsvpResponsesRelationManager extends RelationManager
{
    protected static string $relationship = 'rsvpResponses';

    protected static ?string $title = 'Réponses RSVP';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Nom'),
                StatusComponents::column()->label('Réponse'),
                TextColumn::make('phone')->label('Téléphone'),
                TextColumn::make('guests_count')->label('Accompagnants'),
                TextColumn::make('comment')->label('Commentaire')->limit(40),
                SharedTableColumns::createdAt('Reçue le'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                DeleteAction::make(),
            ]);
    }
}
