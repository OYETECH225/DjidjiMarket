<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Panier — DjidjiMarket'])]
class Cart extends Component
{
    public function updateQuantity(int $listingId, int $quantity, CartService $cart): void
    {
        $cart->updateQuantity($listingId, $quantity);
    }

    public function remove(int $listingId, CartService $cart): void
    {
        $cart->remove($listingId);
    }

    public function render(CartService $cart)
    {
        return view('livewire.cart', [
            'items' => $cart->items(),
            'total' => $cart->total(),
        ]);
    }
}
