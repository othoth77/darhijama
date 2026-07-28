<?php

namespace Modules\Orders\Filament\Resources\OrderResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Filament\Resources\OrderResource;
use Modules\Orders\Services\CreateOrderService;

/**
 * Passe par CreateOrderService (transaction + référence race-safe) plutôt que
 * par la création Eloquent par défaut de Filament — voir PHASE_1.md §7.
 */
class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateOrderService::class)->execute([
            'client' => ['id' => $data['client_id']],
            'template_id' => $data['template_id'] ?? null,
            'subtotal' => $data['subtotal'] ?? null,
            'discount' => $data['discount'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'status' => isset($data['status']) ? OrderStatus::from($data['status']) : null,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
    }
}
