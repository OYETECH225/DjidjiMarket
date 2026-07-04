<div>
    <div class="mb-8 flex items-center gap-4 rounded-xl bg-white p-5 shadow-sm">
        <img
            src="{{ $vendor->logo_url ?? '/images/DjidjiMarket-icone-seule.png' }}"
            alt="{{ $vendor->business_name }}"
            class="h-16 w-16 rounded-full object-cover"
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

    @if ($addedMessage)
        <div class="mb-4 rounded-lg bg-djidji-green/10 px-4 py-2 text-sm text-djidji-green">
            {{ $addedMessage }}
            — <a href="{{ route('cart.show') }}" class="font-semibold underline">voir le panier</a>
        </div>
    @endif

    @if ($listings->isEmpty())
        <p class="text-center text-djidji-text/60">Cette boutique n'a pas encore de produits.</p>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
            @foreach ($listings as $listing)
                <div class="flex flex-col justify-between rounded-xl border border-black/5 bg-white p-4 shadow-sm">
                    <div>
                        <p class="font-sans font-semibold text-djidji-text">{{ $listing->name }}</p>
                        @if ($listing->description)
                            <p class="mt-1 text-sm text-djidji-text/60">{{ $listing->description }}</p>
                        @endif
                        <p class="mt-2 font-semibold text-djidji-orange">{{ number_format($listing->price, 0, ',', ' ') }} {{ $listing->currency }}</p>
                    </div>

                    <x-button wire:click="addToCart({{ $listing->id }})" variant="secondary" class="mt-4">
                        Ajouter au panier
                    </x-button>
                </div>
            @endforeach
        </div>
    @endif
</div>
