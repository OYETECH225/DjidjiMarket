<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="font-sans text-2xl font-bold text-djidji-green">{{ $vendor->business_name }}</h1>
            <span class="mt-1 inline-block rounded-full bg-djidji-green/10 px-2 py-0.5 text-xs font-medium text-djidji-green">
                {{ \App\Models\Vendor::VERIFICATION_LABELS[$vendor->verification_level] }}
            </span>
        </div>

        <button
            wire:click="toggleActive"
            class="rounded-full border px-4 py-1.5 text-sm font-medium {{ $vendor->is_active ? 'border-djidji-green text-djidji-green' : 'border-djidji-error text-djidji-error' }}"
        >
            {{ $vendor->is_active ? 'Boutique visible' : 'Boutique masquée' }}
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <a href="{{ route('vendor.listings') }}" class="rounded-xl border border-djidji-outline bg-white p-5">
            <p class="text-sm text-djidji-text/60">Catalogue</p>
            <p class="mt-1 font-sans text-3xl font-bold text-djidji-text">{{ $listingsCount }}</p>
            <p class="mt-1 text-sm font-medium text-djidji-green">Gérer mes articles →</p>
        </a>

        <a href="{{ route('vendor.orders') }}" class="rounded-xl border border-djidji-outline bg-white p-5">
            <p class="text-sm text-djidji-text/60">Commandes</p>
            <p class="mt-1 font-sans text-3xl font-bold text-djidji-text">{{ $ordersCount }}</p>
            <p class="mt-1 text-sm font-medium text-djidji-green">Voir les commandes →</p>
        </a>
    </div>

    <div class="mt-6 rounded-xl bg-white border border-djidji-outline p-4 text-sm text-djidji-text/70">
        Votre page publique : <a href="{{ route('vendor.show', $vendor->slug) }}" class="font-medium text-djidji-green underline">djidjimarket.ci/boutique/{{ $vendor->slug }}</a>
    </div>
</div>
