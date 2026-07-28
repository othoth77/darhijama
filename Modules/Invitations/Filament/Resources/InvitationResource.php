<?php

namespace Modules\Invitations\Filament\Resources;

use App\Filament\Components\SharedTableColumns;
use App\Filament\Components\StatusComponents;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Filament\Resources\InvitationResource\Pages;
use Modules\Invitations\Filament\Resources\InvitationResource\RelationManagers\InvitationMediaRelationManager;
use Modules\Invitations\Filament\Resources\InvitationResource\RelationManagers\ProgramStepsRelationManager;
use Modules\Invitations\Filament\Resources\InvitationResource\RelationManagers\RsvpResponsesRelationManager;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Rules\SafeExternalUrl;
use Modules\Invitations\Services\InvitationPublicLinkService;
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
            Section::make('Invitation')->schema([
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
                Textarea::make('message')->columnSpanFull(),
                TextInput::make('dress_code')->label('Dress code'),
                Textarea::make('additional_info')->label('Informations publiques')->columnSpanFull(),
                TextInput::make('contact_name')->label('Contact public'),
                TextInput::make('contact_phone')->label('Téléphone public')->tel(),
            ])->columns(2),
            Section::make('Liens et médias externes')->schema([
                TextInput::make('maps_embed_url')->label('URL Google Maps')
                    ->rules([new SafeExternalUrl(['google.com', 'google.tn'])]),
                TextInput::make('external_video_url')->label('URL vidéo')
                    ->rules([new SafeExternalUrl(['youtube.com', 'youtu.be', 'youtube-nocookie.com', 'vimeo.com'])]),
                TextInput::make('external_audio_url')->label('URL audio')
                    ->rules([new SafeExternalUrl(['soundcloud.com', 'spotify.com', 'cdn.notrejour.tn'])]),
                TextInput::make('facebook_url')->label('Facebook')
                    ->rules([new SafeExternalUrl(['facebook.com'])]),
                TextInput::make('instagram_url')->label('Instagram')
                    ->rules([new SafeExternalUrl(['instagram.com'])]),
                TextInput::make('lat')->label('Latitude')->numeric()->minValue(-90)->maxValue(90),
                TextInput::make('lng')->label('Longitude')->numeric()->minValue(-180)->maxValue(180),
            ])->columns(2),
            Section::make('Publication')->schema([
                Placeholder::make('public_url')->label('URL publique')
                    ->content(fn (?Invitation $record): string => $record
                        ? app(InvitationPublicLinkService::class)->show($record)
                        : 'Disponible après création'),
                Placeholder::make('qr_code')->label('QR code')
                    ->content(fn (?Invitation $record): string => $record?->qr_code_path ?: 'Non généré'),
                Placeholder::make('publication_date')->label('Date de publication')
                    ->content(fn (?Invitation $record): string => $record?->published_at?->format('d/m/Y H:i') ?: 'Non publiée'),
                Placeholder::make('rsvp_statistics')->label('Statistiques RSVP')
                    ->content(function (?Invitation $record): string {
                        if (! $record) {
                            return 'Aucune réponse';
                        }

                        $present = $record->rsvpResponses()->where('status', 'present')->count();
                        $absent = $record->rsvpResponses()->where('status', 'absent')->count();

                        return "{$present} présent(s), {$absent} absent(s)";
                    }),
            ])->columns(2)->visibleOn('edit'),
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
            ProgramStepsRelationManager::class,
            InvitationMediaRelationManager::class,
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
