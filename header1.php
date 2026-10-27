<?php
require_once __DIR__ . '/inc/i18n.php';
$__L = $UWCIV_LANGS[$UWCIV_LANG];
$__cur = basename($_SERVER['SCRIPT_NAME'] ?? '') ?: 'index.php';
$__stage = uwciv_stage($__cur);
// حالة تسجيل الدخول (لإظهار «حسابي / خروج» بدل «تسجيل الدخول»)
$__user = '';
if (isset($_COOKIE[session_name()])) {
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    if (!empty($_SESSION['uwciv_super'])) $__user = 'المشرف العام';
    elseif (!empty($_SESSION['SESSION_EMAIL'])) $__user = (string) $_SESSION['SESSION_EMAIL'];
}
$__stages = ['dip' => ['diploma', '/depl.php', 'fa-seedling'], 'bac' => ['bachelor', '/depm.php', 'fa-book-open'], 'mas' => ['master', '/depmst.php', 'fa-feather-alt'], 'phd' => ['phd', '/depdec.php', 'fa-crown']];

function uwciv_render_sub($items, $depth = 1)
{
    $h = '<ul class="u-sub u-sub-' . $depth . '">';
    foreach ($items as $it) {
        $has = !empty($it[2]);
        $h .= '<li class="' . ($has ? 'u-has' : '') . '"><a href="' . $it[1] . '"><span>' . htmlspecialchars(t($it[0])) . '</span><i class="fas fa-chevron-' . ($has ? 'down' : 'left') . ' u-chev"></i></a>';
        if ($has) $h .= uwciv_render_sub($it[2], $depth + 1);
        $h .= '</li>';
    }
    return $h . '</ul>';
}
?>
<?php require_once __DIR__ . '/inc/settings.php'; require_once __DIR__ . '/inc/mega.php'; require_once __DIR__ . '/inc/ticker.php'; ?>
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#0b3119">
<link rel="apple-touch-icon" href="/assets/app/apple-touch-icon.png">
<script>(function(){if(!document.querySelector("link[rel~=icon]")){var l=document.createElement("link");l.rel="icon";l.href="/assets/app/favicon-48.png";document.head.appendChild(l);}})();</script>
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="جامعة بنيان">
<script>
(function(){
  if(!document.querySelector('head link[rel=manifest]')){var l=document.createElement('link');l.rel='manifest';l.href='/manifest.webmanifest';document.head.appendChild(l);}
  if('serviceWorker' in navigator){window.addEventListener('load',function(){navigator.serviceWorker.register('/sw.js').catch(function(){});});}
  var dp=null;
  window.addEventListener('beforeinstallprompt',function(e){e.preventDefault();dp=e;window.uwcivInstall=function(){dp.prompt();dp.userChoice.finally(function(){dp=null;var b=document.getElementById('uwApp');if(b)b.remove();});};
    var seen=false;try{seen=localStorage.getItem('uwAppDismiss')==='1';}catch(x){}
    if(seen||location.pathname==='/app'||location.pathname==='/app.php'||window.innerWidth>900)return;
    var b=document.createElement('div');b.id='uwApp';b.innerHTML='<img src="/assets/app/icon-96.png" alt=""><span><b>تطبيق جامعة بنيان</b><small>ثبّته على جوالك مجانًا</small></span><button type="button" class="go">تثبيت</button><button type="button" class="x" aria-label="إغلاق">×</button>';
    document.addEventListener('DOMContentLoaded',function(){document.body.appendChild(b);});if(document.body)document.body.appendChild(b);
    b.querySelector('.go').onclick=function(){window.uwcivInstall();};
    b.querySelector('.x').onclick=function(){b.remove();try{localStorage.setItem('uwAppDismiss','1');}catch(x){}};
  });
})();
</script>
<style>#uwApp{position:fixed;bottom:12px;right:12px;left:12px;z-index:9998;display:flex;align-items:center;gap:10px;background:#fff;border-radius:18px;padding:10px 12px;box-shadow:0 20px 40px -14px rgba(6,33,15,.55);font-family:inherit}#uwApp img{width:44px;height:44px;border-radius:12px}#uwApp b{display:block;font-size:14px;color:#0b3119}#uwApp small{font-size:12px;color:#5b6f62}#uwApp .go{margin-inline-start:auto;border:0;border-radius:20px;background:#1f7a45;color:#fff;font-weight:800;padding:8px 16px}#uwApp .x{border:0;background:none;font-size:22px;color:#8a9a90;padding:0 4px}</style>
<link rel="stylesheet" href="/assets/css/uwciv-theme.css?v=22">
<link rel="stylesheet" href="/assets/css/uwciv-mega.css?v=7">
<div class="main-wrapper">
<header class="u-header notranslate" data-lang="<?= $UWCIV_LANG ?>">
  <!-- شريط الخدمات الصغير -->
  <?php $__ct = uwciv_contact(); ?>
  <div class="u-util">
    <div class="u-wrap u-util-row">
      <div class="u-util-links">
        <a href="https://wa.me/<?= $__ct['digits'] ?>" target="_blank" rel="noopener" dir="ltr"><i class="fab fa-whatsapp"></i><?= $__ct['pretty'] ?></a>
        <a href="mailto:info@biuedu.com" class="u-hide-sm" dir="ltr"><i class="far fa-envelope"></i>info@biuedu.com</a>
        <a href="/form2.php" class="u-hide-sm"><i class="fas fa-headset"></i><?= t('support') ?></a>
        <a href="/calendar.php" class="u-hide-sm"><i class="far fa-calendar-alt"></i><?= t('calendar') ?></a>
        <a href="/app"><i class="fas fa-mobile-alt"></i>تطبيق الجوال</a>
      </div>
      <div class="u-util-end">
        <form class="u-search" action="/search.php" method="get" role="search">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="<?= htmlspecialchars(t('search_ph')) ?>" aria-label="<?= htmlspecialchars(t('search_ph')) ?>">
        </form>
        <div class="u-lang">
          <button type="button" class="u-lang-btn" aria-haspopup="true" aria-label="<?= t('language') ?>">
            <i class="fas fa-globe"></i><b><?= $__L['short'] ?></b>
          </button>
          <ul class="u-lang-menu">
            <?php foreach ($UWCIV_LANGS as $code => $L): ?>
              <li><a href="<?= lang_url($code) ?>" data-lang="<?= $code ?>" class="<?= $code === $UWCIV_LANG ? 'is-on' : '' ?>" lang="<?= $code ?>"><?= $L['name'] ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- الشريط العلوي: الشعار يمينًا، اسم الجامعة في الوسط، الأزرار يسارًا -->
  <div class="u-top">
    <div class="u-wrap u-top-row">
      <a class="u-word" href="/index.php" aria-label="<?= htmlspecialchars(t('uni')) ?>">
        <img class="u-brand-cal" src="/assets/images/na-light.png" alt="<?= htmlspecialchars(t('uni')) ?>">
        <small>Bunyan International University</small>
      </a>

      <div class="u-actions">
        <a class="u-pill u-pill-solid" href="/student/index.php"><i class="fas fa-user-plus"></i><span><?= t('register') ?></span></a>
        <a class="u-pill" href="/elctro.php"><i class="fas fa-layer-group"></i><span><?= t('portal') ?></span></a>
        <?php if ($__user): ?>
        <span class="u-pill u-user" title="<?= htmlspecialchars($__user) ?>"><i class="fas fa-user-check"></i><span><?= htmlspecialchars(mb_strimwidth($__user, 0, 18, '…')) ?></span>
          <?php if (!empty($_SESSION['uwciv_super'])): ?><a href="/admin/settings.php" class="u-logout" title="إعدادات الموقع"><i class="fas fa-cog"></i></a><?php endif; ?>
          <a href="/syslog/logout.php" class="u-logout" title="<?= t('logout') ?>"><i class="fas fa-sign-out-alt"></i></a></span>
        <?php else: ?>
        <a class="u-pill" href="/syslog/index.php"><i class="fas fa-sign-in-alt"></i><span><?= t('login') ?></span></a>
        <?php endif; ?>
        <button type="button" class="u-burger" aria-label="<?= t('menu') ?>"><span></span><span></span><span></span></button>
      </div>
    </div>
  </div>

  <!-- القائمة الرئيسية -->
  <nav class="u-nav" aria-label="<?= t('menu') ?>">
    <div class="u-wrap">
      <ul class="u-menu">
        <?php foreach (uwciv_menu_tree() as $m): if (!empty($m['hidden'])) continue;
          $lbl = ($UWCIV_LANG !== 'ar' && isset($UWCIV_T[$m['key']]) && $m['label'] === $UWCIV_T[$m['key']][0]) ? t($m['key']) : $m['label'];
          $mu = uwciv_url_ok($m['url']); $mg = uwciv_render_mega_item($m, $lbl); $has = $mg !== '';
          $active = ($mu !== '#' && ltrim($mu, '/') === ltrim($_SERVER['SCRIPT_NAME'] ?? '', '/')) ? ' is-active' : ''; ?>
          <li class="u-item<?= $has ? ' u-has u-mg' : '' ?><?= $active ?>">
            <a href="<?= htmlspecialchars($mu) ?>"<?= $has ? ' aria-haspopup="true"' : '' ?>><?= htmlspecialchars($lbl) ?><?php if ($has): ?> <i class="fas fa-chevron-down u-chev"></i><?php endif; ?></a>
            <?= $mg ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </nav>
  <?= uwciv_ticker_render() ?>
  <?php if ($__stage): ?>
  <div class="u-stagebar">
    <div class="u-wrap u-stagebar-row">
      <span class="u-stage-now"><i class="fas <?= $__stages[$__stage][2] ?>"></i> <?= t('stage') ?>: <b><?= t($__stages[$__stage][0]) ?></b></span>
      <nav class="u-stage-tabs">
        <?php foreach ($__stages as $k => $st): ?>
          <a class="u-st u-st-<?= $k ?><?= $k === $__stage ? ' is-on' : '' ?>" href="<?= $st[1] ?>"><i class="fas <?= $st[2] ?>"></i> <?= t($st[0]) ?></a>
        <?php endforeach; ?>
      </nav>
    </div>
  </div>
  <?php endif; ?>
