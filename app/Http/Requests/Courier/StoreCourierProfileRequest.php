<?php

namespace App\Http\Requests\Courier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourierProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_type' => ['required', 'string', Rule::in(['moto', 'tricycle', 'velo', 'pied'])],
            'cni_document_url' => ['nullable', 'string', 'max:255'],
            'vehicle_registration_url' => ['nullable', 'string', 'max:255'],
        ];
    }
}
