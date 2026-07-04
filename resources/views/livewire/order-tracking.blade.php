<div class="mx-auto max-w-lg">
    <h1 class="mb-1 font-sans text-2xl font-bold text-djidji-green">Commande #{{ $order->id }}</h1>
    <p class="mb-6 text-sm text-djidji-text/60">Chez {{ $order->vendor->business_name }}</p>

    <div class="mb-6 rounded-xl border border-djidji-outline bg-white p-4">
        <span class="inline-block rounded-full bg-djidji-green/10 px-3 py-1 text-sm font-semibold text-djidji-green">
            {{ $statusLabel }}
        </span>

        <div class="mt-4 space-y-1 text-sm">
            @foreach ($items as $item)
                <div class="flex justify-between">
                    <span>{{ $item->quantity }} × {{ $item->listing->name }}</span>
                    <span>{{ number_format($item->unit_price * $item->quantity, 0, ',', ' ') }} XOF</span>
                </div>
            @endforeach
        </div>

        <div class="mt-3 flex justify-between border-t border-djidji-outline pt-3 font-semibold">
            <span>Total</span>
            <span class="text-djidji-green">{{ number_format($order->total_amount, 0, ',', ' ') }} XOF</span>
        </div>

        <p class="mt-3 text-sm text-djidji-text/60">Livraison : {{ $order->delivery_address_text }}</p>
    </div>

    @if ($errorMessage)
        <div class="mb-4 rounded-xl bg-djidji-error/10 px-4 py-2 text-sm text-djidji-error">{{ $errorMessage }}</div>
    @endif

    @if ($order->status === 'livree')
        <x-button wire:click="confirmReceipt" wire:loading.attr="disabled" wire:target="confirmReceipt">
            <span wire:loading.remove wire:target="confirmReceipt">J'ai reçu ma commande</span>
            <span wire:loading wire:target="confirmReceipt">Confirmation…</span>
        </x-button>
    @elseif ($order->status === 'paiement_libere')
        <p class="text-center text-sm font-medium text-djidji-green">Merci ! Réception confirmée.</p>
    @endif
</div>
