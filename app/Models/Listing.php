<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'vendor_id', 'type', 'name', 'description', 'price', 'currency',
    'sale_price', 'sale_ends_at',
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
            'sale_price' => 'decimal:2',
            'sale_ends_at' => 'datetime',
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

    public function isOnFlashSale(): bool
    {
        return $this->sale_price !== null
            && $this->sale_ends_at !== null
            && $this->sale_ends_at->isFuture();
    }

    public function effectivePrice(): float
    {
        return $this->isOnFlashSale() ? (float) $this->sale_price : (float) $this->price;
    }

    public function scopeOnFlashSale(Builder $query): Builder
    {
        return $query->whereNotNull('sale_price')
            ->whereNotNull('sale_ends_at')
            ->where('sale_ends_at', '>', now());
    }

    /**
     * Shared by the PWA home page and the public API so both surfaces list
     * the exact same cross-vendor "plats du jour" without duplicating the
     * active-vendor/active-listing conditions.
     */
    public static function activeDishesOfTheDay(int $limit = 8)
    {
        return static::where('type', 'plat_du_jour')
            ->where('is_active', true)
            ->whereHas('vendor', fn ($query) => $query->where('is_active', true))
            ->with('vendor')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Shared by the PWA home page and the public API for the same reason as
     * activeDishesOfTheDay().
     */
    public static function activeFlashSales(int $limit = 8)
    {
        return static::onFlashSale()
            ->where('is_active', true)
            ->whereHas('vendor', fn ($query) => $query->where('is_active', true))
            ->with('vendor')
            ->orderBy('sale_ends_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Shared by the PWA home search box and the public API so both surfaces
     * search listings the exact same way.
     */
    public static function searchActive(string $query, int $limit = 10)
    {
        return static::where('is_active', true)
            ->where('name', 'like', '%'.$query.'%')
            ->whereHas('vendor', fn ($q) => $q->where('is_active', true))
            ->with('vendor')
            ->limit($limit)
            ->get();
    }
}
