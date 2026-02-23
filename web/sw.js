/**
 * PWA Service Worker - ERP Hospital
 * Cache static assets; network-first for page navigations.
 */

const CACHE_NAME = 'erp-pwa-v2';
const STATIC_URLS = [
  '/',
  '/site',
  '/images/logo_new.png',
  '/css/custom.css',
  '/css/bootstrap-icons.min.css',
  '/js/erp.js',
  '/libs/font-awesome/fontawesome-free-7.1.0-web/css/all.min.css'
];

// Install: precache critical static URLs
self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE_NAME).then(function (cache) {
      return cache.addAll(STATIC_URLS).catch(function () {
        // Ignore fail for optional URLs (e.g. 404)
        return Promise.resolve();
      });
    }).then(function () {
      return self.skipWaiting();
    })
  );
});

// Activate: take control and remove old caches
self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(
        keys.filter(function (key) { return key !== CACHE_NAME; }).map(function (key) {
          return caches.delete(key);
        })
      );
    }).then(function () {
      return self.clients.claim();
    })
  );
});

// Fetch: network first for same-origin HTML; cache first for static
self.addEventListener('fetch', function (event) {
  var url = new URL(event.request.url);
  if (event.request.method !== 'GET') return;

  // Same-origin only
  if (url.origin !== self.location.origin) return;

  // API / dynamic: network only (no cache)
  if (url.pathname.indexOf('/api/') === 0 || url.search.indexOf('_=') !== -1) {
    return;
  }

  // HTML pages: network first, fallback to cache
  var isNav = event.request.mode === 'navigate' || (event.request.headers.get('accept') || '').indexOf('text/html') !== -1;
  if (isNav) {
    event.respondWith(
      fetch(event.request).then(function (res) {
        var clone = res.clone();
        caches.open(CACHE_NAME).then(function (cache) {
          cache.put(event.request, clone);
        });
        return res;
      }).catch(function () {
        return caches.match(event.request).then(function (cached) {
          return cached || caches.match(self.location.origin + '/site').then(function (fallback) {
            return fallback || new Response('Offline', { status: 503, statusText: 'Offline' });
          });
        });
      })
    );
    return;
  }

  // Static assets: cache first, then network
  if (/\.(css|js|woff2?|png|jpg|jpeg|gif|ico|svg)(\?.*)?$/i.test(url.pathname)) {
    event.respondWith(
      caches.match(event.request).then(function (cached) {
        return cached || fetch(event.request).then(function (res) {
          if (res && res.status === 200 && res.type === 'basic') {
            var clone = res.clone();
            caches.open(CACHE_NAME).then(function (cache) {
              cache.put(event.request, clone);
            });
          }
          return res;
        });
      })
    );
  }
});

// Push: แสดงการแจ้งเตือนเมื่อได้รับ push (สำหรับทดสอบ + Phase 2 Web Push)
self.addEventListener('push', function (event) {
  var data = { title: 'ERP', body: 'มีการแจ้งเตือนใหม่', url: '/' };
  if (event.data) {
    try {
      var json = event.data.json();
      if (json.title) data.title = json.title;
      if (json.body) data.body = json.body;
      if (json.url) data.url = json.url;
    } catch (e) {}
  }
  event.waitUntil(
    self.registration.showNotification(data.title, {
      body: data.body,
      icon: '/images/logo_new.png',
      badge: '/images/logo_new.png',
      tag: 'erp-notify',
      data: { url: data.url },
      requireInteraction: false
    })
  );
});

// กดการแจ้งเตือน → เปิดหน้า (ใช้ URL แบบ HTTPS เสมอ เพื่อไม่ให้เกิด Mixed Content)
self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  var path = (event.notification.data && event.notification.data.url) || '/site';
  var fullUrl = path.startsWith('http') ? path : (self.location.origin + (path.startsWith('/') ? path : '/' + path));
  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
      for (var i = 0; i < clientList.length; i++) {
        if (clientList[i].url.indexOf(self.location.origin) === 0 && 'focus' in clientList[i]) {
          clientList[i].navigate(fullUrl);
          return clientList[i].focus();
        }
      }
      if (self.clients.openWindow) return self.clients.openWindow(fullUrl);
    })
  );
});
