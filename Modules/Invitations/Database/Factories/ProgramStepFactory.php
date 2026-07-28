<?php

namespace Modules\Invitations\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Invitations\Models\Invitation;
use Modules\Invitations\Models\ProgramStep;

/**
 * @extends Factory<ProgramStep>
 */
class ProgramStepFactory extends Factory
{
    protected $model = ProgramStep::class;

    public function definition(): array
    {
        return [
            'invitation_id' => Invitation::factory(),
            'time' => $this->faker->time('H:i'),
            'title' => $this->faker->randomElement(['Accueil', 'Cérémonie', 'Cocktail', 'Dîner', 'Soirée']),
            'description' => null,
            'order' => 0,
        ];
    }
}
