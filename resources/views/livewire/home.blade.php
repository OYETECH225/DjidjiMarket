<div>
    <div class="mb-6 text-center">
        <h1 class="font-sans text-3xl font-bold text-djidji-green">Le vrai marché, en toute confiance</h1>
        <p class="mt-2 text-djidji-text/70">Découvrez les boutiques vérifiées près de chez vous.</p>
    </div>

    <div class="mb-6 rounded-xl border border-djidji-outline bg-djidji-green/5 p-4 text-center text-sm text-djidji-green">
        Paiement sécurisé : votre argent n'est reversé au vendeur qu'après réception de votre commande.
    </div>

    <div class="mb-6 flex flex-wrap justify-center gap-2">
        <button
            type="button"
            wire:click="filterBy(null)"
            class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ is_null($type) ? 'bg-djidji-green text-white' : 'border border-djidji-outline text-djidji-text/70' }}"
        >
            Tous
        </button>
        @foreach (\App\Models\Vendor::VENDOR_TYPE_LABELS as $value => $label)
            <button
                type="button"
                wire:click="filterBy('{{ $value }}')"
                class="rounded-full px-4 py-1.5 text-sm font-medium transition {{ $type === $value ? 'bg-djidji-green text-white' : 'border border-djidji-outline text-djidji-text/70' }}"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if ($vendors->isEmpty())
        <p class="text-center text-djidji-text/60">Aucune boutique disponible pour le moment.</p>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
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
</div>
