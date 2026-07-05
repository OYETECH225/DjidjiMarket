<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Http\Resources\VendorResource;
use App\Models\Listing;
use App\Models\Vendor;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function dishesOfTheDay()
    {
        return ListingResource::collection(Listing::activeDishesOfTheDay());
    }

    public function flashSales()
    {
        return ListingResource::collection(Listing::activeFlashSales());
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return response()->json(['vendors' => [], 'listings' => []]);
        }

        return response()->json([
            'vendors' => VendorResource::collection(Vendor::searchActive($query)),
            'listings' => ListingResource::collection(Listing::searchActive($query)),
        ]);
    }
}
