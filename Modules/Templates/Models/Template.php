<?php

namespace Modules\Templates\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Invitations\Models\Invitation;
use Modules\Orders\Models\Order;
use Modules\Templates\Database\Factories\TemplateFactory;
use Mythos\Core\Media\Models\Media;

/**
 * Columns of the `templates` table (2026_07_18_100002_create_templates_table).
 *
 * @property int $id
 * @property int $template_category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $preview_image_path
 * @property array<string, mixed>|null $demo_data
 * @property bool $is_active
 * @property int $order
 */
class Template extends Model
{
    /** @use HasFactory<TemplateFactory> */
    use HasFactory;

    protected static function newFactory(): TemplateFactory
    {
        return TemplateFactory::new();
    }

    protected $fillable = [
        'template_category_id',
        'name',
        'slug',
        'description',
        'preview_image_path',
        'demo_data',
        'is_active',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'demo_data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'template_category_id');
    }

    /**
     * @return HasMany<Order>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<Invitation>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    /**
     * @return MorphMany<Media>
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
