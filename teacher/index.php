<?php
/* أعضاء الجامعة — دليل تفاعلي مع إدارة مباشرة للمشرف العام */
require_once $_SERVER['DOCUMENT_ROOT'] . '/db_config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/shell.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/members.php';
$G = uwciv_member_groups();
$M = uwciv_members();
$cur = isset($G[$_GET['g'] ?? '']) ? $_GET['g'] : 'board';
$count = array_fill_keys(array_keys($G), 0); foreach ($M as $x) if (isset($count[$x['g']])) $count[$x['g']]++;
$named = count(array_filter($M, function ($x) { return trim($x['name']) !== ''; }));
$LD = uwciv_member_link_defs();
$SUPER = uwciv_is_super();
if ($SUPER && empty($_SESSION['mem_csrf'])) $_SESSION['mem_csrf'] = bin2hex(random_bytes(16));
$COL = ['board' => '#0b3119', 'faculty' => '#1f7a45', 'deans' => '#2c5fd0', 'advisors' => '#0e9aa7', 'legal' => '#9b1f45', 'admin' => '#5b4bb7', 'honorary' => '#8a5a2b'];
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
$lead = null; $pres = null;
foreach ($M as $x) { if ($x['g'] === 'board' && !empty($x['lead']) && !$lead) $lead = $x; if (mb_strpos($x['title'], 'رئيس الجامعة') === 0 && !$pres) $pres = $x; }
uwciv_open('أعضاء الجامعة', t('about'), 'تعرّف على القيادات الأكاديمية والإدارية التي تقود جامعة بنيان العالمية.');

