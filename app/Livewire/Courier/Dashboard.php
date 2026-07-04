<?php

namespace App\Livewire\Courier;

use App\Models\Courier;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Mon espace livreur — DjidjiMarket'])]
class Dashboard extends Component
{
    public Courier $courier;

    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'courier', 403);

        $courier = auth()->user()->courier()->first();

        if (! $courier) {
            $this->redirectRoute('courier.onboarding', navigate: true);

            return;
        }

        $this->courier = $courier;
    }

    public function toggleAvailability(): void
    {
        $this->courier->update(['is_available' => ! $this->courier->is_available]);
    }

    public function render()
    {
        return view('livewire.courier.dashboard', [
            'activeDeliveriesCount' => Order::where('courier_id', auth()->id())
                ->whereNotIn('status', ['livree', 'paiement_libere', 'annulee'])
                ->count(),
        ]);
    }
}
