<?php

namespace Modules\Orders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Orders\Enums\OrderStatus;
use Modules\Orders\Models\Client;
use Modules\Orders\Models\Order;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = '49.000';
        $discount = '0.000';

        return [
            // Format identique a CreateOrderService, unicite garantie par Faker
            // ->unique() pour les besoins des tests (hors logique de generation reelle).
            'reference' => 'NJ-'.now()->year.'-'.$this->faker->unique()->numerify('####'),
            'client_id' => Client::factory(),
            'template_id' => null,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => bcsub($subtotal, $discount, 3),
            'paid_amount' => '0.000',
            'currency' => 'TND',
            'payment_method' => null,
            'paid_at' => null,
            'status' => OrderStatus::Nouveau,
            'notes' => null,
            'created_by' => null,
        ];
    }
}
