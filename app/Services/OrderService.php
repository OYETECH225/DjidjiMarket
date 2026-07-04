<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * @param  array<int, array{listing_id: int, quantity: int}>  $items
     */
    public function createOrder(
        User $client,
        Vendor $vendor,
        array $items,
        string $deliveryAddressText,
        ?float $deliveryLatitude = null,
        ?float $deliveryLongitude = null,
        string $source = 'app',
    ): Order {
        return DB::transaction(function () use ($client, $vendor, $items, $deliveryAddressText, $deliveryLatitude, $deliveryLongitude, $source) {
            $listingIds = collect($items)->pluck('listing_id');

            $listings = Listing::whereIn('id', $listingIds)
                ->where('vendor_id', $vendor->id)
                ->where('is_active', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($listings->count() !== $listingIds->unique()->count()) {
                throw ValidationException::withMessages([
                    'items' => ['Un ou plusieurs articles sont invalides ou n\'appartiennent pas à ce vendeur.'],
                ]);
            }

            $totalAmount = 0;

            foreach ($items as $item) {
                $listing = $listings[$item['listing_id']];

                if ($listing->stock_quantity !== null && $listing->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["Stock insuffisant pour l'article \"{$listing->name}\"."],
                    ]);
                }

                $totalAmount += $listing->price * $item['quantity'];
            }

            $order = Order::create([
                'client_id' => $client->id,
                'vendor_id' => $vendor->id,
                'status' => 'en_attente_paiement',
                'delivery_latitude' => $deliveryLatitude,
                'delivery_longitude' => $deliveryLongitude,
                'delivery_address_text' => $deliveryAddressText,
                'total_amount' => $totalAmount,
                // Delivery-fee calculation depends on courier dispatch/geo logic
                // not built yet — flat 0 placeholder for Phase 1.
                'delivery_fee' => 0,
                'commission_amount' => round($totalAmount * $vendor->commission_rate / 100, 2),
                'source' => $source,
            ]);

            foreach ($items as $item) {
                $listing = $listings[$item['listing_id']];

                $order->items()->create([
                    'listing_id' => $listing->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $listing->price,
                ]);

                if ($listing->stock_quantity !== null) {
                    $listing->decrement('stock_quantity', $item['quantity']);
                }
            }

            return $order;
        });
    }
}
