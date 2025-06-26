// Service Worker PWA - No cache version
self.addEventListener('install', event => {
  self.skipWaiting();
});
self.addEventListener('activate', event => {
  self.clients.claim();
});

self.addEventListener('fetch', function(event) {
  event.respondWith(
    caches.match(event.request).then(function(response) {
      return response || fetch(event.request);
    })
  );
});
