<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#204E29">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'DjidjiMarket' }}</title>

        <link rel="manifest" href="/manifest.json">
        <link rel="icon" href="/images/DjidjiMarket-icone-seule.png">
        <link rel="apple-touch-icon" href="/images/DjidjiMarket-icone-seule.png">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen overflow-x-hidden bg-djidji-bg font-body text-djidji-text antialiased">
        <header class="sticky top-0 z-10 border-b border-djidji-outline bg-white">
            <div class="mx-auto flex max-w-[1280px] items-center justify-between px-4 py-3 md:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <img src="/images/DjidjiMarket-icone-seule.png" alt="DjidjiMarket" class="h-8 w-8">
                    <span class="font-sans text-lg font-bold text-djidji-green">djidji<span class="text-djidji-orange">market</span></span>
                </a>

                <nav class="flex items-center gap-4 text-sm font-medium">
                    <a href="{{ route('cart.show') }}" class="relative text-djidji-text hover:text-djidji-green">
                        Panier
                        @if (app(\App\Services\CartService::class)->count() > 0)
                            <span class="absolute -right-3 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-djidji-orange text-[10px] font-bold text-white">
                                {{ app(\App\Services\CartService::class)->count() }}
                            </span>
                        @endif
                    </a>

                    @auth
                        @if (auth()->user()->role === 'vendor')
                            <a href="{{ route('vendor.dashboard') }}" class="text-djidji-text/70 hover:text-djidji-green">Mon espace vendeur</a>
                        @elseif (auth()->user()->role === 'courier')
                            <a href="{{ route('courier.dashboard') }}" class="text-djidji-text/70 hover:text-djidji-green">Mon espace livreur</a>
                        @endif
                        <span class="text-djidji-text/70">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-djidji-text/70 hover:text-djidji-green">Déconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full bg-djidji-green px-4 py-1.5 text-white hover:bg-djidji-green-dark">
                            Se connecter
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-[1280px] px-4 py-8 md:px-8 {{ ($showBottomNav ?? false) ? 'pb-24 md:pb-8' : '' }}">
            {{ $slot }}
        </main>

        <footer class="w-full bg-djidji-green text-white">
            <div class="mx-auto grid max-w-[1280px] grid-cols-1 gap-6 px-4 py-16 md:grid-cols-2 md:px-8">
                <div class="space-y-6">
                    <span class="font-sans text-lg font-bold">DjidjiMarket</span>
                    <p class="text-sm text-white/70">La première plateforme de marché local en Côte d'Ivoire, connectant vendeurs et acheteurs en toute sécurité.</p>
                    <div class="flex flex-wrap gap-4">
                        <span class="rounded bg-white/10 px-2 py-1 text-xs font-medium text-white/80">Orange Money</span>
                        <span class="rounded bg-white/10 px-2 py-1 text-xs font-medium text-white/80">MTN Money</span>
                        <span class="rounded bg-white/10 px-2 py-1 text-xs font-medium text-white/80">Moov Money</span>
                        <span class="rounded bg-white/10 px-2 py-1 text-xs font-medium text-white/80">Wave</span>
                    </div>
                </div>
                <div>
                    <h4 class="mb-6 text-sm font-semibold uppercase tracking-wide text-white/50">Navigation</h4>
                    <ul class="space-y-2 text-sm text-white/80">
                        <li><a href="{{ route('home') }}" class="hover:text-white">Accueil</a></li>
                        @guest
                            <li><a href="{{ route('login') }}" class="hover:text-white">Devenir vendeur</a></li>
                            <li><a href="{{ route('login') }}" class="hover:text-white">Connexion</a></li>
                        @else
                            <li><a href="{{ route('profile') }}" class="hover:text-white">Mon profil</a></li>
                        @endguest
                    </ul>
                </div>
            </div>
            <div class="mx-auto max-w-[1280px] border-t border-white/10 px-4 py-4 text-center text-xs text-white/50 md:px-8">
                © {{ date('Y') }} DjidjiMarket. Tous droits réservés.
            </div>
        </footer>

        @if ($showBottomNav ?? false)
            <x-bottom-nav />
        @endif

        @livewireScripts
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
            }
        </script>
    </body>
</html>
