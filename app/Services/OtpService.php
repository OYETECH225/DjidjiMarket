<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Support\Facades\Log;

class OtpService
{
    public function send(string $phone): void
    {
        $code = OtpCode::generateFor($phone);

        // No SMS/WhatsApp aggregator wired up yet (Phase 2 per the spec).
        // Only log the plaintext code in local/testing — never in an
        // environment whose logs could reach staging/production log
        // aggregation, to avoid shipping OTPs in plaintext.
        if (app()->environment(['local', 'testing'])) {
            Log::info("OTP for {$phone}: {$code}");
        } else {
            Log::warning('OTP requested but no SMS/WhatsApp provider is configured; code was not delivered.');
        }
    }

    public function verify(string $phone, string $code): bool
    {
        return OtpCode::attempt($phone, $code);
    }
}
