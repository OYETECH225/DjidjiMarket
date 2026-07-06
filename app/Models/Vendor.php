<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'business_name', 'vendor_type', 'slug', 'description',
    'logo_url', 'cover_url', 'address_text', 'latitude', 'longitude',
    'verification_level', 'rccm_number', 'dfe_number', 'rccm_document_url',
    'cni_document_url', 'rccm_assist_status', 'commission_rate', 'is_active',
])]
class Vendor extends Model
{
    public const VERIFICATION_LABELS = [
        'non_verifie' => 'Non vérifié',
        'identite_confirmee' => 'Identité confirmée',
        'verifie' => 'Vérifié',
    ];

    public const VENDOR_TYPE_LABELS = [
        'boutique' => 'Boutique',
        'street_food' => 'Nourriture',
        'restaurant' => 'Restaurant',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'commission_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Shared by the PWA home search box and the public API so both surfaces
     * search vendors the exact same way.
     */
    public static function searchActive(string $query, int $limit = 10)
    {
        // whereRaw + LOWER() rather than a plain `like` because Postgres'
        // LIKE is case-sensitive (unlike SQLite's, which is why this passed
        // in tests but silently returned nothing on the real dev database).
        return static::where('is_active', true)
            ->whereRaw('LOWER(business_name) LIKE ?', ['%'.mb_strtolower($query).'%'])
            ->limit($limit)
            ->get();
    }
}
