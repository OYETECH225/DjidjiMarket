<div>
    <h1 class="mb-6 font-sans text-2xl font-bold text-djidji-green">Profil</h1>

    <div class="rounded-xl border border-djidji-outline bg-white p-4">
        <p class="font-semibold text-djidji-text">{{ auth()->user()->name }}</p>
        <p class="text-sm text-djidji-text/60">{{ auth()->user()->phone }}</p>
        <span class="mt-2 inline-block rounded-full bg-djidji-green/10 px-3 py-1 text-xs font-medium text-djidji-green">
            {{ $roleLabel }}
        </span>
    </div>

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
