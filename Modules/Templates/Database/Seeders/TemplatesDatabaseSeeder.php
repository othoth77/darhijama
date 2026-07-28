<?php

namespace Modules\Templates\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Templates\Models\Template;
use Modules\Templates\Models\TemplateCategory;

class TemplatesDatabaseSeeder extends Seeder
{
    /**
     * Jeu de données de démonstration minimal (catalogue de départ visible dans
     * le back-office dès l'installation locale). Idempotent (firstOrCreate).
     */
    public function run(): void
    {
        $categories = [
            'Classique' => 'Élégance intemporelle, typographie sobre.',
            'Moderne' => 'Lignes épurées, couleurs contemporaines.',
            'Floral' => 'Motifs floraux, ambiance romantique.',
        ];

        foreach ($categories as $name => $description) {
            $category = TemplateCategory::firstOrCreate(
                ['slug' => str($name)->slug()],
                ['name' => $name, 'order' => 0]
            );

            Template::firstOrCreate(
                ['slug' => str($name.'-demo')->slug()],
                [
                    'template_category_id' => $category->id,
                    'name' => $name.' — Démo',
                    'description' => $description,
                    'is_active' => true,
                    'order' => 0,
                ]
            );
        }
    }
}
