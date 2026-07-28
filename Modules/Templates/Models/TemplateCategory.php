<?php

namespace Modules\Templates\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Templates\Database\Factories\TemplateCategoryFactory;

class TemplateCategory extends Model
{
    /** @use HasFactory<TemplateCategoryFactory> */
    use HasFactory;

    protected static function newFactory(): TemplateCategoryFactory
    {
        return TemplateCategoryFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'order',
    ];

    /**
     * @return HasMany<Template>
     */
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }
}
