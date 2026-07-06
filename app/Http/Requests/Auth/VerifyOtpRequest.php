<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isNewUser = ! User::where('phone', $this->input('phone'))->exists();

        return [
            'phone' => ['required', 'string', 'max:30'],
            'code' => ['required', 'string', 'size:6'],
            // Only required to create a brand-new account — ignored (and so
            // not required) when verifying an existing phone, since that's
            // just a login and must not let the caller edit the account.
            'name' => [$isNewUser ? 'required' : 'nullable', 'string', 'max:255'],
            // Public self-registration may only self-assign client/vendor/
            // courier — admin and partner_manager are staff roles granted
            // internally, never through this endpoint.
            'role' => [$isNewUser ? 'required' : 'nullable', 'string', Rule::in(['client', 'vendor', 'courier'])],
        ];
    }
}
