// Minimal service worker — just enough for PWA installability (Phase 1).
// Caches the static app shell (logo, manifest); everything else (pages, API)
// goes straight to the network since content changes too often to cache.
const CACHE_NAME = 'djidjimarket-shell-v1';
const SHELL_ASSETS = [
    '/manifest.json',
    '/images/DjidjiMarket-icone-seule.png',
    '/images/DjidjiMarket-PNG-transparent.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(SHELL_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    const isShellAsset = SHELL_ASSETS.some((asset) => url.pathname === asset);

    if (!isShellAsset) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => cached || fetch(event.request))
    );
});
