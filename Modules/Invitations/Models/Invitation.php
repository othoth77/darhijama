<?php

namespace Modules\Invitations\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Invitations\Database\Factories\InvitationFactory;
use Modules\Invitations\Enums\InvitationStatus;
use Modules\Media\Models\Media;
use Modules\Orders\Models\Order;
use Modules\Templates\Models\Template;

class Invitation extends Model
{
    /** @use HasFactory<InvitationFactory> */
    use HasFactory;

    protected static function newFactory(): InvitationFactory
    {
        return InvitationFactory::new();
    }

    protected $fillable = [
        'order_id',
        'template_id',
        'public_token',
        'slug',
        'title',
        'event_type',
        'locale',
        'timezone',
        'groom_name',
        'bride_name',
        'wedding_date',
        'venue_name',
        'venue_address',
        'maps_embed_url',
        'external_video_url',
        'external_audio_url',
        'facebook_url',
        'instagram_url',
        'lat',
        'lng',
        'message',
        'qr_code_path',
        'status',
        'published_at',
        // Phase 3 — page publique (additif, voir migration 2026_07_20_100000).
        'dress_code',
        'additional_info',
        'contact_name',
        'contact_phone',
    ];

    protected function casts(): array
    {
        return [
            'wedding_date' => 'datetime',
            'published_at' => 'datetime',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
            'status' => InvitationStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * @return MorphMany<Media>
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function isPublished(): bool
    {
        return $this->status === InvitationStatus::Publie && $this->published_at !== null;
    }

    /**
     * @return HasMany<ProgramStep>
     */
    public function programSteps(): HasMany
    {
        return $this->hasMany(ProgramStep::class)->orderBy('order');
    }

    /**
     * @return HasMany<RsvpResponse>
     */
    public function rsvpResponses(): HasMany
    {
        return $this->hasMany(RsvpResponse::class);
    }
}
