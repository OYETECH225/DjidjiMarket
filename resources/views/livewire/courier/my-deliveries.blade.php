<div>
    <h1 class="mb-6 font-sans text-2xl font-bold text-djidji-green">Mes livraisons</h1>

    @if ($errorMessage)
        <div class="mb-4 rounded-xl bg-djidji-error/10 px-4 py-2 text-sm text-djidji-error">{{ $errorMessage }}</div>
    @endif

    @if ($orders->isEmpty())
        <p class="text-center text-djidji-text/60">Aucune livraison en cours.</p>
    @else
        <div class="space-y-3">
            @foreach ($orders as $order)
                <div class="rounded-xl border border-djidji-outline bg-white p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-djidji-text">{{ $order->vendor->business_name }}</p>
                            <p class="text-sm text-djidji-text/60">{{ $order->vendor->address_text }}</p>
                            <p class="mt-1 text-sm text-djidji-text/60">Livraison : {{ $order->delivery_address_text }}</p>
                        </div>
                        <span class="rounded-full bg-djidji-green/10 px-3 py-1 text-xs font-medium text-djidji-green">
                            {{ \App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}
                        </span>
                    </div>

                    @if (isset($transitions[$order->status]))
                        <x-button
                            wire:click="advance({{ $order->id }}, '{{ $transitions[$order->status] }}')"
                            class="mt-3 !w-auto px-6"
                        >
                            {{ \App\Models\Order::STATUS_LABELS[$transitions[$order->status]] ?? $transitions[$order->status] }}
                        </x-button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
