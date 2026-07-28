<?php

namespace Modules\Invitations\Filament\Resources\InvitationResource\RelationManagers;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProgramStepsRelationManager extends RelationManager
{
    protected static string $relationship = 'programSteps';

    protected static ?string $title = 'Programme';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('time')->label('Heure')->type('time'),
            TextInput::make('title')->label('Titre')->required()->maxLength(255),
            Textarea::make('description')->label('Description')->maxLength(1000),
            TextInput::make('order')->label('Ordre')->numeric()->default(0)->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('time')->label('Heure'),
                TextColumn::make('title')->label('Étape'),
                TextColumn::make('description')->limit(50),
                TextColumn::make('order')->label('Ordre')->sortable(),
            ])
            ->reorderable('order')
            ->headerActions([CreateAction::make()])
            ->actions([EditAction::make(), DeleteAction::make()]);
    }
}
