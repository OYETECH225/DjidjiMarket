<div>
    <h1 class="mb-6 font-sans text-2xl font-bold text-djidji-green">Mon panier</h1>

    @if ($items->isEmpty())
        <p class="text-center text-djidji-text/60">
            Votre panier est vide. <a href="{{ route('home') }}" class="font-medium text-djidji-green">Découvrir les boutiques</a>
        </p>
    @else
        <div class="space-y-3">
            @foreach ($items as $item)
                <div class="flex items-center justify-between rounded-xl border border-black/5 bg-white p-4 shadow-sm">
                    <div>
                        <p class="font-semibold text-djidji-text">{{ $item['listing']->name }}</p>
                        <p class="text-sm text-djidji-text/60">{{ number_format($item['listing']->price, 0, ',', ' ') }} {{ $item['listing']->currency }}</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <input
                            type="number"
                            min="1"
                            value="{{ $item['quantity'] }}"
                            wire:change="updateQuantity({{ $item['listing']->id }}, $event.target.value)"
                            class="w-16 rounded-lg border border-black/10 px-2 py-1 text-center"
                        >
                        <p class="w-24 text-right font-semibold text-djidji-orange">
                            {{ number_format($item['subtotal'], 0, ',', ' ') }}
                        </p>
                        <button wire:click="remove({{ $item['listing']->id }})" class="text-djidji-text/40 hover:text-red-600">
                            &times;
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex items-center justify-between border-t border-black/10 pt-4">
            <p class="font-sans text-lg font-bold text-djidji-text">Total</p>
            <p class="font-sans text-lg font-bold text-djidji-green">{{ number_format($total, 0, ',', ' ') }} XOF</p>
        </div>

        <a href="{{ route('checkout') }}" class="mt-6 block">
            <x-button>Passer la commande</x-button>
        </a>
    @endif
</div>
