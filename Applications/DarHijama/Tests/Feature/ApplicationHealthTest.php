<?php

namespace Applications\DarHijama\Tests\Feature;

use Tests\TestCase;

class ApplicationHealthTest extends TestCase
{
    public function test_public_home_is_isolated_by_host(): void
    {
        $this->get('http://darhijama.tn/')
            ->assertOk()
            ->assertSee('DAR HIJAMA')
            ->assertDontSee('Notre Jour');

        $this->get('http://notrejour.tn/')
            ->assertOk()
            ->assertSee('Notre Jour');
    }

    public function test_application_health_route_is_available(): void
    {
        $this->get(route('mythos.dar-hijama.health'))
            ->assertOk()
            ->assertJson(['application' => 'dar-hijama', 'status' => 'ok']);
    }
}
