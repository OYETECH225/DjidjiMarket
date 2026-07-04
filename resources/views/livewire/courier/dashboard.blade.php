<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="font-sans text-2xl font-bold text-djidji-green">Mon espace livreur</h1>
            <span class="mt-1 inline-block rounded-full bg-djidji-green/10 px-2 py-0.5 text-xs font-medium text-djidji-green">
                {{ \App\Models\Courier::VEHICLE_TYPE_LABELS[$courier->vehicle_type] }} ·
                {{ \App\Models\Courier::VERIFICATION_STATUS_LABELS[$courier->verification_status] }}
            </span>
        </div>

        <button
            wire:click="toggleAvailability"
            class="rounded-full border px-4 py-1.5 text-sm font-medium {{ $courier->is_available ? 'border-djidji-green text-djidji-green' : 'border-djidji-error text-djidji-error' }}"
        >
            {{ $courier->is_available ? 'Disponible' : 'Indisponible' }}
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <a href="{{ route('courier.available-orders') }}" class="rounded-xl border border-djidji-outline bg-white p-5">
            <p class="text-sm text-djidji-text/60">Commandes disponibles</p>
            <p class="mt-1 font-sans text-lg font-semibold text-djidji-text">Voir la liste d'attente →</p>
        </a>

        <a href="{{ route('courier.deliveries') }}" class="rounded-xl border border-djidji-outline bg-white p-5">
            <p class="text-sm text-djidji-text/60">Mes livraisons</p>
            <p class="mt-1 font-sans text-3xl font-bold text-djidji-text">{{ $activeDeliveriesCount }}</p>
            <p class="mt-1 text-sm font-medium text-djidji-green">en cours →</p>
        </a>
    </div>

    @unless ($courier->is_available)
        <p class="mt-6 text-center text-sm text-djidji-text/50">
            Passez-vous disponible pour voir les commandes en attente d'un livreur.
        </p>
    @endunless
</div>
