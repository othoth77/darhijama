<?php

namespace Mythos\Core\Media\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Mythos\Core\Media\Models\Media;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        return [
            // Alias du morph map (Modules\Media\Providers\MediaServiceProvider), jamais le FQCN
            // brut — voir PHASE_1.md §1.
            'mediable_type' => 'test-subject',
            'mediable_id' => 1,
            'disk' => 'public',
            'path' => 'templates/'.$this->faker->uuid().'.jpg',
            'type' => 'image',
            'original_name' => $this->faker->word().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => $this->faker->numberBetween(10_000, 500_000),
            'order' => 0,
        ];
    }
}
