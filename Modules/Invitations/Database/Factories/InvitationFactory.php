<?php

namespace Modules\Invitations\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Invitations\Models\Invitation;
use Modules\Orders\Models\Order;

/**
 * @extends Factory<Invitation>
 */
class InvitationFactory extends Factory
{
    protected $model = Invitation::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'template_id' => null,
            // Identique a la logique reelle de CreateInvitationService (Str::ulid()->toBase32()).
            'public_token' => (string) Str::ulid()->toBase32(),
            'slug' => null,
            'title' => null,
            'event_type' => 'mariage',
            'locale' => 'fr',
            'timezone' => 'Africa/Tunis',
            'groom_name' => $this->faker->firstName('male'),
            'bride_name' => $this->faker->firstName('female'),
            'wedding_date' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
            'venue_name' => null,
            'venue_address' => null,
            'maps_embed_url' => null,
            'lat' => null,
            'lng' => null,
            'message' => null,
            'qr_code_path' => null,
            'status' => InvitationStatus::Brouillon,
            'published_at' => null,
        ];
    }
}
