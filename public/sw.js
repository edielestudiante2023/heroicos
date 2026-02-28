// Service Worker - Academia Heroicos PWA
const CACHE_VERSION = 'heroicos-v1';
const STATIC_CACHE = 'heroicos-static-v1';
const DYNAMIC_CACHE = 'heroicos-dynamic-v1';

// Assets to pre-cache on install
const PRE_CACHE_ASSETS = [
    '/',
    '/login',
    '/offline.html',
    '/assets/images/heroicos.png',
    '/manifest.json',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'
];

// Install event - pre-cache essential assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing Service Worker v1...');
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('[SW] Pre-caching assets');
                return cache.addAll(PRE_CACHE_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating Service Worker v1...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== STATIC_CACHE && name !== DYNAMIC_CACHE)
                    .map((name) => {
                        console.log('[SW] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - serve from cache with appropriate strategies
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip chrome-extension and other non-http(s) requests
    if (!url.protocol.startsWith('http')) return;

    // Strategy: Cache First for CDN assets (Bootstrap, icons fonts)
    if (isCDNAsset(url)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Strategy: Stale While Revalidate for local static assets (images, icons)
    if (isLocalAsset(url)) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    // Strategy: Network First for HTML pages
    if (request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Default: Network First
    event.respondWith(networkFirst(request));
});

// --- Cache Strategies ---

// Cache First: Check cache, fall back to network
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;

    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.log('[SW] Cache First fetch failed:', error);
        return new Response('Offline', { status: 503 });
    }
}

// Network First: Try network, fall back to cache, then offline page
async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) return cached;

        // If it's an HTML request and nothing in cache, show offline page
        if (request.headers.get('accept')?.includes('text/html')) {
            return caches.match('/offline.html');
        }

        return new Response('Offline', { status: 503 });
    }
}

// Stale While Revalidate: Return cache immediately, update in background
async function staleWhileRevalidate(request) {
    const cache = await caches.open(STATIC_CACHE);
    const cached = await cache.match(request);

    const fetchPromise = fetch(request).then((response) => {
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    }).catch(() => cached);

    return cached || fetchPromise;
}

// --- Helper Functions ---

function isCDNAsset(url) {
    return url.hostname === 'cdn.jsdelivr.net' ||
           url.hostname === 'fonts.googleapis.com' ||
           url.hostname === 'fonts.gstatic.com';
}

function isLocalAsset(url) {
    return url.pathname.startsWith('/assets/') ||
           url.pathname === '/favicon.ico' ||
           url.pathname === '/manifest.json';
}
