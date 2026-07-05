<?php

namespace App\Livewire;

use App\Models\Listing;
use App\Models\Vendor;
use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app', ['title' => 'DjidjiMarket — le vrai marché, en toute confiance', 'showBottomNav' => true])]
class Home extends Component
{
    use WithPagination;

    #[Url(as: 'type')]
    public ?string $type = null;

    public string $query = '';

    public ?string $addedMessage = null;

    public function filterBy(?string $type): void
    {
        $this->type = $type && array_key_exists($type, Vendor::VENDOR_TYPE_LABELS) ? $type : null;
        $this->resetPage();
    }

    public function updatedQuery(): void
    {
        $this->resetPage();
    }

    public function addDishToCart(int $listingId, CartService $cart): void
    {
        $listing = Listing::where('id', $listingId)
            ->where('type', 'plat_du_jour')
            ->where('is_active', true)
            ->firstOrFail();

        $cart->add($listing);

        $this->addedMessage = "\"{$listing->name}\" ajouté au panier.";
    }

    public function addFlashSaleToCart(int $listingId, CartService $cart): void
    {
        $listing = Listing::where('id', $listingId)
            ->where('is_active', true)
            ->onFlashSale()
            ->firstOrFail();

        $cart->add($listing);

        $this->addedMessage = "\"{$listing->name}\" ajouté au panier.";
    }

    public function render()
    {
        $trimmedQuery = trim($this->query);

        return view('livewire.home', [
            'vendors' => Vendor::where('is_active', true)
                ->when($this->type, fn ($query) => $query->where('vendor_type', $this->type))
                ->latest()
                ->paginate(12),
            'dishesOfTheDay' => Listing::activeDishesOfTheDay(),
            'flashSales' => Listing::activeFlashSales(),
            'featuredVendors' => Vendor::where('is_active', true)->where('verification_level', 'verifie')->latest()->limit(4)->get(),
            'searchResultVendors' => $trimmedQuery !== '' ? Vendor::searchActive($trimmedQuery) : collect(),
            'searchResultListings' => $trimmedQuery !== '' ? Listing::searchActive($trimmedQuery) : collect(),
        ]);
    }
}
