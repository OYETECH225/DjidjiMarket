<?php

namespace App\Livewire\Courier;

use App\Models\Order;
use App\Services\CourierDispatchService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Commandes disponibles — DjidjiMarket'])]
class AvailableOrders extends Component
{
    public ?string $message = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->role === 'courier', 403);

        $courier = auth()->user()->courier()->first();

        if (! $courier) {
            $this->redirectRoute('courier.onboarding', navigate: true);

            return;
        }

        abort_unless($courier->is_available, 403, 'Passez-vous disponible pour voir les commandes en attente.');
    }

    public function accept(int $orderId, CourierDispatchService $dispatch): void
    {
        $order = Order::findOrFail($orderId);
        $accepted = $dispatch->accept(auth()->user(), $order);

        if (! $accepted) {
            $this->message = 'Cette commande vient d\'être prise par un autre livreur.';

            return;
        }

        $this->redirectRoute('courier.deliveries', navigate: true);
    }

    public function render()
    {
        return view('livewire.courier.available-orders', [
            'orders' => Order::where('status', 'cherche_livreur')
                ->whereNull('courier_id')
                ->with('vendor')
                ->oldest()
                ->get(),
        ]);
    }
}
