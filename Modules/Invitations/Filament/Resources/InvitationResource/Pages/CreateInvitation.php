<?php

namespace Modules\Invitations\Filament\Resources\InvitationResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Invitations\Filament\Resources\InvitationResource;
use Modules\Invitations\Services\CreateInvitationService;
use Modules\Orders\Models\Order;

/**
 * Passe par CreateInvitationService (token ULID race-safe + copy-on-create du
 * modèle) plutôt que par la création Eloquent par défaut — voir PHASE_1.md §7.
 */
class CreateInvitation extends CreateRecord
{
    protected static string $resource = InvitationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $order = Order::findOrFail($data['order_id']);

        unset($data['order_id']);

        return app(CreateInvitationService::class)->execute($order, $data);
    }
}