</header>

<?php if ($UWCIV_LANG !== 'ar'): ?>
<div id="google_translate_element" style="display:none"></div>
<script>
function googleTranslateElementInit(){new google.translate.TranslateElement({pageLanguage:'ar',includedLanguages:'en,bn,ur',autoDisplay:false},'google_translate_element');}
</script>
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<?php endif; ?>
<script>
(function(){
  var L = <?= json_encode($UWCIV_LANG) ?>, D = <?= json_encode($__L['dir']) ?>;
  var h = document.documentElement; h.setAttribute('lang', L); h.setAttribute('dir', D);
  document.body && document.body.setAttribute('dir', D);
  document.body && document.body.classList.add('u-lang-' + L);
  var S = <?= json_encode($__stage) ?>; if (S && document.body) document.body.classList.add('u-stage-' + S);
  var y = 31536000;
  document.cookie = 'uwciv_lang=' + L + ';path=/;max-age=' + y;
  if (L === 'ar') { document.cookie = 'googtrans=;path=/;max-age=0'; document.cookie = 'googtrans=;path=/;domain=.' + location.hostname + ';max-age=0'; }
  else { document.cookie = 'googtrans=/ar/' + L + ';path=/;max-age=' + y; }

  var hd = document.querySelector('.u-header');
  hd.querySelector('.u-burger').addEventListener('click', function(){ hd.classList.toggle('is-open'); });
  hd.querySelector('.u-lang-btn').addEventListener('click', function(e){ e.stopPropagation(); this.parentNode.classList.toggle('is-open'); });
  document.addEventListener('click', function(){ var l = hd.querySelector('.u-lang'); l && l.classList.remove('is-open'); });
  // الجوال: فتح القوائم الفرعية باللمس
  hd.querySelectorAll('.u-has > a').forEach(function(a){
    a.addEventListener('click', function(e){
      if (window.innerWidth < 1100) { e.preventDefault(); a.parentNode.classList.toggle('is-open'); }
    });
  });
  // أزرار القائمة الجانبية في صفحات الكليات: تنقل إلى القسم المطابق في الصفحة
  document.addEventListener('DOMContentLoaded', function(){
    var norm = function(t){ return (t||'').replace(/[\u0640\s]/g,'').replace(/[أإآ]/g,'ا').replace(/ة/g,'ه').replace(/ى/g,'ي'); };
    var cards = [].slice.call(document.querySelectorAll('.about-content'));
    var heads = cards.map(function(c, i){ var h = c.querySelector('h2,h3'); if (!c.id) c.id = 'sec-' + (i+1); return norm(h ? h.textContent : ''); });
    document.querySelectorAll('.sidebar-wrap-02 .checkbox-list a').forEach(function(a, i){
      var here = (a.getAttribute('href')||'').split('#')[0];
      var same = !here || location.pathname.split('/').pop() === here.split('/').pop();
      if (!same || !cards.length) return;
      var t = norm(a.textContent), idx = -1;
      heads.forEach(function(h, j){ if (idx < 0 && h && (h === t || h.indexOf(t) > -1 || t.indexOf(h) > -1)) idx = j; });
      if (idx < 0) heads.forEach(function(h, j){ if (idx < 0 && h && t.length > 3 && h.indexOf(t.slice(0, Math.min(6, t.length))) > -1) idx = j; });
      if (idx < 0 && i < cards.length) idx = i;
      if (idx < 0) return;
      a.setAttribute('href', '#' + cards[idx].id);
      a.addEventListener('click', function(e){ e.preventDefault(); var el = cards[idx]; window.scrollTo({top: el.getBoundingClientRect().top + window.scrollY - 140, behavior: 'smooth'}); el.classList.add('u-flash'); setTimeout(function(){ el.classList.remove('u-flash'); }, 1400);
        document.querySelectorAll('.sidebar-wrap-02 .checkbox-list a').forEach(function(x){ x.classList.remove('is-on'); }); a.classList.add('is-on'); });
    });
  });
  // القائمة العملاقة: تبديل الأقسام (حاسوب) وأكورديون (جوال)
  hd.querySelectorAll('.u-mega').forEach(function(mg){
    var tabs = mg.querySelectorAll('.u-mega-tab'), panes = mg.querySelectorAll('.u-mega-pane');
    tabs.forEach(function(tb){
      var show = function(){ if (window.innerWidth < 1100) return; tabs.forEach(function(x){ x.classList.toggle('on', x === tb); }); panes.forEach(function(p){ p.classList.toggle('on', p.dataset.i === tb.dataset.i); }); };
      tb.addEventListener('mouseenter', show); tb.addEventListener('focus', show);
      tb.addEventListener('click', function(e){
        e.stopPropagation();
        if (window.innerWidth >= 1100) { show(); return; }
        var acc = tb.nextElementSibling;
        if (!acc || !acc.classList.contains('u-mega-acc')) { acc = document.createElement('div'); acc.className = 'u-mega-acc'; var g = mg.querySelector('.u-mega-pane[data-i="' + tb.dataset.i + '"] .u-mega-grid'); if (g) acc.appendChild(g.cloneNode(true)); tb.after(acc); }
        tb.classList.toggle('op');
      });
    });
  });
  // شريط الأخبار: حركة مستمرة بالجافاسكربت (لا تتأثر بإعداد تقليل الحركة في الجهاز)
  var tk = hd.querySelector('.u-ticker');
  if (tk) (function(){
    var tr = tk.querySelectorAll('.u-tk-t'), v = tk.querySelector('.u-tk-v'), rtl = (document.documentElement.getAttribute('dir') || 'rtl') === 'rtl';
    var dur = parseFloat(getComputedStyle(tr[0]).getPropertyValue('--d')) || 60, x = 0, last = 0, paused = false, hover = false;
    tk.classList.add('js');
    function step(t){ if (!last) last = t; var dt = Math.min(64, t - last); last = t; var w = tr[0].offsetWidth;
      if (w && !paused && !hover && !document.hidden) { x += (w / dur) * dt / 1000; if (x >= w) x -= w; }
      var tx = rtl ? x : -x; tr[0].style.transform = tr[1].style.transform = 'translateX(' + tx + 'px)'; requestAnimationFrame(step); }
    requestAnimationFrame(step);
    v.addEventListener('mouseenter', function(){ hover = true; }); v.addEventListener('mouseleave', function(){ hover = false; });
    v.addEventListener('touchstart', function(){ hover = true; }, {passive:true}); v.addEventListener('touchend', function(){ setTimeout(function(){ hover = false; }, 1200); });
    tk.querySelector('.u-tk-p').addEventListener('click', function(){ paused = !paused; tk.classList.toggle('paused', paused); this.innerHTML = paused ? '<i class="fas fa-play"></i>' : '<i class="fas fa-pause"></i>'; });
  })();
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape') hd.querySelectorAll('.u-mg.is-open').forEach(function(x){ x.classList.remove('is-open'); }); });
  var onScroll = function(){ hd.classList.toggle('is-stuck', window.scrollY > 120); };
  window.addEventListener('scroll', onScroll, {passive:true}); onScroll();
})();
</script>
