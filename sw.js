const CACHE_NAME = 'noric-v1';
const urlsToCache = [
  '/',
  '/index.php',
  'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    fetch(event.request).catch(() => caches.match(event.request))
  );
});