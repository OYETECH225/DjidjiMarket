<div>
    <h1 class="mb-6 font-sans text-2xl font-bold text-djidji-green">Mes commandes</h1>

    @if ($orders->isEmpty())
        <p class="text-center text-djidji-text/60">Vous n'avez pas encore passé de commande.</p>
    @else
        <div class="space-y-3">
            @foreach ($orders as $order)
                <a href="{{ route('order.show', $order) }}" class="flex items-center justify-between rounded-xl border border-djidji-outline bg-white p-4 hover:border-djidji-green">
                    <div>
                        <p class="font-semibold text-djidji-text">{{ $order->vendor->business_name }}</p>
                        <p class="text-sm text-djidji-text/60">{{ number_format($order->total_amount, 0, ',', ' ') }} XOF · {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="rounded-full bg-djidji-green/10 px-3 py-1 text-xs font-medium text-djidji-green">
                        {{ \App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}
                    </span>
                </a>
            @endforeach
        </div>
    @endif
</div>
