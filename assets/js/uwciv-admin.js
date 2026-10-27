/* مركز الإدارة — سلوك مشترك */
(function () {
  var $ = function (s, r) { return (r || document).querySelector(s); }, $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

  /* القائمة على الجوال */
  var ad = $('#ad'), b = $('#adb');
  if (b) b.addEventListener('click', function () { ad.classList.toggle('open'); });
  $$('[data-close]').forEach(function (x) { x.addEventListener('click', function () { ad.classList.remove('open'); }); });

  /* طيّ مجموعات القائمة الجانبية وحفظ حالتها */
  var st = {}; try { st = JSON.parse(localStorage.getItem('adGroups') || '{}'); } catch (e) {}
  $$('.ad-g').forEach(function (g) {
    var id = g.getAttribute('data-g'); if (g.querySelector('a.n.on')) g.classList.add('open'); else if (id in st) g.classList.toggle('open', !!st[id]);
    g.querySelector('.ad-gh').addEventListener('click', function () { if (Date.now() - (g._hv || 0) < 900) return; g.classList.toggle('open'); st[id] = g.classList.contains('open'); try { localStorage.setItem('adGroups', JSON.stringify(st)); } catch (e) {} });
    /* فتح بمجرد المرور بالمؤشر (أجهزة الفأرة) */
    var t; g.addEventListener('mouseenter', function () { if (!matchMedia('(hover:hover)').matches) return; clearTimeout(t); t = setTimeout(function () { if (!g.classList.contains('open')) { g._hv = Date.now(); g.classList.add('open'); st[id] = true; try { localStorage.setItem('adGroups', JSON.stringify(st)); } catch (e) {} } }, 90); });
    g.addEventListener('mouseleave', function () { clearTimeout(t); });
  });
  /* ما يُفتح بالمرور يبقى مفتوحًا — يُطوى فقط بالضغط على عنوانه */

  /* إظهار كلمة المرور */
  $$('.ad-eye,.eye').forEach(function (e) { e.addEventListener('click', function () { var i = e.parentNode.querySelector('input'); var s = i.type === 'password'; i.type = s ? 'text' : 'password'; e.innerHTML = s ? '<i class="far fa-eye-slash"></i>' : '<i class="far fa-eye"></i>'; i.focus(); }); });
  var aup = $('#aup'), caps = $('#aucaps');
  if (aup && caps) ['keydown', 'keyup'].forEach(function (ev) { aup.addEventListener(ev, function (e) { caps.classList.toggle('on', !!(e.getModifierState && e.getModifierState('CapsLock'))); }); });
  var auf = $('.au-form form'); if (auf) { var u = auf.querySelector('[name=u]'); if (u && !u.value) u.focus(); else if (aup) aup.focus(); auf.addEventListener('submit', function () { var g = auf.querySelector('.au-go'); if (g) { g.style.opacity = '.75'; g.querySelector('span').textContent = 'جارٍ التحقق…'; } }); }

  /* الإشعارات */
  $$('[data-toast]').forEach(function (t) { setTimeout(function () { t.classList.add('in'); }, 60); setTimeout(function () { t.classList.remove('in'); }, 4800); });
  window.uwcToast = function (m, bad) { var t = document.createElement('div'); t.className = 'ad-toast ' + (bad ? 'bad' : 'ok'); t.innerHTML = '<i class="fas ' + (bad ? 'fa-exclamation-circle' : 'fa-check-circle') + '"></i><span></span>'; t.querySelector('span').textContent = m; document.body.appendChild(t); setTimeout(function () { t.classList.add('in'); }, 30); setTimeout(function () { t.classList.remove('in'); setTimeout(function () { t.remove(); }, 400); }, 3600); };
  /* إزالة ?ok= من الرابط بعد العرض */
  try { if (/[?&](ok|err)=/.test(location.search)) { var q = location.search.replace(/([?&])(ok|err)=[^&]*&?/g, '$1').replace(/[?&]$/, ''); history.replaceState(null, '', location.pathname + q + location.hash); } } catch (e) {}

  /* تأكيد الحذف */
  document.addEventListener('submit', function (e) { var f = e.target, m = f.getAttribute('data-confirm') || (e.submitter && e.submitter.getAttribute('data-confirm')); if (m && !confirm(m)) e.preventDefault(); }, true);
  document.addEventListener('click', function (e) { var a = e.target.closest('a[data-confirm]'); if (a && !confirm(a.getAttribute('data-confirm'))) e.preventDefault(); });

  /* النوافذ */
  document.addEventListener('click', function (e) {
    var o = e.target.closest('[data-open]'); if (o) { var d = document.getElementById(o.getAttribute('data-open')); if (d && d.showModal) { e.preventDefault(); d.showModal(); var f = d.querySelector('input:not([type=hidden]),textarea,select'); if (f) setTimeout(function () { f.focus(); }, 50); } }
    var c = e.target.closest('[data-dlg-close]'); if (c) { var dd = c.closest('dialog'); if (dd) dd.close(); }
  });
  $$('dialog.ad-dlg').forEach(function (d) { d.addEventListener('click', function (e) { if (e.target === d) d.close(); }); });

  /* منطقة الإفلات للملفات + معاينة */
  $$('.ad-drop').forEach(function (z) {
    var i = z.querySelector('input[type=file]'), lbl = z.querySelector('b'), img = z.querySelector('img');
    ['dragenter', 'dragover'].forEach(function (ev) { z.addEventListener(ev, function () { z.classList.add('dr'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { z.addEventListener(ev, function () { z.classList.remove('dr'); }); });
    if (i) i.addEventListener('change', function () { var f = i.files[0]; if (!f) return; lbl.textContent = f.name + ' — ' + (f.size / 1048576).toFixed(2) + ' MB'; if (img && /^image\//.test(f.type)) { img.src = URL.createObjectURL(f); img.hidden = false; } });
  });

  /* بحث وتصفية فوري: data-filter="#list" وعناصر .fi تحمل data-s و data-g */
  $$('[data-filter]').forEach(function (inp) {
    var box = $(inp.getAttribute('data-filter')); if (!box) return;
    var chips = $$('[data-chip]', inp.closest('.ad-bar') || document), cur = 'all', cnt = $(inp.getAttribute('data-count') || '#nothing');
    function run() { var q = inp.value.trim().toLowerCase(), n = 0; $$('.fi', box).forEach(function (el) { var ok = (!q || (el.getAttribute('data-s') || el.textContent).toLowerCase().indexOf(q) > -1) && (cur === 'all' || (' ' + (el.getAttribute('data-g') || '') + ' ').indexOf(' ' + cur + ' ') > -1); el.style.display = ok ? '' : 'none'; if (ok) n++; }); if (cnt) cnt.textContent = n; var em = $('.fi-empty', box.parentNode); if (em) em.hidden = n > 0; }
    inp.addEventListener('input', run);
    chips.forEach(function (c) { c.addEventListener('click', function () { chips.forEach(function (x) { x.classList.remove('on'); }); c.classList.add('on'); cur = c.getAttribute('data-chip'); run(); }); });
  });

  /* الانتقال السريع Ctrl+K */
  var pal = $('#adpal'), pq = $('#adpq'), pl = $('#adpl'), sel = 0, list = [];
  function render() {
    var q = (pq.value || '').trim().toLowerCase(); list = (window.UWCIV_NAV || []).filter(function (x) { return !q || (x.t + ' ' + x.g).toLowerCase().indexOf(q) > -1; }); sel = Math.min(sel, Math.max(0, list.length - 1));
    pl.innerHTML = list.map(function (x, i) { return '<li class="' + (i === sel ? 'on' : '') + '"><a href="' + x.u + '"><i class="' + x.i + '"></i><span></span><small></small></a></li>'; }).join('') || '<li style="padding:16px;color:#7a8c80;text-align:center">لا نتائج</li>';
    $$('li', pl).forEach(function (li, i) { if (!list[i]) return; li.querySelector('span').textContent = list[i].t; li.querySelector('small').textContent = list[i].g; });
  }
  function openPal() { if (!pal) return; pal.hidden = false; pq.value = ''; sel = 0; render(); setTimeout(function () { pq.focus(); }, 20); }
  function closePal() { if (pal) pal.hidden = true; }
  $$('[data-palette]').forEach(function (x) { x.addEventListener('click', openPal); });
  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) { e.preventDefault(); pal && pal.hidden ? openPal() : closePal(); }
    if (!pal || pal.hidden) return;
    if (e.key === 'Escape') closePal();
    if (e.key === 'ArrowDown') { sel = Math.min(sel + 1, list.length - 1); render(); e.preventDefault(); }
    if (e.key === 'ArrowUp') { sel = Math.max(sel - 1, 0); render(); e.preventDefault(); }
    if (e.key === 'Enter' && list[sel]) { location.href = list[sel].u; closePal(); }
  });
  if (pq) pq.addEventListener('input', function () { sel = 0; render(); });
  if (pal) pal.addEventListener('click', function (e) { if (e.target === pal) closePal(); });
})();
