<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'client';
    }

    public function rules(): array
    {
        return [
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.listing_id' => ['required', 'integer', 'exists:listings,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'delivery_address_text' => ['required', 'string', 'max:255'],
            'delivery_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'delivery_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            // tiktok_live / lien_vendeur sources go with the Phase 2 quick-order flow.
            'source' => ['nullable', 'string', Rule::in(['app', 'web'])],
        ];
    }
}
