<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Mes commandes — DjidjiMarket', 'showBottomNav' => true])]
class MyOrders extends Component
{
    public function render()
    {
        return view('livewire.my-orders', [
            'orders' => auth()->user()->orders()->with('vendor')->latest()->get(),
        ]);
    }
}
