<?php
/* المكتبة الرقمية المركزية */
require_once $_SERVER['DOCUMENT_ROOT'] . '/db_config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/shell.php';
$books = [];
try { $db = db_pdo('logweb'); $books = $db->query('SELECT id, book_dep, book_name, pdf_file FROM stdlb ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC); } catch (Throwable $e) { $books = []; }
$books = array_values(array_filter($books, function ($b) { return $b['pdf_file'] && is_file(__DIR__ . '/admin/files/' . basename($b['pdf_file'])); }));
$deps = []; foreach ($books as $b) { $k = trim($b['book_dep']); $deps[$k] = ($deps[$k] ?? 0) + 1; }
$PAL = ['#1f7a45', '#2c5fd0', '#9b1f45', '#0e9aa7', '#c9962a', '#5b4bb7', '#d0542c', '#8a5a2b'];
$col = function ($s) use ($PAL) { return $PAL[abs(crc32($s)) % count($PAL)]; };
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
$SUPER = uwciv_is_super();
uwciv_open('المكتبة الرقمية', t('library_m'), 'كتب ومراجع أكاديمية بصيغة PDF متاحة للقراءة والتحميل على مدار الساعة.');
?>
<style>
.lb-hero{margin:58px 0 22px;border-radius:28px;overflow:hidden;position:relative;color:#fff;padding:38px 36px;background:linear-gradient(110deg,rgba(6,33,15,.96) 35%,rgba(6,33,15,.55)),url('/librarysystem/lb.jpg') center/cover;box-shadow:var(--sh-lift)}
.lb-hero h2{color:#fff;font-size:clamp(28px,3.8vw,46px);font-weight:900;margin:8px 0 10px}
.lb-hero p{color:#d6ebdd;max-width:36em;line-height:1.9;margin:0 0 22px}
.lb-hero small{color:#9fe0b8;font-weight:800}
.lb-search{display:flex;align-items:center;gap:10px;background:#fff;border-radius:18px;padding:6px 6px 6px 18px;max-width:640px;box-shadow:0 20px 40px -20px #000}
.lb-search i{color:var(--muted);font-size:18px;margin-inline-start:12px}
.lb-search input{flex:1;border:0!important;outline:none;height:52px;font-size:16px;font-family:inherit;background:transparent!important;box-shadow:none!important;padding:0!important;color:var(--text)}
.lb-search span{background:var(--teal);color:#fff;border-radius:14px;padding:14px 20px;font-weight:900;white-space:nowrap}
.lb-kpi{display:flex;gap:26px;margin-top:22px}
.lb-kpi b{display:block;font-size:30px;font-weight:900;color:#7fe0a6;line-height:1}.lb-kpi span{font-size:13px;color:#cfe6d7}
.lb-bar{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:18px}
.lb-chip{border:1px solid #dfe9e3;background:#fff;border-radius:30px;padding:8px 15px;font-weight:800;font-size:14px;color:var(--ink-3);cursor:pointer;font-family:inherit}
.lb-chip small{background:var(--mist);border-radius:10px;padding:1px 8px;margin-inline-start:6px}
.lb-chip.on{background:var(--ink-3);color:#fff;border-color:var(--ink-3)}.lb-chip.on small{background:rgba(255,255,255,.2)}
.lb-view{margin-inline-start:auto;display:flex;gap:4px;background:#fff;border-radius:12px;padding:4px;border:1px solid #dfe9e3}
.lb-view button{border:0;background:none;width:36px;height:34px;border-radius:9px;cursor:pointer;color:var(--muted)}
.lb-view button.on{background:var(--mist);color:var(--teal)}
.lb-shelf{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:22px}
.lb-book{display:flex;flex-direction:column;gap:10px;perspective:900px}
.lb-cover{position:relative;aspect-ratio:3/4;border-radius:6px 14px 14px 6px;background:linear-gradient(135deg,var(--c),color-mix(in srgb,var(--c) 50%,#000));color:#fff;padding:18px 16px 16px 26px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:inset 8px 0 0 rgba(0,0,0,.18),inset 10px 0 0 rgba(255,255,255,.08),0 18px 30px -16px rgba(6,33,15,.6);transition:transform .45s cubic-bezier(.2,.8,.2,1);transform-origin:right center;overflow:hidden}
.lb-cover:after{content:"";position:absolute;inset:0;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='50' height='50'%3E%3Cpath d='M25 3l6 16 16 6-16 6-6 16-6-16-16-6 16-6z' fill='none' stroke='%23fff' stroke-opacity='.08'/%3E%3C/svg%3E");pointer-events:none}
.lb-book:hover .lb-cover{transform:rotateY(-14deg) translateX(-4px)}
.lb-cover small{font-size:11px;letter-spacing:.12em;opacity:.8;font-weight:800}
.lb-cover b{font-size:19px;line-height:1.4;font-weight:900;position:relative;z-index:1;word-break:break-word}
.lb-cover img{width:44px;opacity:.9;align-self:flex-start;position:relative;z-index:1}
.lb-name{font-weight:900;color:var(--ink);font-size:15px;line-height:1.5}
.lb-dep{font-size:12.5px;color:var(--muted);font-weight:700}
.lb-acts{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.lb-acts a{display:flex;align-items:center;justify-content:center;gap:6px;height:38px;border-radius:10px;font-weight:800;font-size:13.5px;text-decoration:none!important}
.lb-acts .r{background:var(--c);color:#fff!important}.lb-acts .d{background:var(--mist);color:var(--ink-3)!important}
.lb-shelf.list{grid-template-columns:1fr;gap:10px}
.lb-shelf.list .lb-book{flex-direction:row;align-items:center;background:#fff;border-radius:16px;padding:10px 14px;box-shadow:var(--sh-1);gap:14px}
.lb-shelf.list .lb-cover{width:54px;aspect-ratio:3/4;padding:6px;flex:none}.lb-shelf.list .lb-cover b,.lb-shelf.list .lb-cover small{display:none}.lb-shelf.list .lb-cover img{width:30px;margin:auto}
.lb-shelf.list .lb-info{flex:1}.lb-shelf.list .lb-acts{width:220px}
.lb-empty{text-align:center;background:#fff;border-radius:22px;padding:44px 20px;border:1px dashed #cfdcd4;color:var(--muted)}
.lb-empty i{font-size:42px;color:#bfd8c8;margin-bottom:10px}
.lb-more{margin-top:40px;display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.lb-more a{display:flex;gap:14px;align-items:center;background:#fff;border-radius:20px;padding:18px;box-shadow:var(--sh-1);text-decoration:none!important;color:var(--text)!important;transition:.25s}
.lb-more a:hover{transform:translateY(-4px);box-shadow:var(--sh-lift)}
.lb-more i{width:50px;height:50px;border-radius:14px;display:grid;place-items:center;font-size:20px;background:var(--cyan-soft);color:var(--teal);flex:none}
.lb-more b{display:block;color:var(--ink)}.lb-more small{color:var(--muted)}
.lb-adm{display:inline-flex;gap:8px;align-items:center;background:var(--cyan-soft);color:var(--teal)!important;border-radius:12px;padding:9px 14px;font-weight:800;text-decoration:none!important}
@media(max-width:800px){.lb-hero{padding:26px 18px}.lb-more{grid-template-columns:1fr}.lb-shelf{grid-template-columns:repeat(2,1fr);gap:14px}.lb-shelf.list .lb-acts{width:auto}.lb-search span{display:none}.lb-view{display:none}}
</style>

<section class="lb-hero">
  <small><i class="fas fa-book-open"></i> المكتبة المركزية</small>
  <h2>المكتبة الرقمية</h2>
  <p>تصفّح الكتب والمراجع الأكاديمية حسب القسم، واقرأها مباشرة أو حمّلها بصيغة PDF.</p>
  <label class="lb-search"><i class="fas fa-search"></i><input id="lbq" type="search" placeholder="ابحث بعنوان الكتاب أو القسم…" aria-label="بحث في المكتبة"><span>بحث فوري</span></label>
  <div class="lb-kpi"><div><b><?= count($books) ?></b><span>كتابًا</span></div><div><b><?= count($deps) ?></b><span>قسمًا</span></div><div><b>24/7</b><span>متاحة دائمًا</span></div></div>
</section>

<div class="lb-bar">
  <button class="lb-chip on" data-d="">الكل <small><?= count($books) ?></small></button>
  <?php foreach ($deps as $d => $n): ?><button class="lb-chip" data-d="<?= $h($d) ?>"><?= $h($d) ?> <small><?= $n ?></small></button><?php endforeach; ?>
  <?php if ($SUPER): ?><a class="lb-adm" href="/librarysystem/admin/index.php"><i class="fas fa-plus"></i> إضافة كتب</a><?php endif; ?>
  <div class="lb-view"><button class="on" data-v="grid" aria-label="عرض شبكي"><i class="fas fa-th-large"></i></button><button data-v="list" aria-label="عرض قائمة"><i class="fas fa-list"></i></button></div>
</div>

<?php if (!$books): ?>
  <div class="lb-empty"><i class="fas fa-book"></i><h3 style="color:var(--ink)">المكتبة قيد الإثراء</h3><p>نضيف الكتب والمراجع تباعًا، تابعنا قريبًا.</p></div>
<?php else: ?>
<div class="lb-shelf" id="lbs">
  <?php foreach ($books as $b): $url = '/librarysystem/admin/files/' . rawurlencode(basename($b['pdf_file'])); $c = $col($b['book_dep']); ?>
  <article class="lb-book" style="--c:<?= $c ?>" data-dep="<?= $h(trim($b['book_dep'])) ?>" data-hay="<?= $h($b['book_name'] . ' ' . $b['book_dep']) ?>">
    <div class="lb-cover"><small><?= $h(mb_strtoupper($b['book_dep'])) ?></small><b><?= $h($b['book_name']) ?></b><img src="/assets/images/logo-light.png" alt=""></div>
    <div class="lb-info"><div class="lb-name"><?= $h($b['book_name']) ?></div><div class="lb-dep"><i class="far fa-folder"></i> <?= $h($b['book_dep']) ?></div></div>
    <div class="lb-acts"><a class="r" href="<?= $url ?>" target="_blank" rel="noopener"><i class="fas fa-book-reader"></i> قراءة</a><a class="d" href="<?= $url ?>" download><i class="fas fa-download"></i> تحميل</a></div>
  </article>
  <?php endforeach; ?>
</div>
<div class="lb-empty" id="lbe" style="display:none"><i class="fas fa-search"></i><h3 style="color:var(--ink)">لا توجد نتائج</h3><p>جرّب كلمة أخرى أو اطلب الكتاب من <a href="/form2.php">الدعم الفني</a>.</p></div>
<?php endif; ?>

<div class="lb-more">
  <a href="/pdf_grad/index.php"><i class="fas fa-scroll"></i><span><b>وثائق الخريجين</b><small>البحث عن وثيقة التخرج</small></span></a>
  <a href="/journal"><i class="fas fa-newspaper"></i><span><b>المجلة العلمية</b><small>بحوث محكّمة وشروط النشر</small></span></a>
  <a href="/regulations.php"><i class="fas fa-gavel"></i><span><b>اللوائح وأدلة الكليات</b><small>35 وثيقة رسمية</small></span></a>
</div>

<script>
(function(){var q=document.getElementById('lbq'),dep='',books=document.querySelectorAll('.lb-book'),e=document.getElementById('lbe');
function norm(t){return (t||'').replace(/[أإآ]/g,'ا').replace(/ة/g,'ه').replace(/ى/g,'ي').toLowerCase();}
function run(){var v=norm(q.value.trim()),n=0;books.forEach(function(b){var ok=(!dep||b.dataset.dep===dep)&&(!v||norm(b.dataset.hay).indexOf(v)>-1);b.style.display=ok?'':'none';if(ok)n++;});if(e)e.style.display=n||!books.length?'none':'block';}
q.addEventListener('input',run);try{var qq=new URLSearchParams(location.search).get('q');if(qq){q.value=qq;run();}}catch(e){}
document.querySelectorAll('.lb-chip').forEach(function(c){c.onclick=function(){document.querySelectorAll('.lb-chip').forEach(function(x){x.classList.toggle('on',x===c)});dep=c.dataset.d;run();}});
document.querySelectorAll('.lb-view button').forEach(function(b){b.onclick=function(){document.querySelectorAll('.lb-view button').forEach(function(x){x.classList.toggle('on',x===b)});var s=document.getElementById('lbs');if(s)s.classList.toggle('list',b.dataset.v==='list');try{localStorage.setItem('lbView',b.dataset.v)}catch(x){}}});
try{if(localStorage.getItem('lbView')==='list')document.querySelector('.lb-view [data-v=list]').click();}catch(x){}})();
</script>
<?php uwciv_close(); ?>
