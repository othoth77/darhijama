<?php

namespace Modules\Orders\Tests\Unit;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Orders\Models\Client;
use Tests\TestCase;

class ClientPhoneNormalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_phone_with_leading_zero_is_normalized(): void
    {
        $client = Client::create([
            'name' => 'Test',
            'whatsapp_phone' => '098 123 456',
        ]);

        $this->assertSame('21698123456', $client->whatsapp_phone_normalized);
    }

    public function test_local_eight_digit_number_is_prefixed(): void
    {
        $client = Client::create([
            'name' => 'Test',
            'whatsapp_phone' => '98123456',
        ]);

        $this->assertSame('21698123456', $client->whatsapp_phone_normalized);
    }

    public function test_duplicate_normalized_phone_is_rejected_at_database_level(): void
    {
        Client::create(['name' => 'A', 'whatsapp_phone' => '98123456']);

        $this->expectException(QueryException::class);

        Client::create(['name' => 'B', 'whatsapp_phone' => '216 98 123 456']);
    }
}
