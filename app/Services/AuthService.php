<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(private readonly OtpService $otp) {}

    /**
     * Register a new phone, or — if that phone already has a pending
     * (unverified) signup — just resend the OTP. Never overwrites an
     * existing row's name/password/role, since the caller hasn't proven
     * control of the phone yet: an attacker who only knows someone else's
     * number could otherwise hijack their in-progress registration.
     *
     * Check `$user->wasRecentlyCreated` to tell a fresh signup from a resend.
     */
    public function registerOrResendOtp(string $name, string $phone, string $password, string $role): User
    {
        $existing = User::where('phone', $phone)->first();

        if ($existing && $existing->phone_verified_at !== null) {
            throw ValidationException::withMessages([
                'phone' => ['Ce numéro est déjà enregistré et vérifié.'],
            ]);
        }

        if ($existing) {
            $this->otp->send($existing->phone);

            return $existing;
        }

        $user = User::create([
            'name' => $name,
            'phone' => $phone,
            'role' => $role,
            'password' => Hash::make($password),
        ]);

        $this->otp->send($user->phone);

        return $user;
    }

    public function verifyOtpAndActivate(string $phone, string $code): User
    {
        if (! $this->otp->verify($phone, $code)) {
            throw ValidationException::withMessages([
                'code' => ['Code invalide ou expiré.'],
            ]);
        }

        $user = User::where('phone', $phone)->firstOrFail();
        $user->forceFill(['phone_verified_at' => now()])->save();

        return $user;
    }
}
