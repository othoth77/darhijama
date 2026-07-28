<?php

namespace Modules\Orders\Services;

use App\Events\OrderCreated;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Client;
use Modules\Orders\Models\Order;
use RuntimeException;

/**
 * Aucune couche Repository (v2 pt. 8) : Eloquent utilisé directement.
 * DB::transaction() car cette opération regroupe une déduplication client
 * + une génération de référence + une création de commande — plusieurs
 * écritures logiquement indissociables (PHASE_1.md §7).
 *
 * Note MySQL : contrairement à PostgreSQL, une violation de contrainte unique
 * (code 23000) au sein d'une transaction InnoDB n'invalide pas la transaction
 * entière — la ré-tentative dans la même transaction est donc sûre ici.
 */
class CreateOrderService
{
    /**
     * @param  array{
     *     client: array{id?: int, name?: string, whatsapp_phone?: string, notes?: ?string},
     *     template_id?: ?int,
     *     subtotal?: string|float,
     *     discount?: string|float,
     *     payment_method?: ?string,
     *     status?: OrderStatus,
     *     notes?: ?string,
     *     created_by?: ?int,
     * }  $data
     */
    public function execute(array $data): Order
    {
        $order = DB::transaction(function () use ($data) {
            $client = $this->resolveClient($data['client']);

            $subtotal = (string) ($data['subtotal'] ?? config('whatsapp.offer.price', 49));
            $discount = (string) ($data['discount'] ?? 0);

            for ($attempt = 0; $attempt < 10; $attempt++) {
                $reference = $this->candidateReference($attempt);

                if (Order::where('reference', $reference)->exists()) {
                    continue;
                }

                try {
                    return Order::create([
                        'reference' => $reference,
                        'client_id' => $client->id,
                        'template_id' => $data['template_id'] ?? null,
                        'subtotal' => $subtotal,
                        'discount' => $discount,
                        'currency' => 'TND',
                        'payment_method' => $data['payment_method'] ?? null,
                        'status' => $data['status'] ?? OrderStatus::Nouveau,
                        'notes' => $data['notes'] ?? null,
                        'created_by' => $data['created_by'] ?? null,
                    ]);
                } catch (QueryException $exception) {
                    if (! $this->isUniqueViolation($exception)) {
                        throw $exception;
                    }

                    // Collision détectée par l'index unique malgré la pré-vérification
                    // (fenêtre de concurrence) : on régénère un nouveau candidat.
                    continue;
                }
            }

            throw new RuntimeException(
                'Impossible de générer une référence de commande unique après plusieurs tentatives.'
            );
        });

        OrderCreated::dispatch($order->id);

        return $order;
    }

    protected function resolveClient(array $clientData): Client
    {
        if (isset($clientData['id'])) {
            return Client::findOrFail($clientData['id']);
        }

        $normalized = Client::normalizePhone($clientData['whatsapp_phone']);

        return Client::firstOrCreate(
            ['whatsapp_phone_normalized' => $normalized],
            [
                'name' => $clientData['name'],
                'whatsapp_phone' => $clientData['whatsapp_phone'],
                'notes' => $clientData['notes'] ?? null,
            ]
        );
    }

    protected function candidateReference(int $attempt): string
    {
        $year = now()->year;
        $sequence = Order::whereYear('created_at', $year)->count() + 1 + $attempt;

        return sprintf('NJ-%d-%04d', $year, $sequence);
    }

    protected function isUniqueViolation(QueryException $exception): bool
    {
        return $exception->getCode() === '23000';
    }
}
