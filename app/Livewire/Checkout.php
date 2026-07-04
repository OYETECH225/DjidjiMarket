<?php

namespace App\Livewire;

use App\Models\Vendor;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Commande — DjidjiMarket'])]
class Checkout extends Component
{
    public string $delivery_address_text = '';

    public string $provider = 'cash_on_delivery';

    public function mount(CartService $cart): void
    {
        if ($cart->isEmpty()) {
            $this->redirectRoute('cart.show', navigate: true);
        }
    }

    public function placeOrder(CartService $cart, OrderService $orders, PaymentService $payments): void
    {
        $this->validate([
            'delivery_address_text' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'string', 'in:orange_money,mtn_money,moov_money,wave,cash_on_delivery'],
        ]);

        if ($cart->isEmpty()) {
            $this->redirectRoute('cart.show', navigate: true);

            return;
        }

        $vendor = Vendor::findOrFail($cart->vendorId());

        try {
            $order = $orders->createOrder(
                client: auth()->user(),
                vendor: $vendor,
                items: $cart->toOrderItems(),
                deliveryAddressText: $this->delivery_address_text,
                source: 'web',
            );

            $payments->initiate($order, $this->provider);
        } catch (ValidationException $e) {
            $this->addError('items', collect($e->errors())->flatten()->first());

            return;
        }

        $cart->clear();

        $this->redirectRoute('order.show', ['order' => $order->id], navigate: true);
    }

    public function render(CartService $cart)
    {
        return view('livewire.checkout', [
            'items' => $cart->items(),
            'total' => $cart->total(),
        ]);
    }
}
