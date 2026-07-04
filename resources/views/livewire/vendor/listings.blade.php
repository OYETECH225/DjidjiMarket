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
                <div class="flex items-center justify-between rounded-xl border border-black/5 bg-white p-4 shadow-sm">
                    <div>
                        <p class="font-semibold text-djidji-text">{{ $listing->name }}</p>
                        <p class="text-sm text-djidji-text/60">
                            {{ number_format($listing->price, 0, ',', ' ') }} {{ $listing->currency }}
                            @if ($listing->stock_quantity !== null)
                                · Stock : {{ $listing->stock_quantity }}
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            wire:click="toggleActive({{ $listing->id }})"
                            class="rounded-full border px-3 py-1 text-xs font-medium {{ $listing->is_active ? 'border-djidji-green text-djidji-green' : 'border-red-300 text-red-600' }}"
                        >
                            {{ $listing->is_active ? 'Actif' : 'Inactif' }}
                        </button>
                        <a href="{{ route('vendor.listings.edit', $listing) }}" class="text-sm font-medium text-djidji-green">Modifier</a>
                        <button
                            wire:click="delete({{ $listing->id }})"
                            wire:confirm="Supprimer cet article ?"
                            class="text-djidji-text/40 hover:text-red-600"
                        >
                            &times;
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
