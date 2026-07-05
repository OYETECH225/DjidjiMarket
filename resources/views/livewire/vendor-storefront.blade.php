<div>
    <div class="mb-6 overflow-hidden rounded-xl border border-djidji-outline bg-white">
        <div
            class="h-28 w-full bg-djidji-green/10 bg-cover bg-center sm:h-40"
            @if ($vendor->cover_url) style="background-image: url('{{ $vendor->cover_url }}')" @endif
        ></div>

        <div class="flex items-center gap-4 p-5 pt-0">
            <img
                src="{{ $vendor->logo_url ?? '/images/DjidjiMarket-icone-seule.png' }}"
                alt="{{ $vendor->business_name }}"
                class="-mt-8 h-16 w-16 shrink-0 rounded-full border-4 border-white object-cover"
            >
            <div>
                <h1 class="font-sans text-2xl font-bold text-djidji-text">{{ $vendor->business_name }}</h1>
                <p class="text-sm text-djidji-text/60">{{ $vendor->address_text }}</p>
                @if ($vendor->verification_level === 'verifie')
                    <span class="mt-1 inline-block rounded-full bg-djidji-green/10 px-2 py-0.5 text-xs font-medium text-djidji-green">
                        ✓ Vérifié
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if ($addedMessage)
        <div class="mb-4 rounded-xl bg-djidji-green/10 px-4 py-2 text-sm text-djidji-green">
            {{ $addedMessage }}
            — <a href="{{ route('cart.show') }}" class="font-semibold underline">voir le panier</a>
        </div>
    @endif

    @if ($availableTypes->count() > 1)
        <div class="mb-6 flex flex-wrap gap-2">
            <button
                type="button"
                wire:click="filterBy(null)"
                class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ is_null($type) ? 'bg-djidji-green text-white' : 'border border-djidji-outline text-djidji-text/70' }}"
            >
                Tout
            </button>
            @foreach ($availableTypes as $value)
                <button
                    type="button"
                    wire:click="filterBy('{{ $value }}')"
                    class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ $type === $value ? 'bg-djidji-green text-white' : 'border border-djidji-outline text-djidji-text/70' }}"
                >
                    {{ \App\Models\Listing::TYPE_LABELS[$value] ?? $value }}
                </button>
            @endforeach
        </div>
    @endif

    @if ($listings->isEmpty())
        <p class="text-center text-djidji-text/60">Cette boutique n'a pas encore de produits.</p>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
            @foreach ($listings as $listing)
                <div class="flex flex-col justify-between rounded-xl border border-djidji-outline bg-white p-4">
                    <div>
                        <p class="font-sans font-semibold text-djidji-text">{{ $listing->name }}</p>
                        @if ($listing->description)
                            <p class="mt-1 text-sm text-djidji-text/60">{{ $listing->description }}</p>
                        @endif
                        <x-listing-price :listing="$listing" class="mt-2" />
                    </div>

                    <x-button wire:click="addToCart({{ $listing->id }})" class="mt-4">
                        Ajouter au panier
                    </x-button>
                </div>
            @endforeach
        </div>
    @endif
</div>
