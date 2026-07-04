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
