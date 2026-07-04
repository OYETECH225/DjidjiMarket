<div class="mx-auto max-w-lg">
    <h1 class="mb-2 font-sans text-2xl font-bold text-djidji-green">Créer ma boutique</h1>
    <p class="mb-6 text-sm text-djidji-text/60">
        Ces informations seront visibles publiquement sur votre page boutique.
    </p>

    <form wire:submit="create" class="space-y-4">
        <x-input label="Nom de la boutique" wire:model="business_name" :error="$errors->first('business_name')" />

        <div>
            <label class="mb-1 block text-sm font-medium text-djidji-text">Type d'activité</label>
            <select wire:model="vendor_type" class="w-full rounded-lg border border-black/10 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-djidji-green">
                <option value="boutique">Boutique</option>
                <option value="street_food">Street food</option>
                <option value="restaurant">Restaurant</option>
            </select>
        </div>

        <div>
            <x-input label="Lien personnalisé" wire:model="slug" placeholder="ma-boutique" :error="$errors->first('slug')" />
            <p class="mt-1 text-xs text-djidji-text/50">djidjimarket.ci/boutique/{{ $slug ?: 'ma-boutique' }}</p>
        </div>

        <x-input label="Adresse" wire:model="address_text" placeholder="Quartier, ville" />

        <div>
            <label class="mb-1 block text-sm font-medium text-djidji-text">Description</label>
            <textarea wire:model="description" rows="3" class="w-full rounded-lg border border-black/10 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-djidji-green"></textarea>
        </div>

        <x-button type="submit" wire:loading.attr="disabled" wire:target="create">
            <span wire:loading.remove wire:target="create">Créer ma boutique</span>
            <span wire:loading wire:target="create">Création…</span>
        </x-button>
    </form>
</div>
