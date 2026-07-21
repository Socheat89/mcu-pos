// Mekong POS - Service Worker v3
// Caches core assets for offline / faster repeat loads

const CACHE_NAME = 'mekong-pos-v3';
const ASSETS = [
  '/public/dist/assets/index.css',
  '/public/dist/assets/index.js',
  '/public/manifest.json',
  '/public/images/logo-192.png',
  '/public/images/logo-512.png',
  '/public/images/no-image.svg',
];

// Install - cache core assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS).catch(() => {});
    })
  );
  self.skipWaiting();
});

// Activate - clean old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      );
    })
  );
  self.clients.claim();
});

// Fetch - network first for HTML/API, cache first for assets
self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);

  // Skip non-same-origin and API calls
  if (url.origin !== self.location.origin) return;
  if (url.pathname.includes('/api/') || url.pathname.includes('bakong')) return;

  // Cache-first for static assets (JS, CSS, images)
  if (url.pathname.match(/\.(js|css|png|svg|jpg|jpeg|webp|woff2?)$/)) {
    event.respondWith(
      caches.match(event.request).then((cached) => {
        return cached || fetch(event.request).then((response) => {
          if (response.status === 200) {
            const clone = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
          }
          return response;
        });
      })
    );
    return;
  }

  // Network-first for HTML pages (always fresh)
  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request))
  );
});
