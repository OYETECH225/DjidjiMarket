<?php

namespace App\Livewire;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Mes commandes — DjidjiMarket', 'showBottomNav' => true])]
class MyOrders extends Component
{
    #[Url]
    public string $tab = 'en_cours';

    public function selectTab(string $tab): void
    {
        $this->tab = in_array($tab, ['en_cours', 'terminees'], true) ? $tab : 'en_cours';
    }

    public function render()
    {
        $orders = auth()->user()->orders()->with(['vendor', 'items.listing'])->latest()->get();

        return view('livewire.my-orders', [
            'orders' => $orders->filter(fn (Order $order) => $this->tab === 'terminees' ? $order->isFinal() : ! $order->isFinal())->values(),
        ]);
    }
}
