<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Listing;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();
        $vendor = Vendor::findOrFail($data['vendor_id']);

        $order = DB::transaction(function () use ($data, $vendor, $request) {
            $listingIds = collect($data['items'])->pluck('listing_id');

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

            foreach ($data['items'] as $item) {
                $listing = $listings[$item['listing_id']];

                if ($listing->stock_quantity !== null && $listing->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ["Stock insuffisant pour l'article \"{$listing->name}\"."],
                    ]);
                }

                $totalAmount += $listing->price * $item['quantity'];
            }

            $order = Order::create([
                'client_id' => $request->user()->id,
                'vendor_id' => $vendor->id,
                'status' => 'en_attente_paiement',
                'delivery_latitude' => $data['delivery_latitude'] ?? null,
                'delivery_longitude' => $data['delivery_longitude'] ?? null,
                'delivery_address_text' => $data['delivery_address_text'],
                'total_amount' => $totalAmount,
                // Delivery-fee calculation depends on courier dispatch/geo logic
                // not built yet — flat 0 placeholder for Phase 1.
                'delivery_fee' => 0,
                'commission_amount' => round($totalAmount * $vendor->commission_rate / 100, 2),
                'source' => $data['source'] ?? 'app',
            ]);

            foreach ($data['items'] as $item) {
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

        return response()->json(['order' => new OrderResource($order->load('items'))], 201);
    }

    public function show(Request $request, Order $order)
    {
        $user = $request->user();

        abort_unless(
            $order->client_id === $user->id
                || $order->vendor->user_id === $user->id
                || $user->role === 'admin',
            403
        );

        return new OrderResource($order->load('items.listing'));
    }

    public function confirmReceipt(Request $request, Order $order)
    {
        abort_unless($order->client_id === $request->user()->id, 403);

        if ($order->status !== 'livree') {
            throw ValidationException::withMessages([
                'status' => ['La commande doit être marquée "livrée" avant confirmation de réception.'],
            ]);
        }

        $payment = Payment::where('order_id', $order->id)->where('status', 'sequestre')->latest()->first();

        if (! $payment) {
            throw ValidationException::withMessages([
                'status' => ['Aucun paiement séquestré trouvé pour cette commande.'],
            ]);
        }

        DB::transaction(function () use ($order, $payment) {
            $payment->update(['status' => 'libere', 'escrow_released_at' => now()]);
            $order->update(['status' => 'paiement_libere']);
        });

        return new OrderResource($order->refresh());
    }
}
