<?php

namespace App\Http\Requests\VendorListing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', 'string', Rule::in(['produit', 'plat_du_jour', 'menu_item'])],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'sale_price' => [
                'nullable', 'numeric', 'min:0', 'required_with:sale_ends_at',
                function ($attribute, $value, $fail) {
                    $currentPrice = $this->input('price', $this->route('listing')?->price);

                    if ($value !== null && $currentPrice !== null && (float) $value >= (float) $currentPrice) {
                        $fail('Le prix promo doit être inférieur au prix normal.');
                    }
                },
            ],
            'sale_ends_at' => ['nullable', 'date', 'after:now', 'required_with:sale_price'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'display_number' => ['nullable', 'integer', 'min:0'],
            'promo_code' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
