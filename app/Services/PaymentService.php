<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function initiate(Order $order, string $provider): Payment
    {
        if ($order->status !== 'en_attente_paiement') {
            throw ValidationException::withMessages([
                'order_id' => ['Cette commande a déjà un paiement en cours ou confirmé.'],
            ]);
        }

        $amount = $order->total_amount + $order->delivery_fee;

        return DB::transaction(function () use ($order, $provider, $amount) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => $provider,
                'amount' => $amount,
                // Cash is collected physically at delivery, so there is no
                // aggregator confirmation step or escrow for it in Phase 1 —
                // the order moves straight to "confirmee".
                'status' => $provider === 'cash_on_delivery' ? 'confirme' : 'initie',
            ]);

            if ($provider === 'cash_on_delivery') {
                $order->update(['status' => 'confirmee']);
            }

            return $payment;
        });
    }

    public function confirmReceipt(Order $order): void
    {
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
    }
}
