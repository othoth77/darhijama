<?php

namespace Modules\Orders\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Orders\Models\Client;
use Modules\Orders\Services\CreateOrderService;
use Tests\TestCase;

class CreateOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_order_with_a_unique_reference(): void
    {
        $order = app(CreateOrderService::class)->execute([
            'client' => ['name' => 'Leila', 'whatsapp_phone' => '98123456'],
        ]);

        $this->assertNotEmpty($order->reference);
        $this->assertStringStartsWith('NJ-'.now()->year.'-', $order->reference);
        $this->assertSame('49.000', $order->total);
    }

    public function test_it_reuses_an_existing_client_by_normalized_phone(): void
    {
        $service = app(CreateOrderService::class);

        $order1 = $service->execute([
            'client' => ['name' => 'Leila', 'whatsapp_phone' => '98123456'],
        ]);

        $order2 = $service->execute([
            'client' => ['name' => 'Leila Karim', 'whatsapp_phone' => '216 98 123 456'],
        ]);

        $this->assertSame($order1->client_id, $order2->client_id);
        $this->assertSame(1, Client::count());
    }

    public function test_references_are_unique_across_multiple_orders(): void
    {
        $service = app(CreateOrderService::class);

        $references = [];

        for ($i = 0; $i < 5; $i++) {
            $order = $service->execute([
                'client' => ['name' => "Client $i", 'whatsapp_phone' => '981234'.str_pad((string) $i, 2, '0', STR_PAD_LEFT)],
            ]);
            $references[] = $order->reference;
        }

        $this->assertSame($references, array_unique($references));
    }
}
