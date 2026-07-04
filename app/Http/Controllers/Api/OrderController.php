<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Vendor;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly PaymentService $payments,
    ) {}

    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();
        $vendor = Vendor::findOrFail($data['vendor_id']);

        $order = $this->orders->createOrder(
            client: $request->user(),
            vendor: $vendor,
            items: $data['items'],
            deliveryAddressText: $data['delivery_address_text'],
            deliveryLatitude: $data['delivery_latitude'] ?? null,
            deliveryLongitude: $data['delivery_longitude'] ?? null,
            source: $data['source'] ?? 'app',
        );

        return response()->json(['order' => new OrderResource($order->load('items'))], 201);
    }

    public function show(Request $request, Order $order)
    {
        $user = $request->user();

        abort_unless(
            $order->client_id === $user->id
                || $order->vendor->user_id === $user->id
                || $user->role === 'admin',
            403
        );

        return new OrderResource($order->load('items.listing'));
    }

    public function confirmReceipt(Request $request, Order $order)
    {
        abort_unless($order->client_id === $request->user()->id, 403);

        $this->payments->confirmReceipt($order);

        return new OrderResource($order->refresh());
    }
}
