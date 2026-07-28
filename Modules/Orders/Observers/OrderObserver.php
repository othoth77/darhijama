<?php

namespace Modules\Orders\Observers;

use InvalidArgumentException;
use Modules\Orders\Models\Order;

/**
 * Choix "Observer dédié" plutôt qu'un mutateur/`calculateTotal()` : la validation
 * porte sur plusieurs champs à la fois (subtotal/discount/total/currency), un
 * mutateur par attribut ne peut pas voir l'état complet du modèle au moment de
 * l'écriture. Justification complète : PHASE_1.md §5.
 */
class OrderObserver
{
    public function saving(Order $order): void
    {
        if (blank($order->currency)) {
            $order->currency = 'TND';
        }

        $subtotal = (string) ($order->subtotal ?? '0');
        $discount = (string) ($order->discount ?? '0');

        if (bccomp($discount, $subtotal, 3) === 1) {
            throw new InvalidArgumentException(
                'La remise ne peut pas dépasser le sous-total de la commande.'
            );
        }

        $expectedTotal = bcsub($subtotal, $discount, 3);

        if (blank($order->total)) {
            $order->total = $expectedTotal;

            return;
        }

        if (bccomp((string) $order->total, $expectedTotal, 3) !== 0) {
            throw new InvalidArgumentException(
                "Le total de la commande ({$order->total}) ne correspond pas à subtotal - discount ({$expectedTotal})."
            );
        }
    }
}
