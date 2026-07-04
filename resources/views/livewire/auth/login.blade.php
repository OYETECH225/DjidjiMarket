<div class="mx-auto max-w-sm">
    <h1 class="mb-6 text-center font-sans text-2xl font-bold text-djidji-green">Connexion</h1>

    <form wire:submit="login" class="space-y-4">
        <x-input label="Téléphone" type="tel" placeholder="+225 07 00 00 00 00" wire:model="phone" :error="$errors->first('phone')" />
        <x-input label="Mot de passe" type="password" wire:model="password" />

        <x-button type="submit" wire:loading.attr="disabled" wire:target="login">
            <span wire:loading.remove wire:target="login">Se connecter</span>
            <span wire:loading wire:target="login">Connexion…</span>
        </x-button>
    </form>

    <p class="mt-4 text-center text-sm text-djidji-text/60">
        Pas encore de compte ? <a href="{{ route('register') }}" class="font-medium text-djidji-green">Créer un compte</a>
    </p>
</div>
