<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorProfileRequest;
use App\Http\Resources\ListingResource;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::where('is_active', true)->latest()->paginate(20);

        return VendorResource::collection($vendors);
    }

    public function storeProfile(StoreVendorProfileRequest $request)
    {
        $user = $request->user();

        if ($user->vendor()->exists()) {
            throw ValidationException::withMessages([
                'business_name' => ['Un profil vendeur existe déjà pour ce compte.'],
            ]);
        }

        $vendor = $user->vendor()->create([
            ...$request->validated(),
            'verification_level' => 'non_verifie',
        ]);

        return response()->json(['vendor' => new VendorResource($vendor)], 201);
    }

    public function show(string $slug)
    {
        $vendor = Vendor::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return new VendorResource($vendor);
    }

    public function listings(Request $request, Vendor $vendor)
    {
        abort_unless($vendor->is_active, 404);

        $listings = $vendor->listings()->where('is_active', true)->paginate(20);

        return ListingResource::collection($listings);
    }
}
