<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'client_id', 'vendor_id', 'courier_id', 'status',
    'delivery_latitude', 'delivery_longitude', 'delivery_address_text',
    'total_amount', 'delivery_fee', 'commission_amount', 'source', 'promo_code_used',
])]
class Order extends Model
{
    public const STATUS_LABELS = [
        'en_attente_paiement' => 'En attente de paiement',
        'paiement_sequestre' => 'Paiement séquestré',
        'confirmee' => 'Confirmée',
        'en_preparation' => 'En préparation',
        'cherche_livreur' => 'Recherche livreur',
        'livreur_assigne' => 'Livreur assigné',
        'recuperee' => 'Récupérée',
        'en_livraison' => 'En livraison',
        'livree' => 'Livrée',
        'paiement_libere' => 'Paiement libéré',
        'litige_ouvert' => 'Litige ouvert',
        'annulee' => 'Annulée',
    ];

    protected static function booted(): void
    {
        // The DB column defaults to 'en_attente_paiement', but that default
        // isn't reflected on the in-memory model right after insert — set it
        // explicitly so OrderObserver sees the real value when logging history.
        static::creating(function (Order $order) {
            $order->status ??= 'en_attente_paiement';
        });
    }

    protected function casts(): array
    {
        return [
            'delivery_latitude' => 'decimal:7',
            'delivery_longitude' => 'decimal:7',
            'total_amount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'commission_amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function courier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
