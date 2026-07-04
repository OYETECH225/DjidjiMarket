<div>
    <h1 class="mb-6 font-sans text-2xl font-bold text-djidji-green">Mes commandes</h1>

    @if ($orders->isEmpty())
        <p class="text-center text-djidji-text/60">Aucune commande reçue pour le moment.</p>
    @else
        <div class="space-y-3">
            @foreach ($orders as $order)
                <div class="flex items-center justify-between rounded-xl border border-djidji-outline bg-white p-4">
                    <div>
                        <p class="font-semibold text-djidji-text">Commande #{{ $order->id }} — {{ $order->client->name }}</p>
                        <p class="text-sm text-djidji-text/60">{{ number_format($order->total_amount, 0, ',', ' ') }} XOF · {{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <span class="rounded-full bg-djidji-green/10 px-3 py-1 text-xs font-medium text-djidji-green">
                        {{ \App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}
                    </span>
                </div>
            @endforeach
        </div>

        <p class="mt-4 text-center text-xs text-djidji-text/40">
            La progression du statut (préparation, recherche livreur…) se fait depuis le panel admin ou l'app livreur.
        </p>
    @endif
</div>
