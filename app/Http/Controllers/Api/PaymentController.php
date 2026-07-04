<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\InitiatePaymentRequest;
use App\Http\Requests\Payment\PaymentWebhookRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function initiate(InitiatePaymentRequest $request)
    {
        $order = Order::findOrFail($request->validated('order_id'));

        abort_unless($order->client_id === $request->user()->id, 403);

        if ($order->status !== 'en_attente_paiement') {
            throw ValidationException::withMessages([
                'order_id' => ['Cette commande a déjà un paiement en cours ou confirmé.'],
            ]);
        }

        $provider = $request->validated('provider');
        $amount = $order->total_amount + $order->delivery_fee;

        $payment = DB::transaction(function () use ($order, $provider, $amount) {
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

        return response()->json(['payment' => new PaymentResource($payment)], 201);
    }

    public function webhook(PaymentWebhookRequest $request)
    {
        $data = $request->validated();
        $order = Order::findOrFail($data['order_id']);

        $payment = Payment::where('order_id', $order->id)
            ->where('provider', $data['provider'])
            ->where('status', 'initie')
            ->latest()
            ->first();

        // No matching pending payment: already processed or unknown — accept
        // the callback without error so the aggregator doesn't retry forever.
        if (! $payment) {
            return response()->json(['message' => 'Aucun paiement en attente correspondant.']);
        }

        DB::transaction(function () use ($payment, $order, $data) {
            if ($data['status'] === 'confirme') {
                $payment->update([
                    'status' => 'sequestre',
                    'provider_transaction_id' => $data['provider_transaction_id'],
                ]);
                $order->update(['status' => 'paiement_sequestre']);
            } else {
                $payment->update([
                    'status' => 'echoue',
                    'provider_transaction_id' => $data['provider_transaction_id'],
                ]);
            }
        });

        return response()->json(['message' => 'ok']);
    }
}
