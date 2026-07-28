<?php

namespace Modules\Orders\Observers;

use Modules\Orders\Models\Client;

/**
 * Classe dédiée (plutôt qu'une closure dans Client::booted()) pour rester
 * testable isolément et documentée — voir ARCHITECTURE.md (règle sur les hooks
 * de cycle de vie Eloquent) et PHASE_1.md §4.
 */
class ClientObserver
{
    public function saving(Client $client): void
    {
        if (filled($client->whatsapp_phone)) {
            $client->whatsapp_phone_normalized = Client::normalizePhone($client->whatsapp_phone);
        }
    }
}
