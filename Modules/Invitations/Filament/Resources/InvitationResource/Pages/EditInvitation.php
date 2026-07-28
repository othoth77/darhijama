<?php

namespace Modules\Invitations\Filament\Resources\InvitationResource\Pages;

use App\Filament\Components\SharedActions;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Filament\Resources\InvitationResource;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Services\InvitationPublicLinkService;
use Modules\Invitations\Services\PublishInvitationService;

class EditInvitation extends EditRecord
{
    protected static string $resource = InvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SharedActions::publish(
                function (Invitation $record): void {
                    app(PublishInvitationService::class)->execute($record);
                    $this->fillForm();
                },
                fn (Invitation $record) => ! $record->isPublished(),
            ),
            SharedActions::archive(
                fn (Invitation $record) => $record->update(['status' => InvitationStatus::Archive]),
                fn (Invitation $record) => $record->isPublished(),
            ),
            SharedActions::copyPublicLink(
                fn (Invitation $record): string => app(InvitationPublicLinkService::class)->show($record),
            )->visible(fn (Invitation $record) => $record->isPublished()),
            DeleteAction::make(),
        ];
    }
}
