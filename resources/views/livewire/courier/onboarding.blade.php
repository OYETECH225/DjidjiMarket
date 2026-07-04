<div class="mx-auto max-w-lg">
    <h1 class="mb-2 font-sans text-2xl font-bold text-djidji-green">Devenir livreur</h1>
    <p class="mb-6 text-sm text-djidji-text/60">
        Renseignez votre moyen de transport pour commencer à recevoir des commandes.
    </p>

    <form wire:submit="create" class="space-y-4">
        <div>
            <label class="mb-1 block text-sm font-medium text-djidji-text">Moyen de transport</label>
            <select wire:model="vehicle_type" class="w-full rounded-xl border border-djidji-outline px-3 py-2 focus:outline-none focus:ring-2 focus:ring-djidji-green">
                <option value="moto">Moto</option>
                <option value="tricycle">Tricycle</option>
                <option value="velo">Vélo</option>
                <option value="pied">À pied</option>
            </select>
        </div>

        <x-button type="submit" wire:loading.attr="disabled" wire:target="create">
            <span wire:loading.remove wire:target="create">Devenir livreur</span>
            <span wire:loading wire:target="create">Création…</span>
        </x-button>
    </form>
</div>
