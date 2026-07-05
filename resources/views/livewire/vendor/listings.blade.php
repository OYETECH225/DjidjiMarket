<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="font-sans text-2xl font-bold text-djidji-green">Mon catalogue</h1>
        <a href="{{ route('vendor.listings.create') }}">
            <x-button>+ Ajouter un article</x-button>
        </a>
    </div>

    @if ($listings->isEmpty())
        <p class="text-center text-djidji-text/60">Aucun article pour le moment.</p>
    @else
        <div class="space-y-3">
            @foreach ($listings as $listing)
                <div class="flex items-center justify-between rounded-xl border border-djidji-outline bg-white p-4">
                    <div>
                        <p class="font-semibold text-djidji-text">{{ $listing->name }}</p>
                        <p class="text-sm text-djidji-text/60">
                            @if ($listing->isOnFlashSale())
                                <span class="line-through">{{ number_format($listing->price, 0, ',', ' ') }}</span>
                                <span class="font-semibold text-djidji-orange">{{ number_format($listing->sale_price, 0, ',', ' ') }} {{ $listing->currency }}</span>
                                <span class="text-xs text-djidji-orange">(vente flash jusqu'au {{ $listing->sale_ends_at->format('d/m H:i') }})</span>
                            @else
                                {{ number_format($listing->price, 0, ',', ' ') }} {{ $listing->currency }}
                            @endif
                            @if ($listing->stock_quantity !== null)
                                · Stock : {{ $listing->stock_quantity }}
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            wire:click="toggleActive({{ $listing->id }})"
                            class="rounded-full border px-3 py-1 text-xs font-medium {{ $listing->is_active ? 'border-djidji-green text-djidji-green' : 'border-djidji-error text-djidji-error' }}"
                        >
                            {{ $listing->is_active ? 'Actif' : 'Inactif' }}
                        </button>
                        <a href="{{ route('vendor.listings.edit', $listing) }}" class="text-sm font-medium text-djidji-green">Modifier</a>
                        <button
                            wire:click="delete({{ $listing->id }})"
                            wire:confirm="Supprimer cet article ?"
                            class="text-djidji-text/40 hover:text-djidji-error"
                        >
                            &times;
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
