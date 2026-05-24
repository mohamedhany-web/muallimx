const CACHE_NAME = 'muallimx-shell-v3';
const APP_SHELL = ['/manifest.webmanifest', '/icons/icon-192.png', '/icons/icon-512.png'];

function isAppShellAsset(pathname) {
  return APP_SHELL.includes(pathname) || pathname.startsWith('/icons/');
}

function wantsHtml(request) {
  if (request.mode === 'navigate') {
    return true;
  }
  var accept = request.headers.get('accept') || '';

  return accept.indexOf('text/html') !== -1;
}

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys
          .filter((key) => key !== CACHE_NAME)
          .map((key) => caches.delete(key))
      )
    ).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  if (event.request.method !== 'GET') {
    return;
  }

  var url = new URL(event.request.url);

  if (url.origin !== self.location.origin) {
    return;
  }

  // صفحات Laravel: دائماً من الشبكة (لا نخزّن HTML/API في الكاش)
  if (wantsHtml(event.request) || url.pathname.startsWith('/admin') || url.pathname.startsWith('/employee') || url.pathname.startsWith('/student') || url.pathname.startsWith('/instructor')) {
    event.respondWith(
      fetch(event.request).catch(function () {
        return caches.match('/manifest.webmanifest');
      })
    );

    return;
  }

  // أيقونات PWA فقط: كاش ثم شبكة
  if (isAppShellAsset(url.pathname)) {
    event.respondWith(
      caches.match(event.request).then(function (cached) {
        return cached || fetch(event.request);
      })
    );

    return;
  }

  // باقي الملفات: شبكة فقط بدون تخزين (تجنّب JS/CSS/HTML قديمة)
});
