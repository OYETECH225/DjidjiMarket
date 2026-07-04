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

        // Allow retrying registration (new password + fresh OTP) while the
        // phone hasn't been verified yet, instead of hard-failing on the
        // unique constraint.
        $user = $existing ?? new User();
        $user->fill([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'role' => $data['role'],
        ]);
        $user->password = Hash::make($data['password']);
        $user->save();

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

        // No SMS/WhatsApp aggregator wired up yet (Phase 2 per the spec) —
        // log the code so it can be used in local/dev testing.
        Log::info("OTP for {$phone}: {$code}");
    }
}
