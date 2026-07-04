<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'provider' => [
                'required', 'string',
                Rule::in(['orange_money', 'mtn_money', 'moov_money', 'wave', 'cash_on_delivery']),
            ],
        ];
    }
}
