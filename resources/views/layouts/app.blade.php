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
    <body class="min-h-screen bg-djidji-bg font-body text-djidji-text antialiased">
        <header class="sticky top-0 z-10 border-b border-black/5 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
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
                        <span class="text-djidji-text/70">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-djidji-text/70 hover:text-djidji-green">Déconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-djidji-text/70 hover:text-djidji-green">Connexion</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-djidji-green px-4 py-1.5 text-white hover:bg-djidji-green-dark">
                            Créer un compte
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8">
            {{ $slot }}
        </main>

        <footer class="mx-auto max-w-5xl px-4 py-8 text-center text-sm text-djidji-text/50">
            DjidjiMarket — le vrai marché, en toute confiance.
        </footer>

        @livewireScripts
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
            }
        </script>
    </body>
</html>
