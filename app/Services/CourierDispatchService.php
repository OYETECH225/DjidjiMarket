<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourierDispatchService
{
    /**
     * Status a courier is allowed to move an order to, keyed by the
     * order's current status. Enforces the pickup → in-transit → delivered
     * sequence one step at a time.
     */
    public const ALLOWED_TRANSITIONS = [
        'livreur_assigne' => 'recuperee',
        'recuperee' => 'en_livraison',
        'en_livraison' => 'livree',
    ];

    /**
     * "First courier to accept wins" — a plain conditional UPDATE (not an
     * Eloquent save) so the DB row lock makes this atomic under concurrent
     * requests, without needing the Redis lock reserved for Phase 2's
     * automated dispatch.
     */
    public function accept(User $courier, Order $order): bool
    {
        return DB::transaction(function () use ($courier, $order) {
            $affected = Order::where('id', $order->id)
                ->where('status', 'cherche_livreur')
                ->whereNull('courier_id')
                ->update(['courier_id' => $courier->id, 'status' => 'livreur_assigne']);

            if ($affected === 1) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'livreur_assigne',
                    'changed_at' => now(),
                    'changed_by' => Auth::id() ?? $courier->id,
                ]);
            }

            return $affected === 1;
        });
    }

    public function updateStatus(User $courier, Order $order, string $requestedStatus): void
    {
        abort_unless($order->courier_id === $courier->id, 403);

        $expectedNext = self::ALLOWED_TRANSITIONS[$order->status] ?? null;

        if ($expectedNext === null || $requestedStatus !== $expectedNext) {
            throw ValidationException::withMessages([
                'status' => ["Transition invalide depuis \"{$order->status}\"."],
            ]);
        }

        $order->update(['status' => $requestedStatus]);
    }
}
