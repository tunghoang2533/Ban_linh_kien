/**
 * Service Worker - Ban Linh Kiện Push Notifications
 * 
 * Xử lý push notifications từ server và hiển thị thông báo trên trình duyệt
 * Cũng cache các tài nguyên tĩnh để cải thiện tốc độ
 */

const CACHE_NAME = 'banlinhkien-v1';
const STATIC_URLS = [
    '/',
    '/public/css/style.css',
    '/public/js/dungchung.js'
];

// Install: cache tài nguyên tĩnh
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(STATIC_URLS);
        })
    );
    self.skipWaiting();
});

// Activate: dọn dẹp cache cũ
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(
                keys.filter(key => key !== CACHE_NAME)
                    .map(key => caches.delete(key))
            );
        })
    );
    self.clients.claim();
});

// Fetch: cache-first cho static, network-first cho API
self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);
    
    // Chỉ cache GET requests
    if (event.request.method !== 'GET') return;
    
    // Bỏ qua các request API và admin
    if (url.pathname.includes('/admin/') || url.pathname.includes('ajax')) return;
    
    event.respondWith(
        caches.match(event.request).then(cached => {
            return cached || fetch(event.request).then(response => {
                // Cache ảnh và CSS
                if (url.pathname.match(/\.(css|js|png|jpg|jpeg|webp|svg|woff2?)$/)) {
                    const clone = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
                }
                return response;
            });
        })
    );
});

// Push Notification: nhận và hiển thị
self.addEventListener('push', event => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { title: 'Ban Linh Kiện', body: event.data ? event.data.text() : 'Có thông báo mới!' };
    }

    const title = data.title || 'Ban Linh Kiện';
    const options = {
        body: data.body || '',
        icon: data.icon || '/public/img/favicon.png',
        badge: '/public/img/badge.png',
        image: data.image || undefined,
        vibrate: [200, 100, 200],
        data: {
            url: data.url || '/',
            id: data.id || null
        },
        actions: data.actions || [
            { action: 'open', title: 'Xem ngay' },
            { action: 'close', title: 'Đóng' }
        ],
        tag: data.tag || 'default',
        renotify: true,
        requireInteraction: true
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Click notification
self.addEventListener('notificationclick', event => {
    event.notification.close();

    if (event.action === 'close') return;

    const url = event.notification.data?.url || '/';
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(clientList => {
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
