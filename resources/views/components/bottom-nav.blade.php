@php
    $cartCount = app(\App\Services\CartService::class)->count();
    $ordersRoute = auth()->check() ? route('my-orders') : route('login');
    $profileRoute = auth()->check() ? route('profile') : route('login');

    $item = function (string $active, string $label) {
        return $active
            ? 'flex flex-1 flex-col items-center gap-0.5 rounded-xl bg-djidji-green/10 py-1.5 text-djidji-green'
            : 'flex flex-1 flex-col items-center gap-0.5 py-1.5 text-djidji-text/50';
    };
@endphp

<nav class="fixed inset-x-0 bottom-0 z-40 flex items-center justify-around border-t border-djidji-outline bg-white px-2 pb-[env(safe-area-inset-bottom)] pt-1 md:hidden">
    <a href="{{ route('home') }}" class="{{ $item(request()->routeIs('home'), 'Accueil') }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
            <path d="M11.47 3.84a.75.75 0 0 1 1.06 0l8.69 8.69a.75.75 0 1 1-1.06 1.06l-.97-.97V19.5a2.25 2.25 0 0 1-2.25 2.25h-3a.75.75 0 0 1-.75-.75v-4.5a.75.75 0 0 0-.75-.75h-3a.75.75 0 0 0-.75.75V21a.75.75 0 0 1-.75.75h-3A2.25 2.25 0 0 1 3.75 19.5v-6.88l-.97.97a.75.75 0 1 1-1.06-1.06z"/>
        </svg>
        <span class="text-[11px] font-medium">Accueil</span>
    </a>

    <a href="{{ route('cart.show') }}" class="{{ $item(request()->routeIs('cart.show'), 'Panier') }} relative">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
            <path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.764.724-1.865 1.679l-1.263 12A1.875 1.875 0 0 0 4.25 22.5h15.5a1.875 1.875 0 0 0 1.865-2.071l-1.263-12a1.875 1.875 0 0 0-1.865-1.679H16.5V6a4.5 4.5 0 1 0-9 0M12 3a3 3 0 0 0-3 3v.75h6V6a3 3 0 0 0-3-3m-3 8.25a3 3 0 1 0 6 0v-.75a.75.75 0 0 1 1.5 0v.75a4.5 4.5 0 1 1-9 0v-.75a.75.75 0 0 1 1.5 0z" clip-rule="evenodd"/>
        </svg>
        @if ($cartCount > 0)
            <span class="absolute right-2 top-0 flex h-4 w-4 items-center justify-center rounded-full bg-djidji-orange text-[9px] font-bold text-white">{{ $cartCount }}</span>
        @endif
        <span class="text-[11px] font-medium">Panier</span>
    </a>

    <a href="{{ $ordersRoute }}" class="{{ $item(request()->routeIs('my-orders'), 'Commandes') }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
            <path fill-rule="evenodd" d="M6 3a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3zm2.5 4.5a.75.75 0 0 0 0 1.5h7a.75.75 0 0 0 0-1.5zm0 4a.75.75 0 0 0 0 1.5h7a.75.75 0 0 0 0-1.5zm0 4a.75.75 0 0 0 0 1.5h4a.75.75 0 0 0 0-1.5z" clip-rule="evenodd"/>
        </svg>
        <span class="text-[11px] font-medium">Commandes</span>
    </a>

    <a href="{{ $profileRoute }}" class="{{ $item(request()->routeIs('profile'), 'Profil') }}">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
            <path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0 0 21.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 0 0 3.065 7.097A9.716 9.716 0 0 0 12 21.75a9.716 9.716 0 0 0 6.685-2.653m-12.54-1.285A7.486 7.486 0 0 1 12 15a7.486 7.486 0 0 1 5.855 2.812A8.224 8.224 0 0 1 12 20.25a8.224 8.224 0 0 1-5.855-2.438M15.75 9a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0" clip-rule="evenodd"/>
        </svg>
        <span class="text-[11px] font-medium">Profil</span>
    </a>
</nav>
