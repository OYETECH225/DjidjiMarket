<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $existing = User::where('phone', $data['phone'])->first();

        if ($existing && $existing->phone_verified_at !== null) {
            throw ValidationException::withMessages([
                'phone' => ['Ce numéro est déjà enregistré et vérifié.'],
            ]);
        }

        if ($existing) {
            // Someone else could be retrying with this phone number without
            // actually owning it. Only resend the OTP — never overwrite
            // name/password/role of a pending registration from an
            // unauthenticated request, or an attacker could hijack it before
            // the real owner verifies.
            $this->sendOtp($existing->phone);

            return response()->json([
                'message' => 'Ce numéro a déjà une inscription en attente. Un nouveau code de vérification a été envoyé.',
                'user' => new UserResource($existing),
            ], 200);
        }

        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        $this->sendOtp($user->phone);

        return response()->json([
            'message' => 'Compte créé. Un code de vérification a été envoyé.',
            'user' => new UserResource($user),
        ], 201);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $data = $request->validated();

        if (! OtpCode::attempt($data['phone'], $data['code'])) {
            throw ValidationException::withMessages([
                'code' => ['Code invalide ou expiré.'],
            ]);
        }

        $user = User::where('phone', $data['phone'])->firstOrFail();
        $user->forceFill(['phone_verified_at' => now()])->save();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('phone', $data['phone'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Identifiants invalides.'],
            ]);
        }

        if ($user->phone_verified_at === null) {
            throw ValidationException::withMessages([
                'phone' => ['Numéro non vérifié. Vérifiez votre code OTP avant de vous connecter.'],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    private function sendOtp(string $phone): void
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
}
