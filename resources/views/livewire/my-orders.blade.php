<div>
    <h1 class="mb-6 font-sans text-2xl font-bold text-djidji-green">Mes commandes</h1>

    <div class="mb-6 flex rounded-full border border-djidji-outline bg-djidji-outline/10 p-1">
        <button
            type="button"
            wire:click="selectTab('en_cours')"
            class="flex-1 rounded-full py-2 text-sm font-semibold transition {{ $tab === 'en_cours' ? 'bg-djidji-green text-white' : 'text-djidji-text/60' }}"
        >
            En cours
        </button>
        <button
            type="button"
            wire:click="selectTab('terminees')"
            class="flex-1 rounded-full py-2 text-sm font-semibold transition {{ $tab === 'terminees' ? 'bg-djidji-green text-white' : 'text-djidji-text/60' }}"
        >
            Terminées
        </button>
    </div>

    @if ($orders->isEmpty())
        <p class="text-center text-djidji-text/60">
            {{ $tab === 'terminees' ? 'Aucune commande terminée pour le moment.' : "Aucune commande en cours." }}
        </p>
    @else
        <div class="space-y-3">
            @foreach ($orders as $order)
                @php($firstItem = $order->items->first())
                <a href="{{ route('order.show', $order) }}" class="flex items-center gap-4 rounded-xl border border-djidji-outline bg-white p-4 hover:border-djidji-green">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-djidji-green/10">
                        @if ($firstItem?->listing?->photo_urls[0] ?? null)
                            <img src="{{ $firstItem->listing->photo_urls[0] }}" class="h-full w-full object-cover" alt="">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6 text-djidji-green">
                                <path fill-rule="evenodd" d="M6 3a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3zm2.5 4.5a.75.75 0 0 0 0 1.5h7a.75.75 0 0 0 0-1.5zm0 4a.75.75 0 0 0 0 1.5h7a.75.75 0 0 0 0-1.5zm0 4a.75.75 0 0 0 0 1.5h4a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold text-djidji-text">{{ $firstItem?->listing?->name ?? $order->vendor->business_name }}</p>
                        <p class="truncate text-xs text-djidji-text/60">Vendu par : {{ $order->vendor->business_name }}</p>
                        <p class="text-xs text-djidji-text/50">{{ $order->created_at->format('d M Y · H:i') }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <span class="mb-1 inline-block rounded-full bg-djidji-orange/10 px-3 py-1 text-xs font-semibold text-djidji-orange">
                            {{ \App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}
                        </span>
                        <p class="font-semibold text-djidji-text">{{ number_format($order->total_amount, 0, ',', ' ') }} XOF</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
