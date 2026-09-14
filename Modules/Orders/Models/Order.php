<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Invitations\Models\Invitation;
use Modules\Orders\Database\Factories\OrderFactory;
use Modules\Orders\Enums\OrderStatus;
use Modules\Templates\Models\Template;
use Mythos\Core\Identity\Models\User;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    protected $fillable = [
        'reference',
        'client_id',
        'template_id',
        'subtotal',
        'discount',
        'total',
        'paid_amount',
        'currency',
        'payment_method',
        'paid_at',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:3',
            'discount' => 'decimal:3',
            'total' => 'decimal:3',
            'paid_amount' => 'decimal:3',
            'paid_at' => 'datetime',
            'status' => OrderStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<Invitation>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }
}
