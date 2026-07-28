<?php

namespace Modules\Templates\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Templates\Models\TemplateCategory;

/**
 * @extends Factory<TemplateCategory>
 */
class TemplateCategoryFactory extends Factory
{
    protected $model = TemplateCategory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'order' => 0,
        ];
    }
}
