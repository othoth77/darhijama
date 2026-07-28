<?php

namespace Modules\Media\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Media\Models\Media;
use Modules\Templates\Models\Template;

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
            'mediable_type' => 'template',
            'mediable_id' => Template::factory(),
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
