<?php

namespace App\Livewire\Courier;

use App\Models\Order;
use App\Services\CourierDispatchService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Mes livraisons — DjidjiMarket'])]
class MyDeliveries extends Component
{
    public ?string $errorMessage = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'courier', 403);

        if (! auth()->user()->courier()->exists()) {
            $this->redirectRoute('courier.onboarding', navigate: true);
        }
    }

    public function advance(int $orderId, string $nextStatus, CourierDispatchService $dispatch): void
    {
        $order = Order::findOrFail($orderId);

        try {
            $dispatch->updateStatus(auth()->user(), $order, $nextStatus);
            $this->errorMessage = null;
        } catch (ValidationException $e) {
            $this->errorMessage = collect($e->errors())->flatten()->first();
        }
    }

    public function render()
    {
        return view('livewire.courier.my-deliveries', [
            'orders' => Order::where('courier_id', auth()->id())
                ->whereNotIn('status', ['livree', 'paiement_libere', 'annulee'])
                ->with('vendor')
                ->latest()
                ->get(),
            'transitions' => CourierDispatchService::ALLOWED_TRANSITIONS,
        ]);
    }
}
