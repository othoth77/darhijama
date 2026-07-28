<?php

namespace Modules\Orders\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Orders\Models\Client;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        // 8 chiffres locaux, prefixe automatiquement par ClientObserver.
        $local = $this->faker->unique()->numerify('########');

        return [
            'name' => $this->faker->name(),
            'whatsapp_phone' => $local,
            'notes' => null,
        ];
    }
}
