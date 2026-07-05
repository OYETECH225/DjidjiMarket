<?php

namespace App\Http\Requests\VendorListing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(['produit', 'plat_du_jour', 'menu_item'])],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => [
                'nullable', 'numeric', 'min:0', 'required_with:sale_ends_at',
                function ($attribute, $value, $fail) {
                    if ($value !== null && (float) $value >= (float) $this->input('price')) {
                        $fail('Le prix promo doit être inférieur au prix normal.');
                    }
                },
            ],
            'sale_ends_at' => ['nullable', 'date', 'after:now', 'required_with:sale_price'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'display_number' => ['nullable', 'integer', 'min:0'],
            'promo_code' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
