<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // Public registration may only self-assign client/vendor/courier —
            // admin and partner_manager are staff roles granted internally.
            'role' => ['required', 'string', 'in:client,vendor,courier'],
        ];
    }
}
