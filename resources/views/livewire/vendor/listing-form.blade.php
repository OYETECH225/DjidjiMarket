<div class="mx-auto max-w-lg">
    <h1 class="mb-6 font-sans text-2xl font-bold text-djidji-green">
        {{ $listing ? 'Modifier l\'article' : 'Ajouter un article' }}
    </h1>

    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="mb-1 block text-sm font-medium text-djidji-text">Type</label>
            <select wire:model="type" class="w-full rounded-xl border border-djidji-outline px-3 py-2 focus:outline-none focus:ring-2 focus:ring-djidji-green">
                <option value="produit">Produit</option>
                <option value="plat_du_jour">Plat du jour</option>
                <option value="menu_item">Menu item</option>
            </select>
        </div>

        <x-input label="Nom de l'article" wire:model="name" :error="$errors->first('name')" />

        <div>
            <label class="mb-1 block text-sm font-medium text-djidji-text">Description</label>
            <textarea wire:model="description" rows="3" class="w-full rounded-xl border border-djidji-outline px-3 py-2 focus:outline-none focus:ring-2 focus:ring-djidji-green"></textarea>
        </div>

        <x-input label="Prix (XOF)" type="number" wire:model="price" :error="$errors->first('price')" />

        <div class="rounded-xl border border-djidji-outline p-4">
            <p class="mb-3 text-sm font-semibold text-djidji-green">Vente flash (optionnel)</p>
            <div class="space-y-4">
                <x-input label="Prix promo (XOF)" type="number" wire:model="sale_price" :error="$errors->first('sale_price')" />
                <x-input label="Se termine le" type="datetime-local" wire:model="sale_ends_at" :error="$errors->first('sale_ends_at')" />
            </div>
        </div>

        <x-input label="Stock (laisser vide si non applicable)" type="number" wire:model="stock_quantity" />
        <x-input label="Numéro d'affichage (pour les lives)" type="number" wire:model="display_number" />
        <x-input label="Code promo" wire:model="promo_code" />

        <label class="flex items-center gap-2">
            <input type="checkbox" wire:model="is_active" class="rounded text-djidji-green focus:ring-djidji-green">
            <span class="text-sm text-djidji-text">Article actif (visible dans la boutique)</span>
        </label>

        @if ($listing && $listing->photo_urls)
            <div>
                <p class="mb-2 text-sm font-medium text-djidji-text">Photos actuelles</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($listing->photo_urls as $url)
                        <div class="relative">
                            <img src="{{ $url }}" class="h-20 w-20 rounded-xl object-cover">
                            <button
                                type="button"
                                wire:click="removePhoto('{{ $url }}')"
                                class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full border border-djidji-outline bg-white text-djidji-error"
                            >
                                &times;
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div>
            <label class="mb-1 block text-sm font-medium text-djidji-text">Ajouter des photos</label>
            <input type="file" wire:model="newPhotos" multiple accept="image/*" class="w-full text-sm">
            @error('newPhotos.*')
                <p class="mt-1 text-sm text-djidji-error">{{ $message }}</p>
            @enderror
        </div>

        <x-button type="submit" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save">Enregistrer</span>
            <span wire:loading wire:target="save">Enregistrement…</span>
        </x-button>
    </form>
</div>
