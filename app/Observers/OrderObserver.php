<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{
    public function created(Order $order): void
    {
        $this->logStatus($order);
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status')) {
            $this->logStatus($order);
        }
    }

    private function logStatus(Order $order): void
    {
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $order->status,
            'changed_at' => now(),
            'changed_by' => Auth::id() ?? 'system',
        ]);
    }
}
