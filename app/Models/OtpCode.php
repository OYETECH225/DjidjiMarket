<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

#[Fillable(['phone', 'code', 'expires_at', 'consumed_at'])]
class OtpCode extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    /**
     * Create a new OTP for the phone and return the plaintext code to send.
     * Only the hash is persisted.
     */
    public static function generateFor(string $phone): string
    {
        $plainCode = (string) random_int(100000, 999999);

        static::create([
            'phone' => $phone,
            'code' => Hash::make($plainCode),
            'expires_at' => now()->addMinutes(10),
        ]);

        return $plainCode;
    }

    public static function attempt(string $phone, string $code): bool
    {
        $otp = static::query()
            ->where('phone', $phone)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otp || ! Hash::check($code, $otp->code)) {
            return false;
        }

        $otp->update(['consumed_at' => now()]);

        return true;
    }
}
