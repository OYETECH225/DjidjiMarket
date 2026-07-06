<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;

class ProfileController extends Controller
{
    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $user->update($request->validated());

        return new UserResource($user->refresh());
    }
}
