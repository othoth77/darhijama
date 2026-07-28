<?php

namespace Modules\Invitations\Filament\Resources\InvitationResource\RelationManagers;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Mythos\Core\Media\Contracts\MediaManager as MediaService;
use Mythos\Core\Media\Models\Media;

class InvitationMediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $title = 'Galerie, audio et vidéo';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')->label('Type')->badge(),
                TextColumn::make('original_name')->label('Fichier')->default('Média'),
                TextColumn::make('order')->label('Ordre')->sortable(),
            ])
            ->defaultSort('order')
            ->headerActions([
                CreateAction::make('upload')
                    ->label('Ajouter un média')
                    ->form([
                        Select::make('type')->options([
                            'image' => 'Image',
                            'audio' => 'Audio',
                            'video' => 'Vidéo',
                        ])->required()->live(),
                        FileUpload::make('upload')
                            ->label('Fichier')
                            ->storeFiles(false)
                            ->maxSize(102400)
                            ->required(),
                        TextInput::make('order')->numeric()->default(0)->required(),
                    ])
                    ->using(function (array $data): Media {
                        /** @var TemporaryUploadedFile $file */
                        $file = $data['upload'];

                        return app(MediaService::class)->uploadFor(
                            $this->getOwnerRecord(),
                            $file,
                            $data['type'],
                            'invitations/media',
                            $data['type'],
                            order: (int) $data['order'],
                        );
                    }),
            ])
            ->actions([
                Action::make('delete')
                    ->label('Supprimer')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Media $record) => app(MediaService::class)->deleteMedia($record)),
            ]);
    }
}
