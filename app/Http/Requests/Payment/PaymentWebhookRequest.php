<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expected = config('services.payment_aggregator.webhook_secret');
        $provided = $this->header('X-Webhook-Secret', '');

        return $expected !== null && hash_equals($expected, $provided);
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'provider' => [
                'required', 'string',
                Rule::in(['orange_money', 'mtn_money', 'moov_money', 'wave']),
            ],
            'provider_transaction_id' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['confirme', 'echoue'])],
        ];
    }
}
