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

    public ?string $addedMessage = null;

    public function filterBy(?string $type): void
    {
        $this->type = $type && array_key_exists($type, Vendor::VENDOR_TYPE_LABELS) ? $type : null;
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

    public function render()
    {
        return view('livewire.home', [
            'vendors' => Vendor::where('is_active', true)
                ->when($this->type, fn ($query) => $query->where('vendor_type', $this->type))
                ->latest()
                ->paginate(12),
            'dishesOfTheDay' => Listing::where('type', 'plat_du_jour')
                ->where('is_active', true)
                ->whereHas('vendor', fn ($query) => $query->where('is_active', true))
                ->with('vendor')
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }
}