function md_ini($name)
{
    $name = trim(preg_replace('/^(أ\.د\.|د\.|أ\.|الشيخ|الدكتور|الأستاذ)\s*/u', '', $name));
    if ($name === '') return '';
    $p = preg_split('/\s+/u', $name);
    return mb_substr($p[0], 0, 1) . (count($p) > 1 ? mb_substr(end($p), 0, 1) : '');
}
?>
<style>
.mb{--c:#1f7a45}
.mb-head{display:grid;grid-template-columns:1fr auto;gap:24px;align-items:end;margin:64px 0 26px}
.mb-head h2{margin:0;font-size:clamp(26px,3.2vw,36px);font-weight:900;color:var(--ink)}
.mb-head p{margin:8px 0 0;color:var(--muted);font-size:16px;max-width:44em;line-height:1.8}
.mb-kpi{display:flex;gap:12px}
.mb-kpi div{background:#fff;border-radius:18px;padding:14px 20px;box-shadow:var(--sh-1);min-width:104px}
.mb-kpi b{display:block;font-size:28px;font-weight:900;color:var(--ink);line-height:1.1}.mb-kpi span{font-size:13px;color:var(--muted)}
/* خريطة القيادة */
.mb-org{background:linear-gradient(160deg,#0b3119,#06210f);border-radius:28px;padding:30px 26px 26px;color:#fff;margin-bottom:28px;position:relative;overflow:hidden}
.mb-org:before{content:"";position:absolute;inset:0;background:radial-gradient(600px 260px at 50% -10%,rgba(70,192,122,.25),transparent 60%)}
.mb-org>*{position:relative}
.mb-org h3{text-align:center;margin:0 0 22px;font-size:18px;font-weight:800;color:#bfe6cc}
.mb-lv{display:flex;justify-content:center;gap:14px;flex-wrap:wrap;position:relative}
.mb-lv+.mb-lv{margin-top:34px}
.mb-lv+.mb-lv:before{content:"";position:absolute;top:-34px;left:50%;width:2px;height:22px;background:rgba(255,255,255,.3)}
.mb-node{display:flex;align-items:center;gap:12px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.16);border-radius:18px;padding:10px 16px 10px 12px;cursor:pointer;transition:.25s;color:#fff;font-family:inherit;text-align:right;min-width:200px}
.mb-node:hover{background:rgba(255,255,255,.16);transform:translateY(-3px)}
.mb-node .av{width:46px;height:46px;border-radius:50%;flex:none;display:grid;place-items:center;background:rgba(255,255,255,.14);overflow:hidden;font-weight:900;color:#9fe0b8}
.mb-node .av img{width:100%;height:100%;object-fit:cover}
.mb-node b{display:block;font-size:14.5px}.mb-node small{color:#a9d6b9;font-size:12.5px}
.mb-node.top{background:#fff;color:var(--ink);border-color:#fff;min-width:260px}
.mb-node.top small{color:var(--teal)}.mb-node.top .av{background:var(--cyan-soft);color:var(--teal)}
.mb-node em{font-style:normal;margin-inline-start:auto;background:rgba(255,255,255,.14);border-radius:10px;padding:2px 9px;font-size:12px;font-weight:800}
/* الفئات */
.mb-cats{display:grid;grid-template-columns:repeat(7,1fr);gap:10px;margin-bottom:18px}
.mb-cat{border:0;background:#fff;border-radius:18px;padding:14px 10px;cursor:pointer;box-shadow:var(--sh-1);display:flex;flex-direction:column;align-items:center;gap:8px;font-family:inherit;transition:.25s;border-bottom:3px solid transparent;position:relative}
.mb-cat i{width:44px;height:44px;border-radius:14px;display:grid;place-items:center;font-size:18px;color:var(--c);background:color-mix(in srgb,var(--c) 12%,#fff);transition:.25s}
.mb-cat b{font-size:13.5px;color:var(--ink);font-weight:800;text-align:center;line-height:1.4}
.mb-cat small{position:absolute;top:8px;left:10px;font-size:11.5px;font-weight:800;color:var(--muted)}
.mb-cat:hover{transform:translateY(-3px)}
.mb-cat.on{border-bottom-color:var(--c);box-shadow:0 14px 26px -16px var(--c)}
.mb-cat.on i{background:var(--c);color:#fff}
.mb-tools{display:flex;gap:12px;align-items:center;margin-bottom:22px;flex-wrap:wrap}
.mb-intro{flex:1;color:var(--muted);font-size:15px;padding-inline-start:14px;border-inline-start:4px solid var(--c);min-width:240px}
.mb-search{display:flex;align-items:center;gap:8px;background:#fff;border-radius:14px;padding:0 14px;box-shadow:var(--sh-1)}
.mb-search input{border:0!important;outline:none;height:44px;width:230px;background:transparent!important;font-family:inherit;box-shadow:none!important;padding:0!important}
.mb-add{border:0;border-radius:14px;height:44px;padding:0 18px;font-weight:900;color:#fff;background:linear-gradient(135deg,var(--teal),var(--ink-3));cursor:pointer;font-family:inherit;display:inline-flex;gap:8px;align-items:center}
.mb-pane{display:none}.mb-pane.on{display:block;animation:mbIn .45s ease}
@keyframes mbIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
.mb-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:20px}
/* البطاقة */
.mb-card{position:relative;background:#fff;border-radius:22px;overflow:hidden;box-shadow:var(--sh-1);cursor:pointer;transition:transform .3s cubic-bezier(.2,.8,.2,1),box-shadow .3s;text-align:center;outline:none}
.mb-card:hover,.mb-card:focus-visible{transform:translateY(-6px);box-shadow:0 26px 44px -24px rgba(6,33,15,.45)}
.mb-cover{height:86px;background:linear-gradient(135deg,var(--c),color-mix(in srgb,var(--c) 55%,#000));position:relative;overflow:hidden}
.mb-cover:after{content:"";position:absolute;inset:0;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60'%3E%3Cpath d='M30 4l7 19 19 7-19 7-7 19-7-19-19-7 19-7z' fill='none' stroke='%23fff' stroke-opacity='.12'/%3E%3C/svg%3E")}
.mb-av{width:104px;height:104px;border-radius:50%;margin:-56px auto 12px;position:relative;z-index:1;border:5px solid #fff;background:linear-gradient(145deg,#eef5f0,#d7e8de);display:grid;place-items:center;overflow:hidden;box-shadow:0 12px 24px -12px rgba(6,33,15,.5);color:#8fb19d;font-size:38px}
.mb-av img{width:100%;height:100%;object-fit:cover}
.mb-av span{font-size:32px;font-weight:900;color:var(--c)}
.mb-body{padding:0 18px 20px}
.mb-name{font-size:17.5px;font-weight:900;color:var(--ink);margin:0 0 4px;line-height:1.45}
.mb-name.tbd{color:#9aa9a0;font-weight:700;font-size:15px}
.mb-role{display:inline-block;color:var(--c);background:color-mix(in srgb,var(--c) 10%,#fff);font-weight:800;font-size:13px;border-radius:10px;padding:4px 10px;line-height:1.5}
.mb-spec{margin:8px 0 0;color:var(--muted);font-size:13.5px;line-height:1.6}
.mb-more{display:inline-flex;gap:6px;align-items:center;margin-top:12px;font-size:13px;font-weight:800;color:var(--c)}
.mb-edit{position:absolute;top:10px;left:10px;z-index:2;width:34px;height:34px;border-radius:10px;border:0;background:rgba(255,255,255,.92);color:var(--ink-3);cursor:pointer}
.mb-card.lead{grid-column:1/-1;display:grid;grid-template-columns:260px 1fr;text-align:right}
.mb-card.lead .mb-cover{height:auto;display:grid;place-items:center;padding:26px}
.mb-card.lead .mb-av{margin:0;width:150px;height:150px;border-color:rgba(255,255,255,.3)}
.mb-card.lead .mb-body{padding:26px 30px;display:flex;flex-direction:column;justify-content:center}
.mb-card.lead .mb-name{font-size:26px}
.mb-card.lead .mb-tag{font-size:12.5px;font-weight:800;color:var(--muted);margin-bottom:6px}
.mb-empty{display:none;text-align:center;color:var(--muted);padding:30px;background:#fff;border-radius:18px;border:1px dashed #cfdcd4}
/* النافذة */
.mb-dlg{border:0;padding:0;border-radius:26px;width:min(560px,94vw);margin:auto;position:fixed;inset:0;height:fit-content;max-height:92vh;overflow:auto;box-shadow:0 40px 90px -30px rgba(0,0,0,.6)}
.mb-dlg::backdrop{background:rgba(4,21,9,.55);backdrop-filter:blur(4px)}
.mb-dlg .mb-cover{height:110px}
.mb-dlg .mb-av{width:120px;height:120px;margin-top:-64px}
.mb-dlg .mb-body{padding:0 26px 26px;text-align:center}
.mb-dlg .mb-bio{color:#46584d;line-height:1.9;font-size:15px;margin:14px 0 0;text-align:right}
.mb-links{display:flex;gap:8px;justify-content:center;margin-top:16px;flex-wrap:wrap}
.mb-links a{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;background:var(--mist);color:var(--ink-3)!important;transition:.2s;text-decoration:none!important}
.mb-links a:hover{background:var(--c);color:#fff!important}
.mb-x{position:absolute;top:12px;left:12px;z-index:3;width:36px;height:36px;border-radius:50%;border:0;background:rgba(255,255,255,.9);cursor:pointer;font-size:18px}
/* نموذج الإضافة */
.mb-form{padding:24px 26px 26px}
.mb-form h3{margin:0 0 14px;font-size:20px;font-weight:900;color:var(--ink)}
.mb-fg{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:12px}
.mb-fg input:not([type=checkbox]),.mb-fg select,.mb-fg textarea{width:100%;min-width:0;box-sizing:border-box}
.mb-ok+.mb-head{margin-top:24px}
.mb-fg label{display:flex;flex-direction:column;gap:6px;font-weight:800;font-size:13.5px;color:var(--ink-3)}
.mb-fg label.w{grid-column:1/-1}
.mb-fg input,.mb-fg select,.mb-fg textarea{border:2px solid #e3ece6;border-radius:12px;padding:10px 12px;font-family:inherit;font-weight:500;font-size:14.5px;outline:none}
.mb-fg input:focus,.mb-fg select:focus,.mb-fg textarea:focus{border-color:var(--teal)}
.mb-fg .chk{flex-direction:row;align-items:center;gap:8px}
.mb-fa{display:flex;gap:10px;margin-top:16px}
.mb-fa button{border:0;border-radius:14px;padding:12px 22px;font-weight:900;font-family:inherit;cursor:pointer}
.mb-fa .s{background:linear-gradient(135deg,var(--teal),var(--ink-3));color:#fff}.mb-fa .c{background:var(--mist);color:var(--ink-3)}
.mb-ok{background:var(--cyan-soft);color:var(--teal);border-radius:14px;padding:12px 16px;font-weight:800;margin-bottom:16px}
@media(max-width:1100px){.mb-cats{grid-template-columns:repeat(4,1fr)}}
@media(max-width:700px){.mb-head{grid-template-columns:1fr}.mb-kpi div{flex:1;min-width:0}.mb-cats{grid-template-columns:repeat(2,1fr)}.mb-card.lead{grid-template-columns:1fr;text-align:center}.mb-card.lead .mb-cover{padding:20px}.mb-search,.mb-search input{width:100%}.mb-fg{grid-template-columns:1fr}.mb-node{min-width:0;width:100%}}
</style>

<div class="mb">
  <?php if (isset($_GET['ok']) && $SUPER): ?><div class="mb-ok" style="margin-top:58px"><i class="fas fa-check-circle"></i> <?= $h($_GET['ok']) ?></div><?php endif; ?>
  <div class="mb-head">
    <div><h2>أعضاء جامعة بنيان العالمية</h2><p>قيادات علمية وإدارية تعمل معًا لتحقيق رسالة الجامعة — اضغط على أي عضو لعرض ملفه الكامل.</p></div>
    <div class="mb-kpi"><div><b><?= count($M) ?></b><span>منصبًا</span></div><div><b><?= count($G) ?></b><span>فئات</span></div><div><b><?= count($UWCIV_COLLEGES) ?></b><span>كلية</span></div></div>
  </div>

  <!-- خريطة القيادة -->
  <section class="mb-org" aria-label="الهيكل القيادي">
    <h3><i class="fas fa-sitemap"></i> الهيكل القيادي للجامعة</h3>
    <?php $node = function ($x, $fallbackTitle, $g, $cls = '') use ($h) { $nm = $x && trim($x['name']) !== '' ? $x['name'] : 'سيُعلن قريبًا'; $ph = $x['photo'] ?? ''; $ini = $x ? md_ini($x['name']) : '';
      return '<button type="button" class="mb-node ' . $cls . '" data-go="' . $g . '"><span class="av">' . ($ph ? '<img src="' . $h($ph) . '" alt="">' : ($ini ? $h($ini) : '<i class="fas fa-user"></i>')) . '</span><span><b>' . $h($nm) . '</b><small>' . $h($x['title'] ?? $fallbackTitle) . '</small></span></button>'; }; ?>
    <div class="mb-lv"><?= $node($lead, 'رئيس مجلس الأمناء', 'board', 'top') ?></div>
    <div class="mb-lv"><?= $node($pres, 'رئيس الجامعة', 'board', 'top') ?></div>
    <div class="mb-lv">
      <?php foreach (['deans', 'faculty', 'advisors', 'legal', 'admin'] as $g): ?>
      <button type="button" class="mb-node" data-go="<?= $g ?>"><span class="av"><i class="fas <?= $G[$g][1] ?>"></i></span><span><b><?= $G[$g][0] ?></b><small>عرض الأعضاء</small></span><em><?= $count[$g] ?></em></button>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- الفئات -->
  <div class="mb-cats" role="tablist">
    <?php foreach ($G as $k => $g): ?><button class="mb-cat<?= $k === $cur ? ' on' : '' ?>" style="--c:<?= $COL[$k] ?>" data-g="<?= $k ?>" role="tab"><small><?= $count[$k] ?></small><i class="fas <?= $g[1] ?>"></i><b><?= $g[0] ?></b></button><?php endforeach; ?>
  </div>
  <div class="mb-tools">
    <p class="mb-intro" id="mbIntro" style="--c:<?= $COL[$cur] ?>"><?= $G[$cur][2] ?></p>
    <label class="mb-search"><i class="fas fa-search" style="color:var(--muted)"></i><input id="mbq" type="search" placeholder="ابحث بالاسم أو المنصب" aria-label="بحث"></label>
    <?php if ($SUPER): ?><button type="button" class="mb-add" id="mbAdd"><i class="fas fa-user-plus"></i> إضافة عضو</button><?php endif; ?>
  </div>

  <?php foreach ($G as $k => $g): $list = array_filter($M, function ($x) use ($k) { return $x['g'] === $k; }); ?>
  <section class="mb-pane<?= $k === $cur ? ' on' : '' ?>" id="mb-<?= $k ?>" style="--c:<?= $COL[$k] ?>" data-intro="<?= $h($g[2]) ?>">
    <div class="mb-grid">
      <?php foreach ($list as $x): $tbd = trim($x['name']) === ''; $ini = md_ini($x['name']); $isLead = !empty($x['lead']);
        $data = ['id' => $x['id'], 'g' => $x['g'], 'name' => $x['name'], 'title' => $x['title'], 'spec' => $x['spec'], 'bio' => $x['bio'], 'email' => $x['email'], 'photo' => $x['photo'], 'links' => $x['links'] ?? [], 'lead' => $isLead]; ?>
      <article class="mb-card<?= $isLead ? ' lead' : '' ?>" tabindex="0" data-m="<?= $h(json_encode($data, JSON_UNESCAPED_UNICODE)) ?>" data-hay="<?= $h($x['name'] . ' ' . $x['title'] . ' ' . $x['spec']) ?>">
        <?php if ($SUPER): ?><button type="button" class="mb-edit" title="تعديل" data-edit><i class="fas fa-pen"></i></button><?php endif; ?>
        <div class="mb-cover"><?php if ($isLead): ?><div class="mb-av"><?= $x['photo'] ? '<img src="' . $h($x['photo']) . '" alt="">' : ($ini ? '<span>' . $h($ini) . '</span>' : '<i class="fas fa-user"></i>') ?></div><?php endif; ?></div>
        <?php if (!$isLead): ?><div class="mb-av"><?= $x['photo'] ? '<img src="' . $h($x['photo']) . '" alt="" loading="lazy">' : ($ini ? '<span>' . $h($ini) . '</span>' : '<i class="fas fa-user"></i>') ?></div><?php endif; ?>
        <div class="mb-body">
          <?php if ($isLead): ?><div class="mb-tag"><i class="fas fa-star" style="color:var(--c)"></i> القيادة العليا</div><?php endif; ?>
          <h3 class="mb-name<?= $tbd ? ' tbd' : '' ?>"><?= $tbd ? 'سيُعلن قريبًا' : $h($x['name']) ?></h3>
          <span class="mb-role"><?= $h($x['title']) ?></span>
          <?php if ($x['spec']): ?><p class="mb-spec"><?= $h($x['spec']) ?></p><?php endif; ?>
          <span class="mb-more">الملف الكامل <i class="fas fa-arrow-left"></i></span>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <div class="mb-empty">لا توجد نتائج مطابقة.</div>
  </section>
  <?php endforeach; ?>
</div>

<!-- نافذة الملف -->
<dialog class="mb-dlg" id="mbView"><button class="mb-x" type="button" data-close aria-label="إغلاق">×</button><div class="mb-cover"></div><div class="mb-av"></div><div class="mb-body"><h3 class="mb-name"></h3><span class="mb-role"></span><p class="mb-spec"></p><p class="mb-bio"></p><div class="mb-links"></div></div></dialog>

<?php if ($SUPER): ?>
<!-- نافذة الإضافة/التعديل (للمشرف العام فقط) -->
<dialog class="mb-dlg" id="mbEdit"><button class="mb-x" type="button" data-close aria-label="إغلاق">×</button>
  <form class="mb-form" method="post" action="/admin/members.php" enctype="multipart/form-data">
    <h3 id="mbFt">إضافة عضو</h3>
    <input type="hidden" name="csrf" value="<?= $_SESSION['mem_csrf'] ?>"><input type="hidden" name="act" value="add"><input type="hidden" name="id" value=""><input type="hidden" name="back" value="">
    <div class="mb-fg">
      <label>الاسم الكامل<input name="name" placeholder="مثال: أ.د. عبد الله محمد"></label>
      <label>المنصب *<input name="title" required></label>
      <label>الفئة<select name="g"><?php foreach ($G as $k => $g): ?><option value="<?= $k ?>"><?= $g[0] ?></option><?php endforeach; ?></select></label>
      <label>التخصص أو الجهة<input name="spec"></label>
      <label class="w">نبذة مختصرة<textarea name="bio" rows="3"></textarea></label>
      <label>البريد الإلكتروني<input name="email" type="email" dir="ltr"></label>
      <label>الصورة (حتى 3MB)<input type="file" name="photo" accept="image/jpeg,image/png,image/webp"></label>
      <?php foreach ($LD as $k => $d): ?><label><span><i class="<?= $d[1] ?>"></i> <?= $d[0] ?></span><input name="links[<?= $k ?>]" dir="ltr" placeholder="https://"></label><?php endforeach; ?>
      <label class="chk w"><input type="checkbox" name="lead" value="1"> بطاقة قيادية كبيرة</label>
      <label class="chk w"><input type="checkbox" name="rmphoto" value="1"> حذف الصورة الحالية</label>
    </div>
    <div class="mb-fa"><button class="s" type="submit"><i class="fas fa-save"></i> حفظ</button><button class="c" type="button" data-close>إلغاء</button></div>
  </form>
</dialog>
<?php endif; ?>

<script>
(function(){
  var cats=document.querySelectorAll('.mb-cat'), q=document.getElementById('mbq'), intro=document.getElementById('mbIntro'), cur=<?= json_encode($cur) ?>;
  var LD=<?= json_encode(array_map(function ($d) { return $d[1]; }, $LD)) ?>;
  function norm(t){return (t||'').replace(/[أإآ]/g,'ا').replace(/ة/g,'ه').replace(/ى/g,'ي').toLowerCase();}
  function filter(){var v=norm(q.value.trim());var p=document.getElementById('mb-'+cur);var n=0;p.querySelectorAll('.mb-card').forEach(function(c){var ok=!v||norm(c.dataset.hay).indexOf(v)>-1;c.style.display=ok?'':'none';if(ok)n++;});p.querySelector('.mb-empty').style.display=n?'none':'block';}
  function show(g,scroll){cur=g;cats.forEach(function(c){c.classList.toggle('on',c.dataset.g===g)});document.querySelectorAll('.mb-pane').forEach(function(p){p.classList.toggle('on',p.id==='mb-'+g)});
    var p=document.getElementById('mb-'+g);intro.textContent=p.dataset.intro;intro.style.setProperty('--c',p.style.getPropertyValue('--c'));history.replaceState(null,'','?g='+g);filter();
    if(scroll)document.querySelector('.mb-cats').scrollIntoView({behavior:'smooth',block:'start'});}
  cats.forEach(function(c){c.onclick=function(){show(c.dataset.g)}});
  document.querySelectorAll('.mb-node').forEach(function(n){n.onclick=function(){show(n.dataset.go,true)}});
  q.addEventListener('input',filter);
  var esc=function(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML};
  var view=document.getElementById('mbView');
  function open(card){var m=JSON.parse(card.dataset.m), c=card.closest('.mb-pane').style.getPropertyValue('--c');view.style.setProperty('--c',c);
    var ini=(m.name||'').trim();view.querySelector('.mb-av').innerHTML=m.photo?'<img src="'+esc(m.photo)+'" alt="">':'<i class="fas fa-user"></i>';
    view.querySelector('.mb-name').textContent=m.name||'سيُعلن قريبًا';view.querySelector('.mb-name').classList.toggle('tbd',!m.name);
    view.querySelector('.mb-role').textContent=m.title;view.querySelector('.mb-spec').textContent=m.spec||'';
    view.querySelector('.mb-bio').innerHTML=m.bio?esc(m.bio).replace(/\n/g,'<br>'):'<span style="color:#9aa9a0">تُضاف السيرة المختصرة قريبًا.</span>';
    var L='';if(m.email)L+='<a href="mailto:'+esc(m.email)+'" aria-label="البريد"><i class="far fa-envelope"></i></a>';Object.keys(m.links||{}).forEach(function(k){if(m.links[k])L+='<a href="'+esc(m.links[k])+'" target="_blank" rel="noopener"><i class="'+(LD[k]||'fas fa-link')+'"></i></a>';});
    view.querySelector('.mb-links').innerHTML=L;view.showModal();}
  document.querySelectorAll('.mb-card').forEach(function(c){c.addEventListener('click',function(e){if(e.target.closest('[data-edit]'))return;open(c);});c.addEventListener('keydown',function(e){if(e.key==='Enter')open(c);});});
  document.querySelectorAll('[data-close]').forEach(function(b){b.onclick=function(){b.closest('dialog').close();}});
  document.querySelectorAll('dialog').forEach(function(d){d.addEventListener('click',function(e){if(e.target===d)d.close();});});
  var ed=document.getElementById('mbEdit');
  if(ed){var f=ed.querySelector('form');
    function fill(m){f.reset();f.act.value=m?'save':'add';f.id.value=m?m.id:'';f.back.value='/teacher/index.php?g='+(m?m.g:cur);document.getElementById('mbFt').textContent=m?'تعديل بيانات العضو':'إضافة عضو جديد';
      f.g.value=m?m.g:cur;['name','title','spec','bio','email'].forEach(function(k){f[k].value=m?(m[k]||''):''});
      Object.keys(LD).forEach(function(k){var i=f.querySelector('[name="links['+k+']"]');if(i)i.value=m&&m.links&&m.links[k]?m.links[k]:'';});f.lead.checked=!!(m&&m.lead);ed.showModal();}
    document.getElementById('mbAdd').onclick=function(){fill(null)};
    document.querySelectorAll('[data-edit]').forEach(function(b){b.onclick=function(e){e.stopPropagation();fill(JSON.parse(b.closest('.mb-card').dataset.m));}});
  }
})();
</script>
<?php uwciv_close(); ?>
