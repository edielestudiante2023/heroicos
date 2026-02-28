// Service Worker - Academia Heroicos PWA v4 (Offline Sync Support)
const CACHE_VERSION = 'heroicos-v4';
const STATIC_CACHE = 'heroicos-static-v4';

// Assets to pre-cache on install
const PRE_CACHE_ASSETS = [
    '/offline.html',
    '/assets/images/heroicos.png',
    '/manifest.json',
    '/assets/js/heroicos-db.js',
    '/assets/js/heroicos-sync.js',
    '/assets/js/heroicos-offline.js',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'
];

// API endpoints that support offline POST
const SYNCABLE_ENDPOINTS = [
    '/api/offline/attendance',
    '/api/offline/payments'
];

// Install event - pre-cache essential assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing Service Worker v4...');
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('[SW] Pre-caching assets');
                return cache.addAll(PRE_CACHE_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean ALL old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating Service Worker v4...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== STATIC_CACHE)
                    .map((name) => {
                        console.log('[SW] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // --- Offline POST interception for sync API endpoints ---
    if (request.method === 'POST' && isSyncableEndpoint(url.pathname)) {
        event.respondWith(handleOfflinePost(request));
        return;
    }

    // Skip non-GET requests (all other POSTs pass through normally)
    if (request.method !== 'GET') return;

    // Skip non-http(s) requests
    if (!url.protocol.startsWith('http')) return;

    // NAVIGATION REQUESTS (HTML pages): Always go to network, never cache.
    // Only show offline page if network is completely down.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match('/offline.html'))
        );
        return;
    }

    // CDN assets: Cache First
    if (isCDNAsset(url)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Local static assets: Stale While Revalidate
    if (isLocalAsset(url)) {
        event.respondWith(staleWhileRevalidate(request));
        return;
    }

    // Everything else: Network only (no caching)
    return;
});

// --- Background Sync ---
self.addEventListener('sync', (event) => {
    if (event.tag === 'heroicos-sync') {
        event.waitUntil(notifyClientsToSync());
    }
});

// --- Message listener (from client) ---
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    if (event.data && event.data.type === 'REGISTER_SYNC') {
        if (self.registration.sync) {
            self.registration.sync.register('heroicos-sync').catch(() => {});
        }
    }
});

// --- Offline POST handler ---
async function handleOfflinePost(request) {
    try {
        const response = await fetch(request.clone());
        return response;
    } catch (error) {
        // Network failed - signal client to queue locally
        return new Response(JSON.stringify({
            offline: true,
            message: 'Guardado localmente. Se sincronizara cuando haya conexion.'
        }), {
            status: 200,
            headers: {
                'Content-Type': 'application/json',
                'X-Offline-Queued': 'true'
            }
        });
    }
}

function isSyncableEndpoint(pathname) {
    return SYNCABLE_ENDPOINTS.some((ep) => pathname.includes(ep));
}

async function notifyClientsToSync() {
    const clients = await self.clients.matchAll({ type: 'window' });
    clients.forEach((client) => {
        client.postMessage({ type: 'SYNC_REQUESTED' });
    });
}

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
