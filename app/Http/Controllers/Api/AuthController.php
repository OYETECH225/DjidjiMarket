<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function requestOtp(RequestOtpRequest $request)
    {
        $isNew = $this->auth->requestOtp($request->validated('phone'));

        return response()->json([
            'message' => 'Un code de vérification a été envoyé.',
            'is_new' => $isNew,
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $data = $request->validated();

        $user = $this->auth->verifyOtpAndAuthenticate($data['phone'], $data['code'], $data['name'] ?? null, $data['role'] ?? null);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }
}
