<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'vehicle_type', 'cni_document_url', 'vehicle_registration_url',
    'verification_status', 'current_latitude', 'current_longitude',
    'is_available', 'rating_average',
])]
class Courier extends Model
{
    public const VEHICLE_TYPE_LABELS = [
        'moto' => 'Moto',
        'tricycle' => 'Tricycle',
        'velo' => 'Vélo',
        'pied' => 'À pied',
    ];

    public const VERIFICATION_STATUS_LABELS = [
        'en_attente' => 'En attente',
        'verifie' => 'Vérifié',
        'rejete' => 'Rejeté',
    ];

    protected function casts(): array
    {
        return [
            'current_latitude' => 'decimal:7',
            'current_longitude' => 'decimal:7',
            'is_available' => 'boolean',
            'rating_average' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
