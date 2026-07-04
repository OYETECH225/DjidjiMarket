<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = $this->auth->registerOrResendOtp($data['name'], $data['phone'], $data['password'], $data['role']);

        return response()->json([
            'message' => $user->wasRecentlyCreated
                ? 'Compte créé. Un code de vérification a été envoyé.'
                : 'Ce numéro a déjà une inscription en attente. Un nouveau code de vérification a été envoyé.',
            'user' => new UserResource($user),
        ], $user->wasRecentlyCreated ? 201 : 200);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $data = $request->validated();

        $user = $this->auth->verifyOtpAndActivate($data['phone'], $data['code']);

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
}
