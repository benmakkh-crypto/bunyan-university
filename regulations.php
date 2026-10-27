<?php
/* اللوائح والأنظمة + مصفوفة الصلاحيات */
require_once $_SERVER['DOCUMENT_ROOT'] . '/db_config.php';
require_once __DIR__ . '/inc/shell.php';
require_once __DIR__ . '/inc/regs.php';
$R = uwciv_regs(); $CATS = uwciv_reg_cats(); $ST = uwciv_reg_status();
$docs = $R['docs']; $powers = $R['powers'];
list($WHO, $MX) = uwciv_regs_matrix();
$SUPER = uwciv_is_super();
$tab = ($_GET['t'] ?? '') === 'powers' ? 'powers' : 'docs';
$cnt = array_fill_keys(array_keys($CATS), 0); foreach ($docs as $d) if (isset($cnt[$d['cat']])) $cnt[$d['cat']]++;
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
uwciv_open('اللوائح والأنظمة', t('about'), 'المرجع المؤسسي لأنظمة الجامعة ولوائحها وأدلة كلياتها، ومصفوفة الصلاحيات من أعلى الهرم إلى آخره.');
?>
<style>
.rg-hero{margin:58px 0 20px;border-radius:26px;padding:30px 34px;color:#fff;background:linear-gradient(120deg,#06210f,#0b3119 55%,#11432a);display:grid;grid-template-columns:1fr auto;gap:24px;align-items:center;position:relative;overflow:hidden;box-shadow:var(--sh-lift)}
.rg-hero:after{content:"";position:absolute;inset:0;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='70' height='70'%3E%3Cpath d='M35 5l8 22 22 8-22 8-8 22-8-22-22-8 22-8z' fill='none' stroke='%23fff' stroke-opacity='.06'/%3E%3C/svg%3E");pointer-events:none}
.rg-hero small{color:#9fe0b8;font-weight:800;display:inline-flex;gap:8px;align-items:center}
.rg-hero small:before{content:"";width:22px;height:2px;background:#9fe0b8}
.rg-hero h2{color:#fff;font-size:clamp(28px,3.6vw,42px);font-weight:900;margin:8px 0}
.rg-hero p{color:#cfe6d7;margin:0;max-width:40em;line-height:1.9}
.rg-num{display:flex;gap:22px;position:relative;z-index:1}
.rg-num div{text-align:center;padding-inline-start:22px;border-inline-start:1px solid rgba(255,255,255,.2)}
.rg-num div:first-child{border:0;padding:0}
.rg-num b{display:block;font-size:42px;font-weight:900;color:#7fe0a6;line-height:1}.rg-num span{font-size:13px;color:#cfe6d7}
.rg-tabs{display:flex;gap:8px;background:#fff;border-radius:18px;padding:8px;box-shadow:var(--sh-1);margin-bottom:18px;position:sticky;top:70px;z-index:6;flex-wrap:wrap;align-items:center}
.rg-tab{border:0;background:none;border-radius:12px;padding:12px 18px;font-weight:900;font-size:15px;color:var(--muted);cursor:pointer;font-family:inherit;display:inline-flex;gap:8px;align-items:center}
.rg-tab.on{background:var(--ink-3);color:#fff}
.rg-adm{margin-inline-start:auto;display:inline-flex;gap:8px;align-items:center;background:var(--cyan-soft);color:var(--teal)!important;border-radius:12px;padding:10px 14px;font-weight:800;text-decoration:none!important}
.rg-pane{display:none}.rg-pane.on{display:block;animation:rgIn .45s ease}
@keyframes rgIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
/* الوثائق */
.rg-bar{display:grid;grid-template-columns:1fr auto;gap:14px;align-items:center;background:#fff;border-radius:18px;padding:14px;box-shadow:var(--sh-1);margin-bottom:14px}
.rg-q{display:flex;align-items:center;gap:10px;background:var(--mist);border-radius:12px;padding:0 14px}
.rg-q input{border:0!important;background:transparent!important;height:46px;flex:1;outline:none;font-family:inherit;font-size:15px;box-shadow:none!important;padding:0!important}
.rg-res{color:var(--muted);font-size:13.5px;font-weight:700}
.rg-cats{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px}
.rg-cat{border:1px solid #dfe9e3;background:#fff;border-radius:30px;padding:9px 15px;font-weight:800;font-size:14px;color:var(--ink-3);cursor:pointer;font-family:inherit;display:inline-flex;gap:8px;align-items:center;transition:.2s}
.rg-cat i{color:var(--c)}
.rg-cat small{background:var(--mist);border-radius:10px;padding:1px 8px;font-size:12px}
.rg-cat.on{background:var(--c);border-color:var(--c);color:#fff}.rg-cat.on i{color:#fff}.rg-cat.on small{background:rgba(255,255,255,.2)}
.rg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.rg-doc{position:relative;background:#fff;border-radius:20px;padding:18px 18px 16px;box-shadow:var(--sh-1);display:flex;flex-direction:column;gap:10px;border-top:4px solid var(--c);transition:transform .25s,box-shadow .25s}
.rg-doc:hover{transform:translateY(-4px);box-shadow:0 22px 40px -22px rgba(6,33,15,.4)}
.rg-top{display:flex;justify-content:space-between;align-items:center;font-size:12px;font-weight:800}
.rg-no{color:#9aa9a0;letter-spacing:1px}
.rg-st{border-radius:20px;padding:3px 10px;color:#fff;background:var(--sc)}
.rg-ttl{display:flex;gap:12px;align-items:flex-start}
.rg-ttl i{width:46px;height:46px;flex:none;border-radius:14px;display:grid;place-items:center;font-size:19px;color:var(--c);background:color-mix(in srgb,var(--c) 11%,#fff)}
.rg-ttl b{display:block;font-size:16px;font-weight:900;color:var(--ink);line-height:1.5}
.rg-ttl small{display:block;color:var(--muted);font-size:13px;line-height:1.7;margin-top:2px}
.rg-meta{display:flex;gap:12px;color:#8a9a90;font-size:12.5px;font-weight:700;margin-top:auto}
.rg-act{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.rg-act a{display:flex;align-items:center;justify-content:center;gap:7px;height:40px;border-radius:12px;font-weight:800;font-size:14px;text-decoration:none!important;transition:.2s}
.rg-act .v{border:1px solid #dfe9e3;color:var(--ink-3)!important;background:#fff}
.rg-act .v:hover{border-color:var(--c)}
.rg-act .d{background:var(--c);color:#fff!important}
.rg-act .d:hover{filter:brightness(1.1)}
.rg-edit{position:absolute;top:12px;left:50%;transform:translateX(-50%);font-size:12px;font-weight:800;background:var(--mist);border-radius:20px;padding:2px 10px;color:var(--ink-3)!important;text-decoration:none!important}
.rg-empty{display:none;text-align:center;color:var(--muted);padding:30px;background:#fff;border-radius:18px;border:1px dashed #cfdcd4}
/* الصلاحيات */
.pw-intro{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:22px}
.pw-intro div{background:#fff;border-radius:16px;padding:14px;box-shadow:var(--sh-1);display:flex;gap:10px;align-items:center;font-size:14px;color:var(--ink-3);font-weight:700}
.pw-intro b{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;color:#fff;font-size:14px;flex:none}
.pw-ladder{position:relative;padding-inline-start:54px}
.pw-ladder:before{content:"";position:absolute;inset-inline-start:21px;top:10px;bottom:10px;width:3px;border-radius:3px;background:linear-gradient(#0b3119,#46c07a)}
.pw-lv{position:relative;margin-bottom:16px}
.pw-lv>span{position:absolute;inset-inline-start:-54px;top:12px;width:44px;height:44px;border-radius:50%;display:grid;place-items:center;font-weight:900;color:#fff;background:var(--lc);box-shadow:0 0 0 5px var(--mist);z-index:1;font-size:15px}
.pw-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px}
.pw-card{background:#fff;border-radius:18px;box-shadow:var(--sh-1);overflow:hidden;border-inline-start:4px solid var(--lc)}
.pw-card summary{list-style:none;cursor:pointer;display:flex;gap:12px;align-items:center;padding:14px 16px}
.pw-card summary::-webkit-details-marker{display:none}
.pw-card summary i.ic{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;flex:none;color:var(--lc);background:color-mix(in srgb,var(--lc) 10%,#fff);font-size:17px}
.pw-card summary b{display:block;color:var(--ink);font-size:15.5px}.pw-card summary small{color:var(--muted);font-size:12.5px}
.pw-card summary .ch{margin-inline-start:auto;color:#9aa9a0;transition:.25s}
.pw-card[open] summary .ch{transform:rotate(180deg)}
.pw-card ul{list-style:none;margin:0;padding:0 16px 14px}
.pw-card li{display:flex;gap:10px;align-items:flex-start;padding:7px 0;border-top:1px dashed #e5ede8;font-size:14px;line-height:1.7}
.pw-card li:before{content:"\f00c";font-family:"Font Awesome 5 Free";font-weight:900;color:var(--lc);font-size:12px;margin-top:5px}
.pw-mx{margin-top:30px;background:#fff;border-radius:22px;box-shadow:var(--sh-1);padding:22px;overflow-x:auto}
.pw-mx h3{margin:0 0 6px;font-size:20px;font-weight:900;color:var(--ink)}
.pw-leg{display:flex;gap:14px;flex-wrap:wrap;color:var(--muted);font-size:13px;margin-bottom:14px}
.pw-leg span{display:inline-flex;gap:6px;align-items:center}
.pw-t{width:100%;border-collapse:separate;border-spacing:0;min-width:760px}
.pw-t th{font-size:13px;color:var(--ink-3);padding:10px;text-align:center;border-bottom:2px solid #e5ede8;font-weight:800}
.pw-t th:first-child,.pw-t td:first-child{text-align:right}
.pw-t td{padding:10px;text-align:center;border-bottom:1px solid #eef3f0;font-size:14px}
.pw-t tr:hover td{background:#f7faf8}
.rl{display:inline-grid;place-items:center;min-width:30px;height:30px;border-radius:9px;font-weight:900;font-size:12.5px}
.rl.A{background:#0b3119;color:#fff}.rl.P{background:#e4f4ea;color:#1f7a45}.rl.C{background:#e8eefc;color:#2c5fd0}.rl.I{background:#f3f3f3;color:#7a867f}
@media(max-width:900px){.rg-hero{grid-template-columns:1fr}.pw-intro{grid-template-columns:1fr 1fr}.rg-bar{grid-template-columns:1fr}.rg-tabs{top:0}}
@media(max-width:560px){.pw-ladder{padding-inline-start:44px}.pw-lv>span{inset-inline-start:-44px;width:36px;height:36px}.pw-ladder:before{inset-inline-start:17px}.rg-hero{padding:24px 18px}}
</style>

<section class="rg-hero">
  <div><small>المرجع المؤسسي</small><h2>اللوائح والأنظمة</h2><p>تصفّح أنظمة الجامعة ولوائحها وأدلة كلياتها، واعرف صلاحيات كل جهة من مجلس الأمناء إلى الطالب.</p></div>
  <div class="rg-num"><div><b><?= count($docs) ?></b><span>وثيقة</span></div><div><b><?= count($powers) ?></b><span>جهة وصلاحية</span></div></div>
</section>

<div class="rg-tabs">
  <button class="rg-tab<?= $tab === 'docs' ? ' on' : '' ?>" data-t="docs"><i class="fas fa-folder-open"></i> الوثائق والأدلة</button>
  <button class="rg-tab<?= $tab === 'powers' ? ' on' : '' ?>" data-t="powers"><i class="fas fa-sitemap"></i> مصفوفة الصلاحيات</button>
  <?php if ($SUPER): ?><a class="rg-adm" href="/admin/regs.php"><i class="fas fa-pen"></i> إدارة اللوائح والصلاحيات</a><?php endif; ?>
</div>

<!-- الوثائق -->
<section class="rg-pane<?= $tab === 'docs' ? ' on' : '' ?>" id="rg-docs">
  <div class="rg-bar"><label class="rg-q"><i class="fas fa-search" style="color:var(--muted)"></i><input id="rgq" type="search" placeholder="ابحث باسم اللائحة أو الدليل أو الكلية…" aria-label="بحث في الوثائق"></label><span class="rg-res" id="rgr">عرض <?= count($docs) ?> من أصل <?= count($docs) ?></span></div>
  <div class="rg-cats">
    <button class="rg-cat on" data-c="" style="--c:#0b3119"><i class="fas fa-th-large"></i> الكل <small><?= count($docs) ?></small></button>
    <?php foreach ($CATS as $k => $c): ?><button class="rg-cat" data-c="<?= $k ?>" style="--c:<?= $c[2] ?>"><i class="fas <?= $c[1] ?>"></i> <?= $c[0] ?> <small><?= $cnt[$k] ?></small></button><?php endforeach; ?>
  </div>
  <div class="rg-grid">
    <?php foreach ($docs as $i => $d): $c = $CATS[$d['cat']] ?? $CATS['general']; $s = $ST[$d['status']] ?? $ST['soon'];
      $hasView = $d['file'] || !empty($d['articles']) || !empty($d['college']);
      $view = $d['file'] ? $d['file'] : '/regulations-view.php?d=' . rawurlencode($d['id']);
      $dl = $d['file'] ? $d['file'] : '/regulations-view.php?d=' . rawurlencode($d['id']) . '&print=1';
      $icon = $d['cat'] === 'guides' && !empty($d['college']) && isset($UWCIV_COLLEGES[$d['college']]) ? $UWCIV_COLLEGES[$d['college']]['i'] : $c[1]; ?>
    <article class="rg-doc" style="--c:<?= $c[2] ?>" data-cat="<?= $d['cat'] ?>" data-hay="<?= $h($d['title'] . ' ' . $d['desc'] . ' ' . $c[0]) ?>">
      <?php if ($SUPER): ?><a class="rg-edit" href="/admin/regs.php#d-<?= $h($d['id']) ?>"><i class="fas fa-pen"></i> تعديل</a><?php endif; ?>
      <div class="rg-top"><span class="rg-no"><?= sprintf('%02d', $i + 1) ?></span><span class="rg-st" style="--sc:<?= $s[1] ?>"><?= $s[0] ?></span></div>
      <div class="rg-ttl"><i class="fas <?= $icon ?>"></i><div><b><?= $h($d['title']) ?></b><small><?= $h($d['desc']) ?></small></div></div>
      <div class="rg-meta"><span><i class="far fa-file-pdf"></i> <?= $d['file'] ? 'ملف PDF' : 'وثيقة إلكترونية' ?></span><span><i class="far fa-folder"></i> <?= $c[0] ?></span></div>
      <div class="rg-act">
        <?php if ($hasView): ?><a class="v" href="<?= $h($view) ?>" target="_blank" rel="noopener"><i class="far fa-eye"></i> معاينة</a><a class="d" href="<?= $h($dl) ?>" <?= $d['file'] ? 'download' : 'target="_blank" rel="noopener"' ?>><i class="fas fa-download"></i> تنزيل</a>
        <?php else: ?><a class="v" style="grid-column:1/-1;opacity:.6;pointer-events:none"><i class="far fa-clock"></i> تُنشر قريبًا</a><?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <div class="rg-empty" id="rge">لا توجد وثائق مطابقة.</div>
</section>

<!-- الصلاحيات -->
<section class="rg-pane<?= $tab === 'powers' ? ' on' : '' ?>" id="rg-powers">
  <div class="pw-intro">
    <div><b style="background:#0b3119"><i class="fas fa-landmark"></i></b>الحوكمة العليا</div>
    <div><b style="background:#1f7a45"><i class="fas fa-user-tie"></i></b>القيادة التنفيذية</div>
    <div><b style="background:#2c5fd0"><i class="fas fa-university"></i></b>الكليات والأقسام</div>
    <div><b style="background:#46c07a"><i class="fas fa-users"></i></b>الأساتذة والموظفون والطلاب</div>
  </div>
  <div class="pw-ladder">
    <?php $byLv = []; foreach ($powers as $p) $byLv[$p[0]][] = $p;
      $lc = [1 => '#0b3119', 2 => '#11432a', 3 => '#1f7a45', 4 => '#2c5fd0', 5 => '#0e9aa7', 6 => '#2e8b57', 7 => '#5b8c6e', 8 => '#46c07a'];
      ksort($byLv); foreach ($byLv as $lv => $list): ?>
    <div class="pw-lv" style="--lc:<?= $lc[$lv] ?? '#1f7a45' ?>"><span><?= $lv ?></span>
      <div class="pw-row">
        <?php foreach ($list as $p): ?>
        <details class="pw-card" <?= $lv <= 2 ? 'open' : '' ?>><summary><i class="fas <?= $h($p[1]) ?> ic"></i><span><b><?= $h($p[2]) ?></b><small><?= $h($p[3]) ?></small></span><i class="fas fa-chevron-down ch"></i></summary>
          <ul><?php foreach ($p[4] as $x): ?><li><?= $h($x) ?></li><?php endforeach; ?></ul></details>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="pw-mx">
    <h3><i class="fas fa-table" style="color:var(--teal)"></i> مصفوفة القرارات الرئيسة</h3>
    <div class="pw-leg"><span><i class="rl A">ع</i> يعتمد القرار</span><span><i class="rl P">ق</i> يقترح ويُعد</span><span><i class="rl C">س</i> يُستشار</span><span><i class="rl I">ب</i> يُبلَّغ</span></div>
    <table class="pw-t"><thead><tr><th>القرار</th><?php foreach ($WHO as $w): ?><th><?= $w ?></th><?php endforeach; ?></tr></thead><tbody>
    <?php $lbl = ['A' => 'ع', 'P' => 'ق', 'C' => 'س', 'I' => 'ب']; foreach ($MX as $r): ?><tr><td><b><?= $r[0] ?></b></td><?php foreach ($r[1] as $v): ?><td><?= $v ? '<i class="rl ' . $v . '">' . $lbl[$v] . '</i>' : '—' ?></td><?php endforeach; ?></tr><?php endforeach; ?>
    </tbody></table>
  </div>
  <p style="color:var(--muted);font-size:13.5px;margin-top:14px"><i class="fas fa-info-circle"></i> الصلاحيات المعروضة مسودة تنظيمية، وتُعتمد نهائيًا بقرار من مجلس الأمناء وفق النظام الأساسي.</p>
</section>

<script>
(function(){
  var tabs=document.querySelectorAll('.rg-tab');
  tabs.forEach(function(t){t.onclick=function(){tabs.forEach(function(x){x.classList.toggle('on',x===t)});document.querySelectorAll('.rg-pane').forEach(function(p){p.classList.toggle('on',p.id==='rg-'+t.dataset.t)});history.replaceState(null,'',t.dataset.t==='powers'?'?t=powers':location.pathname);};});
  var q=document.getElementById('rgq'),cat='',docs=document.querySelectorAll('.rg-doc'),res=document.getElementById('rgr'),total=docs.length;
  function norm(t){return (t||'').replace(/[أإآ]/g,'ا').replace(/ة/g,'ه').replace(/ى/g,'ي').toLowerCase();}
  function run(){var v=norm(q.value.trim()),n=0;docs.forEach(function(d){var ok=(!cat||d.dataset.cat===cat)&&(!v||norm(d.dataset.hay).indexOf(v)>-1);d.style.display=ok?'':'none';if(ok)n++;});res.textContent='عرض '+n+' من أصل '+total;document.getElementById('rge').style.display=n?'none':'block';}
  q.addEventListener('input',run);
  document.querySelectorAll('.rg-cat').forEach(function(b){b.onclick=function(){document.querySelectorAll('.rg-cat').forEach(function(x){x.classList.remove('on')});b.classList.add('on');cat=b.dataset.c;run();};});
})();
</script>
<?php uwciv_close(); ?>
