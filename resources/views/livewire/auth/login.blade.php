<div class="mx-auto max-w-sm">
    <div class="mb-6 text-center">
        <img src="/images/DjidjiMarket-icone-seule.png" alt="DjidjiMarket" class="mx-auto h-12 w-12">
        <h1 class="mt-3 font-sans text-2xl font-bold text-djidji-green">
            djidji<span class="text-djidji-orange">market</span>
        </h1>
    </div>

    @unless ($codeSent)
        <div class="mb-6 flex rounded-full border border-djidji-outline bg-djidji-outline/10 p-1">
            <button
                type="button"
                wire:click="selectRole('client')"
                class="flex-1 rounded-full py-2 text-sm font-semibold transition {{ $role === 'client' ? 'bg-white text-djidji-green shadow-sm' : 'text-djidji-text/60' }}"
            >
                Client
            </button>
            <button
                type="button"
                wire:click="selectRole('vendor')"
                class="flex-1 rounded-full py-2 text-sm font-semibold transition {{ $role === 'vendor' ? 'bg-white text-djidji-green shadow-sm' : 'text-djidji-text/60' }}"
            >
                Vendeur
            </button>
        </div>

        <form wire:submit="requestCode" class="space-y-4">
            <x-input label="Numéro de téléphone" type="tel" placeholder="+225 07 00 00 00 00" wire:model="phone" :error="$errors->first('phone')" />

            <x-button type="submit" wire:loading.attr="disabled" wire:target="requestCode">
                <span wire:loading.remove wire:target="requestCode">Recevoir le code</span>
                <span wire:loading wire:target="requestCode">Envoi…</span>
            </x-button>
        </form>

        <p class="mt-4 text-center text-xs text-djidji-text/50">
            Vous êtes livreur ?
            <button type="button" wire:click="selectRole('courier')" class="font-medium text-djidji-green underline">Inscrivez-vous ici</button>
        </p>
    @else
        <div class="mb-4 text-center">
            <p class="text-sm text-djidji-text/70">
                Code envoyé au <span class="font-semibold text-djidji-text">{{ $phone }}</span>.
                <button type="button" wire:click="changeNumber" class="font-medium text-djidji-green underline">Changer</button>
            </p>
        </div>

        <form wire:submit="verify" class="space-y-4">
            @if ($isNewUser)
                <x-input label="Votre nom" wire:model="name" :error="$errors->first('name')" />
            @endif

            <div
                x-data="{ digits: ['', '', '', '', '', ''] }"
                x-init="$watch('digits', value => $wire.set('code', value.join('')))"
            >
                <label class="mb-2 block text-sm font-medium text-djidji-text">Code à 6 chiffres</label>
                <div class="flex justify-between gap-2">
                    @for ($i = 0; $i < 6; $i++)
                        <input
                            type="text"
                            inputmode="numeric"
                            maxlength="1"
                            x-model="digits[{{ $i }}]"
                            x-ref="otp{{ $i }}"
                            @input="if ($event.target.value && {{ $i }} < 5) $refs.otp{{ $i + 1 }}.focus()"
                            @keydown.backspace="if (!digits[{{ $i }}] && {{ $i }} > 0) $refs.otp{{ $i - 1 }}.focus()"
                            class="h-14 w-12 rounded-xl border border-djidji-outline text-center text-xl font-bold focus:outline-none focus:ring-2 focus:ring-djidji-green"
                        >
                    @endfor
                </div>
                @error('code') <p class="mt-1 text-sm text-djidji-error">{{ $message }}</p> @enderror
            </div>

            <x-button type="submit" wire:loading.attr="disabled" wire:target="verify">
                <span wire:loading.remove wire:target="verify">Vérifier</span>
                <span wire:loading wire:target="verify">Vérification…</span>
            </x-button>
        </form>

        <p class="mt-4 text-center text-sm text-djidji-text/60">
            @if ($resendMessage)
                {{ $resendMessage }}
            @else
                Vous n'avez rien reçu ?
                <button type="button" wire:click="resend" class="font-medium text-djidji-green underline">Renvoyer le code</button>
            @endif
        </p>
    @endunless
</div>
