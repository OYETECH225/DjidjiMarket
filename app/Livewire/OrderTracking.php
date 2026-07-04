<?php

namespace App\Livewire;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class OrderTracking extends Component
{
    public Order $order;

    public ?string $errorMessage = null;

    public function mount(Order $order): void
    {
        abort_unless($order->client_id === auth()->id(), 403);

        $this->order = $order;
    }

    public function confirmReceipt(PaymentService $payments): void
    {
        try {
            $payments->confirmReceipt($this->order);
            $this->order->refresh();
        } catch (ValidationException $e) {
            $this->errorMessage = collect($e->errors())->flatten()->first();
        }
    }

    public function render()
    {
        return view('livewire.order-tracking', [
            'statusLabel' => Order::STATUS_LABELS[$this->order->status] ?? $this->order->status,
            'items' => $this->order->items()->with('listing')->get(),
        ])->layout('layouts.app', ['title' => "Commande #{$this->order->id} — DjidjiMarket"]);
    }
}
