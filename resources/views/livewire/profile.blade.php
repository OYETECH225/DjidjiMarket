<div>
    <h1 class="mb-6 font-sans text-2xl font-bold text-djidji-green">Profil</h1>

    <div class="flex items-center gap-4 rounded-xl border border-djidji-outline bg-white p-4">
        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-djidji-green/10 text-djidji-green">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-8 w-8">
                <path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653m-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438M15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0" clip-rule="evenodd"/>
            </svg>
        </div>
        <div>
            <p class="font-semibold text-djidji-text">{{ auth()->user()->name }}</p>
            <p class="text-sm text-djidji-text/60">{{ auth()->user()->phone }}</p>
            <span class="mt-1 inline-block rounded-full bg-djidji-green/10 px-3 py-1 text-xs font-medium text-djidji-green">
                {{ $roleLabel }}
            </span>
        </div>
    </div>

    @if (auth()->user()->role === 'client')
        <a href="{{ route('vendor.onboarding') }}" class="mt-4 flex items-center justify-between rounded-xl bg-djidji-orange px-4 py-4 text-white">
            <span>
                <span class="block font-semibold">Devenir vendeur sur DjidjiMarket</span>
                <span class="block text-sm text-white/80">Ouvrez votre boutique dès aujourd'hui</span>
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 shrink-0">
                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l6.5 6.5a.75.75 0 0 1 0 1.06l-6.5 6.5a.75.75 0 1 1-1.06-1.06L14.19 12 8.22 6.03a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
            </svg>
        </a>
    @endif

    <form wire:submit="save" class="mt-6 space-y-4 rounded-xl border border-djidji-outline bg-white p-4">
        <p class="font-semibold text-djidji-text">Informations personnelles</p>
        <x-input label="Nom complet" wire:model="name" :error="$errors->first('name')" />
        <x-input label="Adresse email" type="email" wire:model="email" :error="$errors->first('email')" placeholder="vous@exemple.ci" />

        @if ($savedMessage)
            <p class="text-sm text-djidji-green">{{ $savedMessage }}</p>
        @endif

        <x-button type="submit" wire:loading.attr="disabled" wire:target="save">
            <span wire:loading.remove wire:target="save">Enregistrer</span>
            <span wire:loading wire:target="save">Enregistrement…</span>
        </x-button>
    </form>

    <div class="mt-4 space-y-3">
        @if (auth()->user()->role === 'vendor')
            <a href="{{ route('vendor.dashboard') }}" class="block rounded-xl border border-djidji-outline bg-white p-4 font-medium text-djidji-text hover:border-djidji-green">
                Mon espace vendeur
            </a>
        @elseif (auth()->user()->role === 'courier')
            <a href="{{ route('courier.dashboard') }}" class="block rounded-xl border border-djidji-outline bg-white p-4 font-medium text-djidji-text hover:border-djidji-green">
                Mon espace livreur
            </a>
        @endif

        <a href="{{ route('my-orders') }}" class="block rounded-xl border border-djidji-outline bg-white p-4 font-medium text-djidji-text hover:border-djidji-green">
            Mes commandes
        </a>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-6">
        @csrf
        <button type="submit" class="w-full rounded-full border border-djidji-outline bg-white px-4 py-2.5 font-semibold text-djidji-text hover:border-djidji-error hover:text-djidji-error">
            Déconnexion
        </button>
    </form>
</div>
