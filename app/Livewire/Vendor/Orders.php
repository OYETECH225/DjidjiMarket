<?php

namespace App\Livewire\Vendor;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Mes commandes — DjidjiMarket'])]
class Orders extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'vendor', 403);

        if (! auth()->user()->vendor()->exists()) {
            $this->redirectRoute('vendor.onboarding', navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.vendor.orders', [
            'orders' => auth()->user()->vendor()->firstOrFail()->orders()->with('client')->latest()->get(),
        ]);
    }
}
