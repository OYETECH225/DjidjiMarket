<div class="mx-auto max-w-sm">
    <h1 class="mb-6 text-center font-sans text-2xl font-bold text-djidji-green">Créer un compte</h1>

    <form wire:submit="register" class="space-y-4">
        <x-input label="Nom complet" wire:model="name" :error="$errors->first('name')" />
        <x-input label="Téléphone" type="tel" placeholder="+225 07 00 00 00 00" wire:model="phone" :error="$errors->first('phone')" />

        <div>
            <label class="mb-1 block text-sm font-medium text-djidji-text">Je suis...</label>
            <select wire:model="role" class="w-full rounded-xl border border-djidji-outline px-3 py-2 focus:outline-none focus:ring-2 focus:ring-djidji-green">
                <option value="client">Client</option>
                <option value="vendor">Vendeur</option>
                <option value="courier">Livreur</option>
            </select>
        </div>

        <x-input label="Mot de passe" type="password" wire:model="password" :error="$errors->first('password')" />
        <x-input label="Confirmer le mot de passe" type="password" wire:model="password_confirmation" />

        <x-button type="submit" wire:loading.attr="disabled" wire:target="register">
            <span wire:loading.remove wire:target="register">Créer mon compte</span>
            <span wire:loading wire:target="register">Création…</span>
        </x-button>
    </form>

    <p class="mt-4 text-center text-sm text-djidji-text/60">
        Déjà un compte ? <a href="{{ route('login') }}" class="font-medium text-djidji-green">Se connecter</a>
    </p>
</div>
