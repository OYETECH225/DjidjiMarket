<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private readonly OtpService $otp) {}

    /**
     * Send an OTP to a phone, whether it belongs to an existing account or
     * not — registration and login are the same code path, there's no
     * password. Returns whether the phone is new, so the caller knows
     * whether to collect a name/role before calling verifyOtpAndAuthenticate().
     */
    public function requestOtp(string $phone): bool
    {
        $isNew = ! User::where('phone', $phone)->exists();

        $this->otp->send($phone);

        return $isNew;
    }

    /**
     * Verify the OTP and either log an existing user in or create a new one.
     * $name/$role are only used when creating a new account — they're
     * ignored for an existing phone, since logging in must never let the
     * caller change someone else's identity just by supplying different
     * values (the OTP proves phone ownership, not the right to edit).
     */
    public function verifyOtpAndAuthenticate(string $phone, string $code, ?string $name = null, ?string $role = null): User
    {
        if (! $this->otp->verify($phone, $code)) {
            throw ValidationException::withMessages([
                'code' => ['Code invalide ou expiré.'],
            ]);
        }

        $user = User::where('phone', $phone)->first();

        if ($user) {
            if ($user->phone_verified_at === null) {
                $user->forceFill(['phone_verified_at' => now()])->save();
            }

            return $user;
        }

        if (! $name || ! $role) {
            throw ValidationException::withMessages([
                'name' => ['Nom et rôle requis pour créer un compte.'],
            ]);
        }

        $user = User::create([
            'name' => $name,
            'phone' => $phone,
            'role' => $role,
        ]);

        // phone_verified_at is deliberately not mass-assignable (see the
        // Fillable attribute on User) so it can never be set via a stray
        // mass-assignment elsewhere — set it explicitly here instead.
        $user->forceFill(['phone_verified_at' => now()])->save();

        return $user;
    }
}
