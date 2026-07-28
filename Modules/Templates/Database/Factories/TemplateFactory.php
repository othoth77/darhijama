<?php

namespace Modules\Templates\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Templates\Models\Template;
use Modules\Templates\Models\TemplateCategory;

/**
 * @extends Factory<Template>
 */
class TemplateFactory extends Factory
{
    protected $model = Template::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'template_category_id' => TemplateCategory::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'preview_image_path' => null,
            'demo_data' => null,
            'is_active' => true,
            'order' => 0,
        ];
    }
}
