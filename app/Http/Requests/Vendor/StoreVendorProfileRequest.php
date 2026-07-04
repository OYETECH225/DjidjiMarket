<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVendorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'vendor_type' => ['required', 'string', Rule::in(['boutique', 'street_food', 'restaurant'])],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:vendors,slug'],
            'description' => ['nullable', 'string'],
            'address_text' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
