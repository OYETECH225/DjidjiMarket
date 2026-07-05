<div class="mt-6 space-y-16">
    <x-full-bleed class="overflow-hidden bg-djidji-green py-6 text-white md:py-16">
        <div class="grid items-center gap-6 md:grid-cols-2">
            <div class="mx-auto max-w-xl space-y-6 text-center md:mx-0 md:max-w-md md:text-left">
                <h1 class="font-sans text-3xl font-extrabold leading-tight md:text-4xl">Le vrai marché, en toute confiance</h1>
                <p class="text-white/80">Commandez vos produits locaux préférés. Livraison sécurisée et vendeurs vérifiés en Côte d'Ivoire.</p>
                <div class="flex flex-wrap justify-center gap-4 md:justify-start">
                    <a href="#boutiques" class="rounded-full bg-djidji-orange px-6 py-2.5 font-semibold text-white hover:brightness-95">
                        Découvrir les boutiques
                    </a>
                    <a href="#confiance" class="rounded-full border border-white/60 px-6 py-2.5 font-semibold text-white hover:bg-white/10">
                        Comment ça marche
                    </a>
                </div>
            </div>
            <div class="hidden grid-cols-2 gap-4 md:grid">
                <div class="space-y-4">
                    <div class="h-48 rounded-xl border border-white/20 bg-cover bg-center" style="background-image: url('https://picsum.photos/seed/djidji-hero-1/400/300')"></div>
                    <div class="h-64 rounded-xl border border-white/20 bg-cover bg-center" style="background-image: url('https://picsum.photos/seed/djidji-hero-2/400/400')"></div>
                </div>
                <div class="space-y-4 pt-6">
                    <div class="h-64 rounded-xl border border-white/20 bg-cover bg-center" style="background-image: url('https://picsum.photos/seed/djidji-hero-3/400/400')"></div>
                    <div class="h-48 rounded-xl border border-white/20 bg-cover bg-center" style="background-image: url('https://picsum.photos/seed/djidji-hero-4/400/300')"></div>
                </div>
            </div>
        </div>
    </x-full-bleed>

    <x-full-bleed>
        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-djidji-text/40">
                <path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5M2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5" clip-rule="evenodd"/>
            </svg>
            <input
                type="search"
                wire:model.live.debounce.400ms="query"
                placeholder="Rechercher une boutique, un article..."
                class="w-full rounded-full border border-djidji-outline bg-white py-3 pl-11 pr-4 text-djidji-text focus:outline-none focus:ring-2 focus:ring-djidji-green"
            >
        </div>
    </x-full-bleed>

    @if (trim($query) !== '')
        <x-full-bleed>
            <h2 class="mb-6 font-sans text-xl font-bold text-djidji-green">Résultats pour "{{ $query }}"</h2>

            @if ($searchResultVendors->isEmpty() && $searchResultListings->isEmpty())
                <p class="text-center text-djidji-text/60">Aucun résultat.</p>
            @else
                @if ($searchResultVendors->isNotEmpty())
                    <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-djidji-text/50">Boutiques</p>
                    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                        @foreach ($searchResultVendors as $vendor)
                            <a href="{{ route('vendor.show', $vendor->slug) }}" class="flex items-center gap-3 rounded-xl border border-djidji-outline bg-white p-4">
                                <img src="{{ $vendor->logo_url ?? '/images/DjidjiMarket-icone-seule.png' }}" alt="{{ $vendor->business_name }}" class="h-12 w-12 rounded-full object-cover">
                                <div>
                                    <p class="font-sans font-semibold text-djidji-text">{{ $vendor->business_name }}</p>
                                    <p class="text-xs uppercase tracking-wide text-djidji-text/50">{{ \App\Models\Vendor::VENDOR_TYPE_LABELS[$vendor->vendor_type] ?? $vendor->vendor_type }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if ($searchResultListings->isNotEmpty())
                    <p class="mb-3 text-sm font-semibold uppercase tracking-wide text-djidji-text/50">Articles</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                        @foreach ($searchResultListings as $listing)
                            <a href="{{ route('vendor.show', $listing->vendor->slug) }}" class="rounded-xl border border-djidji-outline bg-white p-4">
                                <p class="font-sans font-semibold text-djidji-text">{{ $listing->name }}</p>
                                <p class="text-xs text-djidji-text/50">{{ $listing->vendor->business_name }}</p>
                                <x-listing-price :listing="$listing" class="mt-2" />
                            </a>
                        @endforeach
                    </div>
                @endif
            @endif
        </x-full-bleed>
    @endif

    <x-full-bleed id="confiance" class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div class="flex items-center gap-4 rounded-xl border border-djidji-outline bg-white p-6">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-djidji-green/10 text-djidji-green">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                    <path fill-rule="evenodd" d="M12 1.5c-1.03 0-2.02.29-2.88.82L3.6 5.4a2.25 2.25 0 0 0-1.1 1.94v6.16c0 5.16 3.55 9.7 8.42 10.9a2.25 2.25 0 0 0 1.16 0c4.87-1.2 8.42-5.74 8.42-10.9V7.34a2.25 2.25 0 0 0-1.1-1.94l-5.52-3.08A5.7 5.7 0 0 0 12 1.5m3.03 9.28-3.5 4.5a.75.75 0 0 1-1.15.06l-2-2a.75.75 0 1 1 1.06-1.06l1.38 1.38 2.98-3.83a.75.75 0 1 1 1.23.87" clip-rule="evenodd"/>
                </svg>
            </span>
            <div>
                <p class="font-sans font-semibold text-djidji-text">Paiement protégé</p>
                <p class="text-sm text-djidji-text/60">Votre argent n'est reversé au vendeur qu'après réception.</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-xl border border-djidji-outline bg-white p-6">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-djidji-orange/10 text-djidji-orange">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                    <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.49 4.49 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.49 4.49 0 0 1-1.307 3.497 4.49 4.49 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.49 4.49 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307m7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094z" clip-rule="evenodd"/>
                </svg>
            </span>
            <div>
                <p class="font-sans font-semibold text-djidji-text">Vendeurs vérifiés</p>
                <p class="text-sm text-djidji-text/60">Chaque boutique passe par une vérification.</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-xl border border-djidji-outline bg-white p-6">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-djidji-green/10 text-djidji-green">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                    <path d="M8.25 18.75a1.5 1.5 0 0 1-3 0 1.5 1.5 0 0 1 3 0M18.75 18.75a1.5 1.5 0 0 1-3 0 1.5 1.5 0 0 1 3 0"/>
                    <path fill-rule="evenodd" d="M1.5 4.5a.75.75 0 0 1 .75-.75h2.25a.75.75 0 0 1 .734.6l.259 1.29a.75.75 0 0 0 .734.6h13.5a.75.75 0 0 1 .728.925l-1.5 6a.75.75 0 0 1-.728.575H7.372a.75.75 0 0 0-.728.925l.09.359a.75.75 0 0 0 .728.575h9.038a.75.75 0 0 1 0 1.5H7.462a2.25 2.25 0 0 1-2.183-1.723L3.106 5.25H2.25a.75.75 0 0 1-.75-.75" clip-rule="evenodd"/>
                </svg>
            </span>
            <div>
                <p class="font-sans font-semibold text-djidji-text">Livraison rapide</p>
                <p class="text-sm text-djidji-text/60">Livraison garantie sous 24 à 48h.</p>
            </div>
        </div>
    </x-full-bleed>

    @if ($flashSales->isNotEmpty())
        <x-full-bleed class="rounded-xl border border-djidji-orange/30 bg-djidji-orange/5 py-8">
            <div class="mb-6 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6 text-djidji-orange">
                    <path fill-rule="evenodd" d="M14.615 1.595a.75.75 0 0 1 .359.852L12.982 9.75h7.268a.75.75 0 0 1 .548 1.262l-10.5 11.25a.75.75 0 0 1-1.272-.71l1.992-7.302H3.75a.75.75 0 0 1-.548-1.262l10.5-11.25a.75.75 0 0 1 .913-.143Z" clip-rule="evenodd"/>
                </svg>
                <h2 class="font-sans text-xl font-bold text-djidji-orange">Vente flash</h2>
            </div>
            <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                @foreach ($flashSales as $item)
                    <div class="overflow-hidden rounded-xl border border-djidji-outline bg-white">
                        <div class="relative h-32 bg-cover bg-center" style="background-image: url('https://picsum.photos/seed/djidji-flash-{{ $item->id }}/300/200')">
                            <span class="absolute left-2 top-2 rounded-full bg-djidji-orange px-2 py-0.5 text-xs font-bold text-white">
                                -{{ round((1 - $item->sale_price / $item->price) * 100) }}%
                            </span>
                        </div>
                        <div class="p-4">
                            <p class="font-sans font-semibold text-djidji-text">{{ $item->name }}</p>
                            <p class="text-xs text-djidji-text/50">{{ $item->vendor->business_name }}</p>
                            <p class="mt-1 text-xs font-medium text-djidji-orange">
                                @php($diff = now()->diff($item->sale_ends_at))
                                Se termine dans {{ $diff->h }}h{{ str_pad($diff->i, 2, '0', STR_PAD_LEFT) }}
                            </p>
                            <div class="mt-2 flex items-center justify-between">
                                <div class="flex items-baseline gap-2">
                                    <span class="font-semibold text-djidji-orange">{{ number_format($item->sale_price, 0, ',', ' ') }} {{ $item->currency }}</span>
                                    <span class="text-xs text-djidji-text/40 line-through">{{ number_format($item->price, 0, ',', ' ') }}</span>
                                </div>
                                <button
                                    type="button"
                                    wire:click="addFlashSaleToCart({{ $item->id }})"
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-djidji-orange text-white hover:brightness-95"
                                    aria-label="Ajouter {{ $item->name }} au panier"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                                        <path d="M12 4.5a.75.75 0 0 1 .75.75v6h6a.75.75 0 0 1 0 1.5h-6v6a.75.75 0 0 1-1.5 0v-6h-6a.75.75 0 0 1 0-1.5h6v-6A.75.75 0 0 1 12 4.5"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-full-bleed>
    @endif

    <x-full-bleed>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach (['boutique' => 'djidji-cat-boutique', 'street_food' => 'djidji-cat-food', 'restaurant' => 'djidji-cat-restaurant'] as $value => $seed)
                <button
                    type="button"
                    wire:click="filterBy('{{ $value }}')"
                    class="group relative h-80 overflow-hidden rounded-xl border {{ $type === $value ? 'border-djidji-green ring-2 ring-djidji-green' : 'border-djidji-outline' }} text-left"
                >
                    <div class="absolute inset-0 bg-cover bg-center transition-transform duration-500 group-hover:scale-105" style="background-image: url('https://picsum.photos/seed/{{ $seed }}/500/600')"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <span class="absolute bottom-4 left-4 rounded-full bg-white px-4 py-1.5 text-sm font-semibold text-djidji-green">
                        {{ \App\Models\Vendor::VENDOR_TYPE_LABELS[$value] }}
                    </span>
                </button>
            @endforeach
        </div>
    </x-full-bleed>

    @if ($featuredVendors->isNotEmpty())
        <x-full-bleed>
            <div class="mb-6 flex items-end justify-between">
                <div>
                    <h2 class="font-sans text-xl font-bold text-djidji-green">Vendeurs en vedette</h2>
                    <p class="text-sm text-djidji-text/60">Boutiques vérifiées récemment</p>
                </div>
                <a href="#boutiques" class="flex items-center gap-1 text-sm font-semibold text-djidji-orange hover:underline">
                    Voir tout →
                </a>
            </div>
            <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                @foreach ($featuredVendors as $vendor)
                    <a href="{{ route('vendor.show', $vendor->slug) }}" class="overflow-hidden rounded-xl border border-djidji-outline bg-white">
                        <div class="relative h-32 bg-djidji-green/10 bg-cover bg-center" @if ($vendor->cover_url) style="background-image: url('{{ $vendor->cover_url }}')" @endif>
                            <span class="absolute right-2 top-2 flex items-center gap-1 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold text-djidji-green">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-3 w-3">
                                    <path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.49 4.49 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.49 4.49 0 0 1-1.307 3.497 4.49 4.49 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.49 4.49 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307m7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094z" clip-rule="evenodd"/>
                                </svg>
                                VÉRIFIÉ
                            </span>
                        </div>
                        <div class="p-4">
                            <p class="truncate font-sans font-semibold text-djidji-text">{{ $vendor->business_name }}</p>
                            <p class="text-xs text-djidji-text/50">{{ \App\Models\Vendor::VENDOR_TYPE_LABELS[$vendor->vendor_type] ?? $vendor->vendor_type }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </x-full-bleed>
    @endif

    @if ($addedMessage)
        <div class="rounded-xl bg-djidji-green/10 px-4 py-2 text-sm text-djidji-green">
            {{ $addedMessage }}
            — <a href="{{ route('cart.show') }}" class="font-semibold underline">voir le panier</a>
        </div>
    @endif

    @if ($dishesOfTheDay->isNotEmpty())
        <x-full-bleed>
            <h2 class="mb-6 font-sans text-xl font-bold text-djidji-green">Plats du jour</h2>
            <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                @foreach ($dishesOfTheDay as $dish)
                    <div class="overflow-hidden rounded-xl border border-djidji-outline bg-white">
                        <div class="h-32 bg-cover bg-center" style="background-image: url('https://picsum.photos/seed/djidji-dish-{{ $dish->id }}/300/200')"></div>
                        <div class="p-4">
                            <p class="font-sans font-semibold text-djidji-text">{{ $dish->name }}</p>
                            <p class="text-xs text-djidji-text/50">{{ $dish->vendor->business_name }}</p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="font-semibold text-djidji-orange">{{ number_format($dish->price, 0, ',', ' ') }} {{ $dish->currency }}</span>
                                <button
                                    type="button"
                                    wire:click="addDishToCart({{ $dish->id }})"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-djidji-green text-white hover:bg-djidji-green-dark"
                                    aria-label="Ajouter {{ $dish->name }} au panier"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                                        <path d="M12 4.5a.75.75 0 0 1 .75.75v6h6a.75.75 0 0 1 0 1.5h-6v6a.75.75 0 0 1-1.5 0v-6h-6a.75.75 0 0 1 0-1.5h6v-6A.75.75 0 0 1 12 4.5"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-full-bleed>
    @endif

    <x-full-bleed id="boutiques">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="font-sans text-xl font-bold text-djidji-green">
                {{ $type ? \App\Models\Vendor::VENDOR_TYPE_LABELS[$type] : 'Toutes les boutiques' }}
            </h2>
            @if ($type)
                <button type="button" wire:click="filterBy(null)" class="text-sm font-semibold text-djidji-orange hover:underline">
                    Réinitialiser
                </button>
            @endif
        </div>

        @if ($vendors->isEmpty())
            <p class="text-center text-djidji-text/60">Aucune boutique disponible pour le moment.</p>
        @else
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($vendors as $vendor)
                    <a
                        href="{{ route('vendor.show', $vendor->slug) }}"
                        class="rounded-xl border border-djidji-outline bg-white p-4 transition"
                    >
                        <div class="flex items-center gap-3">
                            <img
                                src="{{ $vendor->logo_url ?? '/images/DjidjiMarket-icone-seule.png' }}"
                                alt="{{ $vendor->business_name }}"
                                class="h-12 w-12 rounded-full object-cover"
                            >
                            <div>
                                <p class="font-sans font-semibold text-djidji-text">{{ $vendor->business_name }}</p>
                                <p class="text-xs uppercase tracking-wide text-djidji-text/50">
                                    {{ \App\Models\Vendor::VENDOR_TYPE_LABELS[$vendor->vendor_type] ?? $vendor->vendor_type }}
                                </p>
                            </div>
                        </div>
                        @if ($vendor->verification_level === 'verifie')
                            <span class="mt-3 inline-block rounded-full bg-djidji-green/10 px-2 py-0.5 text-xs font-medium text-djidji-green">
                                ✓ Vérifié
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $vendors->links() }}
            </div>
        @endif
    </x-full-bleed>
</div>
