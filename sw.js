/* عامل الخدمة لتطبيق جامعة بنيان العالمية */
const V = 'uwciv-v2';
const CORE = ['/offline.html', '/assets/app/icon-192.png', '/assets/images/logo-light.png', '/assets/images/na-light.png'];
self.addEventListener('install', e => { e.waitUntil(caches.open(V).then(c => c.addAll(CORE)).then(() => self.skipWaiting())); });
self.addEventListener('activate', e => { e.waitUntil(caches.keys().then(ks => Promise.all(ks.filter(k => k !== V).map(k => caches.delete(k)))).then(() => self.clients.claim())); });
self.addEventListener('fetch', e => {
  const r = e.request;
  if (r.method !== 'GET' || new URL(r.url).origin !== location.origin) return;
  const u = new URL(r.url);
  if (/\/(admin|syslog|student\/admin|_private)\//.test(u.pathname)) return;
  if (r.mode === 'navigate') {
    // الصفحات: من الشبكة أولًا، وعند انقطاع الإنترنت تظهر صفحة بدون اتصال
    e.respondWith(fetch(r).catch(() => caches.match('/offline.html')));
    return;
  }
  if (/\.(css|js|png|jpe?g|webp|svg|woff2?|ttf)$/i.test(u.pathname)) {
    e.respondWith(caches.match(r).then(hit => {
      const net = fetch(r).then(res => { if (res.ok) { const cp = res.clone(); caches.open(V).then(c => c.put(r, cp)); } return res; }).catch(() => hit);
      return hit || net;
    }));
  }
});
