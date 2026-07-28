<?php

namespace Modules\Invitations\Filament\Resources\InvitationResource\Pages;

use App\Filament\Components\SharedActions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Filament\Resources\InvitationResource;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Services\DuplicateInvitationService;
use Modules\Invitations\Services\InvitationPreviewLinkService;
use Modules\Invitations\Services\InvitationPublicLinkService;
use Modules\Invitations\Services\InvitationQrCodeService;
use Modules\Invitations\Services\InvitationWorkflowService;
use Modules\Invitations\Services\PublishInvitationService;

class EditInvitation extends EditRecord
{
    protected static string $resource = InvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Aperçu')
                ->icon('heroicon-o-eye')
                ->url(fn (Invitation $record) => app(InvitationPreviewLinkService::class)->temporary($record))
                ->openUrlInNewTab(),
            SharedActions::publish(
                function (Invitation $record): void {
                    app(PublishInvitationService::class)->execute($record);
                    $this->fillForm();
                },
                fn (Invitation $record) => $record->status === InvitationStatus::Brouillon,
            ),
            SharedActions::archive(
                function (Invitation $record): void {
                    app(InvitationWorkflowService::class)->archive($record);
                    $this->fillForm();
                },
                fn (Invitation $record) => $record->isPublished(),
            ),
            Action::make('restoreInvitation')
                ->label('Restaurer')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->visible(fn (Invitation $record) => $record->status === InvitationStatus::Archive)
                ->action(function (Invitation $record): void {
                    app(InvitationWorkflowService::class)->restore($record);
                    $this->fillForm();
                }),
            Action::make('duplicate')
                ->label('Dupliquer')
                ->icon('heroicon-o-document-duplicate')
                ->requiresConfirmation()
                ->action(function (Invitation $record) {
                    $copy = app(DuplicateInvitationService::class)->execute($record);

                    return redirect(InvitationResource::getUrl('edit', ['record' => $copy]));
                }),
            SharedActions::copyPublicLink(
                fn (Invitation $record): string => app(InvitationPublicLinkService::class)->show($record),
            )->visible(fn (Invitation $record) => $record->isPublished()),
            Action::make('regenerateQr')
                ->label('Régénérer le QR')
                ->icon('heroicon-o-qr-code')
                ->requiresConfirmation()
                ->visible(fn (Invitation $record) => $record->isPublished())
                ->action(function (Invitation $record): void {
                    app(InvitationQrCodeService::class)->regenerate($record);
                    $this->fillForm();
                }),
            DeleteAction::make(),
        ];
    }
}
