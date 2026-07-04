<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Courier\StoreCourierProfileRequest;
use App\Http\Requests\Courier\UpdateAvailabilityRequest;
use App\Http\Requests\Courier\UpdateDeliveryStatusRequest;
use App\Http\Resources\CourierResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CourierDispatchService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CourierController extends Controller
{
    public function __construct(private readonly CourierDispatchService $dispatch) {}

    public function storeProfile(StoreCourierProfileRequest $request)
    {
        $user = $request->user();

        if ($user->courier()->exists()) {
            throw ValidationException::withMessages([
                'vehicle_type' => ['Un profil livreur existe déjà pour ce compte.'],
            ]);
        }

        $courier = $user->courier()->create([
            ...$request->validated(),
            'verification_status' => 'en_attente',
            'is_available' => false,
        ]);

        return response()->json(['courier' => new CourierResource($courier)], 201);
    }

    public function me(Request $request)
    {
        $courier = $request->user()->courier()->first();

        abort_unless($courier, 404, 'Profil livreur introuvable.');

        return new CourierResource($courier);
    }

    public function myOrders(Request $request)
    {
        $orders = Order::where('courier_id', $request->user()->id)
            ->with('vendor')
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function updateAvailability(UpdateAvailabilityRequest $request)
    {
        // Query fresh rather than the cached `courier` relation property —
        // long-lived User instances (e.g. across requests in tests) would
        // otherwise keep returning a stale null after profile creation.
        $courier = $request->user()->courier()->first();

        abort_unless($courier, 404, 'Profil livreur introuvable.');

        $courier->update(['is_available' => $request->validated('is_available')]);

        return new CourierResource($courier);
    }

    public function available(Request $request)
    {
        $courier = $request->user()->courier()->first();

        abort_unless($courier, 404, 'Profil livreur introuvable.');
        abort_unless($courier->is_available, 403, 'Passez-vous disponible pour voir les commandes en attente.');

        // No geo-radius filtering yet (Phase 2's automated dispatch adds
        // that) — Phase 1's "basic" dispatch just lists every order
        // currently searching for a courier, oldest first.
        $orders = Order::where('status', 'cherche_livreur')
            ->whereNull('courier_id')
            ->with('vendor')
            ->oldest()
            ->paginate(20);

        return OrderResource::collection($orders);
    }

    public function accept(Request $request, Order $order)
    {
        $courier = $request->user()->courier()->first();

        abort_unless($courier, 404, 'Profil livreur introuvable.');

        $accepted = $this->dispatch->accept($request->user(), $order);

        abort_unless($accepted, 409, 'Commande déjà prise ou non disponible.');

        return new OrderResource($order->refresh());
    }

    public function updateStatus(UpdateDeliveryStatusRequest $request, Order $order)
    {
        $this->dispatch->updateStatus($request->user(), $order, $request->validated('status'));

        return new OrderResource($order->refresh());
    }
}
