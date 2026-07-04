<div class="mx-auto max-w-sm">
    <h1 class="mb-2 text-center font-sans text-2xl font-bold text-djidji-green">Vérification</h1>
    <p class="mb-6 text-center text-sm text-djidji-text/60">
        Entrez le code à 6 chiffres envoyé au {{ $phone }}.
    </p>

    <form wire:submit="verify" class="space-y-4">
        <x-input label="Code de vérification" wire:model="code" maxlength="6" inputmode="numeric" :error="$errors->first('code')" />

        <x-button type="submit" wire:loading.attr="disabled" wire:target="verify">
            <span wire:loading.remove wire:target="verify">Vérifier</span>
            <span wire:loading wire:target="verify">Vérification…</span>
        </x-button>
    </form>

    <div class="mt-4 text-center text-sm">
        <button wire:click="resend" class="font-medium text-djidji-green hover:underline">Renvoyer le code</button>
        @if ($resendMessage)
            <p class="mt-1 text-djidji-text/60">{{ $resendMessage }}</p>
        @endif
    </div>
</div>
