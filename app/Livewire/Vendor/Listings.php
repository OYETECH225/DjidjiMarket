<?php

namespace App\Livewire\Vendor;

use App\Models\Listing;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Mon catalogue — DjidjiMarket'])]
class Listings extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'vendor', 403);

        if (! auth()->user()->vendor()->exists()) {
            $this->redirectRoute('vendor.onboarding', navigate: true);
        }
    }

    public function toggleActive(int $listingId): void
    {
        $listing = $this->ownListing($listingId);
        $listing->update(['is_active' => ! $listing->is_active]);
    }

    public function delete(int $listingId): void
    {
        $this->ownListing($listingId)->delete();
    }

    private function ownListing(int $listingId): Listing
    {
        return auth()->user()->vendor()->firstOrFail()->listings()->findOrFail($listingId);
    }

    public function render()
    {
        return view('livewire.vendor.listings', [
            'listings' => auth()->user()->vendor()->firstOrFail()->listings()->latest()->get(),
        ]);
    }
}
