<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Models\Listing;

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
}
