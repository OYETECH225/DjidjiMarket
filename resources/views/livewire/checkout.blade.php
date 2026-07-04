<div class="mx-auto max-w-lg">
    <h1 class="mb-6 font-sans text-2xl font-bold text-djidji-green">Finaliser la commande</h1>

    <div class="mb-6 rounded-xl border border-djidji-outline bg-white p-4">
        @foreach ($items as $item)
            <div class="flex justify-between py-1 text-sm">
                <span>{{ $item['quantity'] }} × {{ $item['listing']->name }}</span>
                <span>{{ number_format($item['subtotal'], 0, ',', ' ') }} XOF</span>
            </div>
        @endforeach
        <div class="mt-2 flex justify-between border-t border-djidji-outline pt-2 font-semibold">
            <span>Total</span>
            <span class="text-djidji-green">{{ number_format($total, 0, ',', ' ') }} XOF</span>
        </div>
    </div>

    @error('items')
        <div class="mb-4 rounded-xl bg-djidji-error/10 px-4 py-2 text-sm text-djidji-error">{{ $message }}</div>
    @enderror

    <form wire:submit="placeOrder" class="space-y-4">
        <x-input label="Adresse de livraison" wire:model="delivery_address_text" placeholder="Quartier, rue, repère..." :error="$errors->first('delivery_address_text')" />

        <div>
            <label class="mb-1 block text-sm font-medium text-djidji-text">Mode de paiement</label>
            <div class="space-y-2">
                @foreach ([
                    'cash_on_delivery' => 'Paiement à la livraison',
                    'orange_money' => 'Orange Money',
                    'mtn_money' => 'MTN Money',
                    'moov_money' => 'Moov Money',
                    'wave' => 'Wave',
                ] as $value => $label)
                    <label class="flex items-center gap-2 rounded-xl border border-djidji-outline px-3 py-2 has-[:checked]:border-djidji-green has-[:checked]:bg-djidji-green/5">
                        <input type="radio" wire:model="provider" value="{{ $value }}" class="text-djidji-green focus:ring-djidji-green">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <p class="text-xs text-djidji-text/50">
            Le paiement mobile money est retenu de façon sécurisée jusqu'à confirmation de réception de votre commande.
        </p>

        <x-button type="submit" wire:loading.attr="disabled" wire:target="placeOrder">
            <span wire:loading.remove wire:target="placeOrder">Confirmer la commande</span>
            <span wire:loading wire:target="placeOrder">Traitement…</span>
        </x-button>
    </form>
</div>
