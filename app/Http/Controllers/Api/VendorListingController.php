<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorListing\StoreListingRequest;
use App\Http\Requests\VendorListing\UpdateListingRequest;
use App\Http\Resources\ListingResource;
use App\Models\Listing;
use Illuminate\Http\Request;

class VendorListingController extends Controller
{
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor()->first();

        abort_unless($vendor, 404, 'Profil vendeur introuvable.');

        return ListingResource::collection($vendor->listings()->latest()->get());
    }

    public function store(StoreListingRequest $request)
    {
        $vendor = $request->user()->vendor()->first();

        abort_unless($vendor, 404, 'Profil vendeur introuvable.');

        $listing = $vendor->listings()->create([
            ...$request->validated(),
            'currency' => 'XOF',
        ]);

        return response()->json(['listing' => new ListingResource($listing)], 201);
    }

    public function update(UpdateListingRequest $request, Listing $listing)
    {
        $this->authorizeOwnership($request, $listing);

        $listing->update($request->validated());

        return new ListingResource($listing->refresh());
    }

    public function destroy(Request $request, Listing $listing)
    {
        $this->authorizeOwnership($request, $listing);

        $listing->delete();

        return response()->json(['message' => 'Article supprimé.']);
    }

    private function authorizeOwnership(Request $request, Listing $listing): void
    {
        $vendor = $request->user()->vendor()->first();

        abort_unless($vendor && $listing->vendor_id === $vendor->id, 403, 'Cet article ne vous appartient pas.');
    }
}
