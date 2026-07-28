<?php

namespace Modules\Invitations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Invitations\Services\CreateInvitationService;
use Modules\Orders\Models\Order;
use Modules\Templates\Models\Template;
use Tests\TestCase;

class CreateInvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function baseData(): array
    {
        return [
            'groom_name' => 'Karim',
            'bride_name' => 'Leila',
            'wedding_date' => now()->addMonth(),
        ];
    }

    public function test_it_generates_a_26_character_public_token(): void
    {
        $order = Order::factory()->create();

        $invitation = app(CreateInvitationService::class)->execute($order, $this->baseData());

        $this->assertSame(26, strlen($invitation->public_token));
    }

    public function test_it_copies_the_order_template_id_when_not_provided(): void
    {
        $template = Template::factory()->create();
        $order = Order::factory()->create(['template_id' => $template->id]);

        $invitation = app(CreateInvitationService::class)->execute($order, $this->baseData());

        $this->assertSame($template->id, $invitation->template_id);
    }

    public function test_a_template_id_can_be_overridden_explicitly(): void
    {
        $orderTemplate = Template::factory()->create();
        $overrideTemplate = Template::factory()->create();
        $order = Order::factory()->create(['template_id' => $orderTemplate->id]);

        $invitation = app(CreateInvitationService::class)->execute(
            $order,
            ['template_id' => $overrideTemplate->id] + $this->baseData()
        );

        $this->assertSame($overrideTemplate->id, $invitation->template_id);
    }

    public function test_an_order_can_have_multiple_invitations(): void
    {
        $order = Order::factory()->create();
        $service = app(CreateInvitationService::class);

        $service->execute($order, $this->baseData());
        $service->execute($order, $this->baseData());

        $this->assertSame(2, $order->invitations()->count());
    }

    public function test_public_tokens_are_unique_across_invitations(): void
    {
        $order = Order::factory()->create();
        $service = app(CreateInvitationService::class);

        $tokens = [];

        for ($i = 0; $i < 5; $i++) {
            $invitation = $service->execute($order, $this->baseData());
            $tokens[] = $invitation->public_token;
        }

        $this->assertSame($tokens, array_unique($tokens));
    }
}
