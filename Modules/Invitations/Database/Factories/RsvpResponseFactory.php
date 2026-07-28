<?php

namespace Modules\Invitations\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Invitations\Enums\RsvpStatus;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Models\RsvpResponse;

/**
 * @extends Factory<RsvpResponse>
 */
class RsvpResponseFactory extends Factory
{
    protected $model = RsvpResponse::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'status' => $this->faker->randomElement(RsvpStatus::cases()),
            'name' => $this->faker->name(),
            'phone' => $this->faker->numerify('216#######'),
            'guests_count' => $this->faker->numberBetween(0, 4),
            'comment' => null,
        ];
    }
}
