const CACHE_NAME = '{{ $cacheName }}';
const PRECACHE_URLS = {!! json_encode($precacheUrls, JSON_UNESCAPED_SLASHES) !!};
const OFFLINE_URL = '{{ $offlineUrl }}';
const STRATEGY = '{{ $strategy }}';

// ─── Install: Precache ───────────────────────────────────────────────────────
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                PRECACHE_URLS.map(url => cache.add(url).catch(() => null))
            );
        }).then(() => self.skipWaiting())
    );
});

// ─── Activate: Alte Caches entfernen ────────────────────────────────────────
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            );
        }).then(() => self.clients.claim())
    );
});

// ─── Fetch ───────────────────────────────────────────────────────────────────
self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;
    if (!event.request.url.startsWith(self.location.origin)) return;

    const isLivewire = event.request.url.includes('/livewire/');
    const isJson     = event.request.headers.get('Accept')?.includes('application/json');
    if (isLivewire || isJson) return;

    // Redirects nicht abfangen — Safari verträgt das nicht
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL))
        );
        return;
    }

    if (STRATEGY === 'cache-first') {
        event.respondWith(cacheFirst(event.request));
    } else if (STRATEGY === 'stale-while-revalidate') {
        event.respondWith(staleWhileRevalidate(event.request));
    } else {
        event.respondWith(networkFirst(event.request));
    }
});

// ─── Strategien ──────────────────────────────────────────────────────────────

async function networkFirst(request) {
    try {
        const response = await fetch(request);
        if (response && response.status === 200 && response.type !== 'opaqueredirect') {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        const cached = await caches.match(request);
        if (cached) return cached;
        if (OFFLINE_URL && request.destination === 'document') {
            return caches.match(OFFLINE_URL);
        }
    }
}

async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) return cached;
    try {
        const response = await fetch(request);
        if (response && response.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }
        return response;
    } catch {
        if (OFFLINE_URL && request.destination === 'document') {
            return caches.match(OFFLINE_URL);
        }
    }
}

async function staleWhileRevalidate(request) {
    const cache  = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);

    const fetchPromise = fetch(request).then((response) => {
        if (response && response.status === 200) {
            cache.put(request, response.clone());
        }
        return response;
    }).catch(() => null);

    return cached || fetchPromise;
}

@if(config('lara-base.pwa.push.enabled', false))
// ─── Push Notifications ───────────────────────────────────────────────────────
self.addEventListener('push', (event) => {
    const data = event.data?.json() ?? {};

    const title   = data.title ?? '{{ config('app.name') }}';
    const options = {
        body:    data.body    ?? '',
        icon:    data.icon    ?? '/icons-pwa/icon-192x192.png',
        badge:   data.badge   ?? '/icons-pwa/icon-192x192.png',
        tag:     data.tag     ?? 'default',
        data:    { url: data.url ?? '/' },
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url ?? '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
@endif