/* ══════════════════════════════════════════════
   TSC Service Worker — Offline Fallback
   Versi: 3.0 — Always show offline.html when offline
   ══════════════════════════════════════════════ */

const CACHE_NAME = 'tsc-cache-v3';
const BASE = '/app-inventori';
const OFFLINE_URL = BASE + '/offline.html';

// Asset yang di-cache saat install
const PRECACHE_ASSETS = [
    OFFLINE_URL,
    BASE + '/assets/img/TSC_page-0001.png',
    BASE + '/assets/img/wp-1.png',
];

/* ── INSTALL ── */
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            // Wajib cache offline.html dulu
            return cache.add(OFFLINE_URL).then(() => {
                return Promise.allSettled(
                    PRECACHE_ASSETS.map(url => cache.add(url).catch(() => {}))
                );
            });
        }).then(() => self.skipWaiting())
    );
});

/* ── ACTIVATE: hapus cache lama ── */
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            )
        ).then(() => self.clients.claim())
    );
});

/* ── FETCH ── */
self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // Skip third-party requests
    if (url.origin !== self.location.origin) return;

    const isHTMLRequest = event.request.destination === 'document' ||
        event.request.headers.get('accept')?.includes('text/html');

    if (isHTMLRequest) {
        // Untuk semua request halaman HTML:
        // Coba network → kalau gagal → tampilkan offline.html
        event.respondWith(
            fetch(event.request)
                .then(response => {
                    // Simpan ke cache kalau berhasil
                    if (response && response.status === 200) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    }
                    return response;
                })
                .catch(() => {
                    // Network gagal → tampilkan offline.html dari cache
                    return caches.match(OFFLINE_URL);
                })
        );
        return;
    }

    // Untuk asset non-HTML (CSS, JS, gambar, dll):
    // Cache first → network fallback
    event.respondWith(
        caches.match(event.request).then(cached => {
            if (cached) return cached;
            return fetch(event.request)
                .then(response => {
                    if (response && response.status === 200) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                    }
                    return response;
                })
                .catch(() => new Response('', { status: 503 }));
        })
    );
});