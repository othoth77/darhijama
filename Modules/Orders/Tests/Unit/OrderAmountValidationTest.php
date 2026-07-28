<?php

namespace Modules\Orders\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Modules\Orders\Models\Client;
use Modules\Orders\Models\Order;
use Tests\TestCase;

class OrderAmountValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_is_computed_when_not_provided(): void
    {
        $client = Client::factory()->create();

        $order = Order::create([
            'reference' => 'NJ-TEST-0001',
            'client_id' => $client->id,
            'subtotal' => '49.000',
            'discount' => '10.000',
        ]);

        $this->assertSame('39.000', $order->total);
    }

    public function test_mismatched_total_is_rejected(): void
    {
        $client = Client::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        Order::create([
            'reference' => 'NJ-TEST-0002',
            'client_id' => $client->id,
            'subtotal' => '49.000',
            'discount' => '10.000',
            'total' => '50.000',
        ]);
    }

    public function test_discount_greater_than_subtotal_is_rejected(): void
    {
        $client = Client::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        Order::create([
            'reference' => 'NJ-TEST-0003',
            'client_id' => $client->id,
            'subtotal' => '49.000',
            'discount' => '60.000',
        ]);
    }

    public function test_currency_defaults_to_tnd(): void
    {
        $client = Client::factory()->create();

        $order = Order::create([
            'reference' => 'NJ-TEST-0004',
            'client_id' => $client->id,
            'subtotal' => '49.000',
            'discount' => '0.000',
        ]);

        $this->assertSame('TND', $order->currency);
    }
}
