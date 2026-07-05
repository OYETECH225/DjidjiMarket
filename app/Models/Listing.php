<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'vendor_id', 'type', 'name', 'description', 'price', 'currency',
    'stock_quantity', 'available_from', 'available_until', 'photo_urls',
    'display_number', 'promo_code', 'is_active',
])]
class Listing extends Model
{
    public const TYPE_LABELS = [
        'produit' => 'Produit',
        'plat_du_jour' => 'Plat du jour',
        'menu_item' => 'Menu',
    ];

    protected static function booted(): void
    {
        // The DB column defaults to true, but that default isn't reflected
        // on the in-memory model right after insert — set it explicitly so
        // API responses right after creation don't show is_active as null.
        static::creating(function (Listing $listing) {
            $listing->is_active ??= true;
        });
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'photo_urls' => 'array',
            'available_from' => 'datetime',
            'available_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
