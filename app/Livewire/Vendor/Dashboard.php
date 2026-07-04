<?php

namespace App\Livewire\Vendor;

use App\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Ma boutique — DjidjiMarket'])]
class Dashboard extends Component
{
    public Vendor $vendor;

    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'vendor', 403);

        $vendor = auth()->user()->vendor()->first();

        if (! $vendor) {
            $this->redirectRoute('vendor.onboarding', navigate: true);

            return;
        }

        $this->vendor = $vendor;
    }

    public function toggleActive(): void
    {
        $this->vendor->update(['is_active' => ! $this->vendor->is_active]);
    }

    public function render()
    {
        return view('livewire.vendor.dashboard', [
            'listingsCount' => $this->vendor->listings()->count(),
            'ordersCount' => $this->vendor->orders()->count(),
        ]);
    }
}
