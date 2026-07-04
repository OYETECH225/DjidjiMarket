<?php

namespace App\Livewire;

use App\Models\Listing;
use App\Models\Vendor;
use App\Services\CartService;
use Livewire\Component;

class VendorStorefront extends Component
{
    public Vendor $vendor;

    public ?string $addedMessage = null;

    public function mount(string $slug): void
    {
        $this->vendor = Vendor::where('slug', $slug)->where('is_active', true)->firstOrFail();
    }

    public function addToCart(int $listingId, CartService $cart): void
    {
        $listing = Listing::where('id', $listingId)
            ->where('vendor_id', $this->vendor->id)
            ->where('is_active', true)
            ->firstOrFail();

        $cart->add($listing);

        $this->addedMessage = "\"{$listing->name}\" ajouté au panier.";
    }

    public function render()
    {
        return view('livewire.vendor-storefront', [
            'listings' => $this->vendor->listings()->where('is_active', true)->get(),
        ])->layout('layouts.app', ['title' => $this->vendor->business_name.' — DjidjiMarket']);
    }
}
