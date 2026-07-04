<div>
    <h1 class="mb-6 font-sans text-2xl font-bold text-djidji-green">Commandes disponibles</h1>

    @if ($message)
        <div class="mb-4 rounded-xl bg-djidji-error/10 px-4 py-2 text-sm text-djidji-error">{{ $message }}</div>
    @endif

    @if ($orders->isEmpty())
        <p class="text-center text-djidji-text/60">Aucune commande en attente pour le moment.</p>
    @else
        <div class="space-y-3">
            @foreach ($orders as $order)
                <div class="flex items-center justify-between rounded-xl border border-djidji-outline bg-white p-4">
                    <div>
                        <p class="font-semibold text-djidji-text">{{ $order->vendor->business_name }}</p>
                        <p class="text-sm text-djidji-text/60">{{ $order->vendor->address_text }}</p>
                        <p class="mt-1 text-sm text-djidji-text/60">Livraison : {{ $order->delivery_address_text }}</p>
                    </div>
                    <x-button wire:click="accept({{ $order->id }})" wire:loading.attr="disabled" wire:target="accept({{ $order->id }})" class="!w-auto px-6">
                        Accepter
                    </x-button>
                </div>
            @endforeach
        </div>
    @endif
</div>
