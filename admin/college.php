<?php
/* إدارة الكليات — بيانات كل كلية وأقسامها وبرامجها */
require_once dirname(__DIR__) . '/inc/admin_ui.php';
uwciv_admin_gate(['title' => 'الكليات', 'perm' => 'colleges']);
require_once dirname(__DIR__) . '/inc/colleges.php';
$msg = ''; $err = '';
$slug = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['c'] ?? ''));
if ($slug !== '' && !isset($UWCIV_COLLEGES[$slug])) $slug = '';
$me = uwciv_staff_me(); $only = (!uwciv_is_super() && $me && !empty($me['college'])) ? $me['college'] : '';
if ($only && $slug !== $only) { header('Location: college.php?c=' . rawurlencode($only)); exit; }
$TABS = ['id' => ['fas fa-id-card', 'الهوية والتعريف'], 'deps' => ['fas fa-sitemap', 'الأقسام العلمية'], 'st' => ['fas fa-layer-group', 'المراحل والبرامج'], 'jobs' => ['fas fa-briefcase', 'المسارات المهنية'], 'dean' => ['fas fa-user-tie', 'العمادة والتواصل'], 'adm' => ['fas fa-clipboard-check', 'شروط القبول']];
$tab = isset($TABS[$_GET['t'] ?? '']) ? $_GET['t'] : 'id';
$clean = function ($v, $n) { return trim(mb_substr(str_replace(['<', '>', '"'], ['', '', '”'], preg_replace('/\s+/u', ' ', (string) $v)), 0, $n)); };
$lines = function ($v, $n, $max) use ($clean) { $o = []; foreach (preg_split('/\r?\n/', (string) $v) as $l) { $l = $clean($l, $n); if ($l !== '') $o[] = $l; } return array_slice($o, 0, $max); };
$ico = function ($v) { $v = trim((string) $v); $v = preg_replace('/^(fas|far|fab) /', '', $v); return preg_match('/^fa-[a-z0-9-]+$/', $v) ? $v : 'fa-book'; };

if ($slug && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!uwciv_csrf_ok()) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    else {
        $ALL = uwciv_colleges_overrides(); $ov = $ALL[$slug] ?? []; $t = $_POST['tab'] ?? '';
        if (($_POST['act'] ?? '') === 'reset') { unset($ALL[$slug]); $msg = 'أُعيدت بيانات الكلية إلى الوضع الافتراضي.'; }
        elseif ($t === 'id') {
            $ov['n'] = $clean($_POST['n'] ?? '', 90) ?: $UWCIV_COLLEGES_DEFAULT[$slug]['n'];
            $ov['tag'] = $clean($_POST['tag'] ?? '', 120); $ov['about'] = $clean($_POST['about'] ?? '', 1500);
            $ov['vision'] = $clean($_POST['vision'] ?? '', 500); $ov['mission'] = $clean($_POST['mission'] ?? '', 600);
            $ov['goals'] = $lines($_POST['goals'] ?? '', 200, 12); $ov['i'] = $ico($_POST['i'] ?? '');
            $c = (string) ($_POST['c'] ?? ''); if (preg_match('/^#[0-9a-f]{6}$/i', $c)) $ov['c'] = $c;
        } elseif ($t === 'deps') {
            $d = []; foreach ((array) ($_POST['dep'] ?? []) as $x) { $n = $clean($x['n'] ?? '', 90); if ($n !== '') { $row = [$n, $clean($x['d'] ?? '', 300), $ico($x['i'] ?? '')]; $u = (string) ($x['u'] ?? ''); if (preg_match('~^/[A-Za-z0-9/_.?=&-]*$~', $u)) $row[] = $u; $d[] = $row; } }
            if (!$d) $err = 'أضف قسمًا واحدًا على الأقل.'; else $ov['deps'] = array_slice($d, 0, 20);
        } elseif ($t === 'st') {
            $s = array_values(array_intersect(array_keys($UWCIV_STAGES), (array) ($_POST['st'] ?? [])));
            if (!$s) $err = 'اختر مرحلة واحدة على الأقل.'; else { $ov['st'] = $s; $ov['hours'] = $clean($_POST['hours'] ?? '', 200); $ov['fees_note'] = $clean($_POST['fees_note'] ?? '', 300); }
        } elseif ($t === 'jobs') { $ov['jobs'] = $lines($_POST['jobs'] ?? '', 120, 16); }
        elseif ($t === 'dean') {
            $ov['dean'] = $clean($_POST['dean'] ?? '', 90); $ov['dean_msg'] = $clean($_POST['dean_msg'] ?? '', 1200);
            $e = trim((string) ($_POST['email'] ?? '')); $ov['email'] = filter_var($e, FILTER_VALIDATE_EMAIL) ? $e : '';
            $ov['phone'] = preg_replace('/[^\d+]/', '', (string) ($_POST['phone'] ?? ''));
        } elseif ($t === 'adm') { $ov['admission'] = $lines($_POST['admission'] ?? '', 220, 15); }
        if (!$err && ($_POST['act'] ?? '') !== 'reset') { $ALL[$slug] = $ov; $msg = 'تم حفظ «' . $TABS[$t][1] . '» ونشره في صفحة الكلية.'; }
        if (!$err) {
            $f = uwciv_colleges_override_file();
            if (@file_put_contents($f, json_encode($ALL, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) === false) $err = 'تعذّر الحفظ على الخادم.';
            else { uwciv_staff_log('عدّل بيانات ' . $UWCIV_COLLEGES[$slug]['n'] . ' — ' . ($TABS[$t][1] ?? 'إعادة افتراضي')); header('Location: college.php?c=' . $slug . '&t=' . (isset($TABS[$t]) ? $t : 'id') . '&ok=' . rawurlencode($msg)); exit; }
        }
    }
}
if (isset($_GET['ok'])) $msg = (string) $_GET['ok'];
$h = 'uwciv_h'; $OVS = uwciv_colleges_overrides();
$score = function ($s) use ($UWCIV_COLLEGES) { $c = $UWCIV_COLLEGES[$s]; $p = 0; foreach ([!empty($c['dean']), !empty($c['admission']), !empty($c['email']) || !empty($c['phone']), count($c['deps']) >= 3, !empty($c['dean_msg'])] as $x) $p += $x ? 20 : 0; return $p; };

if (!$slug):
    [$nc, $nd] = uwciv_college_counts();
    uwciv_admin_open('colleges', 'الكليات', 'اختر كلية لتعديل تعريفها وأقسامها ومراحلها ومساراتها المهنية وعمادتها وشروط القبول — تظهر التعديلات فورًا في صفحة الكلية ودليل الكليات.', '<a class="ad-b g" href="/colleges.php" target="_blank"><i class="fas fa-eye"></i> دليل الكليات</a>');
    uwciv_admin_toast($err ?: $msg, (bool) $err);
?>
<style>
.cg-h{display:flex;gap:10px;align-items:center;margin:20px 0 12px;font-size:18px;font-weight:900;color:var(--ink)}
.cg-h i{width:36px;height:36px;border-radius:11px;display:grid;place-items:center;color:#fff;background:var(--gc)}
.cg-h small{color:var(--muted);font-size:13px;font-weight:700}
.cg{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px}
.cgc{background:#fff;border-radius:20px;box-shadow:var(--sh-1);padding:16px;display:flex;flex-direction:column;gap:10px;text-decoration:none!important;color:var(--text)!important;border-top:5px solid var(--c);transition:.25s}
.cgc:hover{transform:translateY(-4px);box-shadow:var(--sh-lift)}
.cgc-t{display:flex;gap:12px;align-items:center}
.cgc-t i{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;color:#fff;background:linear-gradient(145deg,var(--c),color-mix(in srgb,var(--c) 55%,#000));font-size:19px;flex:none}
.cgc-t b{color:var(--ink);font-size:15px;line-height:1.5}
.cgc-m{display:flex;gap:6px;flex-wrap:wrap}.cgc-m span{background:var(--mist);border-radius:8px;padding:3px 8px;font-size:12px;font-weight:700;color:var(--ink-3)}
.cgc-p{height:6px;border-radius:6px;background:#e6efe9;overflow:hidden}.cgc-p i{display:block;height:100%;background:var(--c)}
.cgc small{color:var(--muted);font-size:12px}
</style>
<div class="cp-kpis">
  <div class="cp-kpi" style="--c:#1f7a45"><i class="fas fa-university"></i><span><b><?= $nc ?></b><span>كلية</span></span></div>
  <div class="cp-kpi" style="--c:#2c5fd0"><i class="fas fa-sitemap"></i><span><b><?= $nd ?></b><span>أقسام وبرامج</span></span></div>
  <div class="cp-kpi" style="--c:#c9962a"><i class="fas fa-user-tie"></i><span><b><?= count(array_filter($UWCIV_COLLEGES, function ($c) { return !empty($c['dean']); })) ?>/<?= $nc ?></b><span>كليات بعميد مسجّل</span></span></div>
  <div class="cp-kpi" style="--c:#9b1f45"><i class="fas fa-pen"></i><span><b><?= count($OVS) ?></b><span>كلية معدّلة</span></span></div>
</div>
<div class="ad-bar"><div class="ad-in"><i class="fas fa-search"></i><input placeholder="ابحث باسم الكلية أو القسم…" data-filter="#cgall"></div></div>
<div id="cgall">
<?php foreach ($UWCIV_GROUPS as $gk => $g): ?>
  <div class="cg-h" style="--gc:<?= $g[2] ?>"><i class="fas <?= $g[1] ?>"></i><?= $g[0] ?></div>
  <div class="cg"><?php foreach ($UWCIV_COLLEGES as $s => $c): if ($c['g'] !== $gk) continue; $p = $score($s); ?>
    <a class="cgc fi" href="?c=<?= $s ?>" style="--c:<?= $c['c'] ?>" data-s="<?= $h($c['n'] . ' ' . implode(' ', array_column($c['deps'], 0))) ?>">
      <div class="cgc-t"><i class="fas <?= $c['i'] ?>"></i><b><?= $h($c['n']) ?></b></div>
      <div class="cgc-m"><span><?= count($c['deps']) ?> أقسام</span><span><?= count($c['st']) ?> مراحل</span><?= !empty($c['dean']) ? '<span><i class="fas fa-user-tie"></i> ' . $h($c['dean']) . '</span>' : '' ?><?= isset($OVS[$s]) ? '<span style="background:#e4f4ea;color:#1f7a45">معدّلة</span>' : '' ?></div>
      <div class="cgc-p" title="اكتمال البيانات"><i style="width:<?= max(4, $p) ?>%"></i></div><small>اكتمال بيانات العمادة والقبول: <?= $p ?>%</small>
    </a>
  <?php endforeach; ?></div>
<?php endforeach; ?>
</div>
<?php uwciv_admin_close(); exit; endif;

$c = $UWCIV_COLLEGES[$slug]; $g = $UWCIV_GROUPS[$c['g']];
uwciv_admin_open('colleges', $c['n'], $h($g[0]) . ' — ' . $h($c['tag']), '<a class="ad-b g" href="/college/' . $slug . '" target="_blank"><i class="fas fa-eye"></i> صفحة الكلية</a>' . ($only ? '' : '<a class="ad-b g" href="college.php"><i class="fas fa-th"></i> كل الكليات</a>'));
uwciv_admin_toast($err ?: $msg, (bool) $err);
$v = function ($k) use ($c, $h) { return $h(is_array($c[$k] ?? '') ? implode("\n", $c[$k]) : ($c[$k] ?? '')); };
?>
<style>
.cl-hd{display:flex;gap:16px;align-items:center;border-radius:24px;padding:20px 22px;color:#fff;margin-bottom:16px;background:radial-gradient(400px 200px at 100% 0,rgba(255,255,255,.18),transparent 60%),linear-gradient(135deg,var(--c),color-mix(in srgb,var(--c) 45%,#000));position:relative;overflow:hidden;isolation:isolate}
.cl-hd:before{content:"";position:absolute;inset:0;z-index:-1;background:url(/assets/images/pattern-islamic.svg) 0 0/72px;opacity:.15}
.cl-hd>i{width:64px;height:64px;border-radius:20px;display:grid;place-items:center;background:rgba(255,255,255,.16);font-size:28px;flex:none}
.cl-hd b{display:block;font-size:22px;font-weight:900}.cl-hd span{opacity:.85}
.cl-hd .st{margin-inline-start:auto;display:flex;gap:6px;flex-wrap:wrap}
.cl-hd .st em{font-style:normal;background:rgba(255,255,255,.15);border-radius:10px;padding:5px 10px;font-weight:800;font-size:12.5px}
.cl-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.cl-tabs a{display:inline-flex;gap:8px;align-items:center;border-radius:14px;padding:10px 15px;font-weight:800;background:#fff;color:var(--ink-3)!important;box-shadow:var(--sh-1);text-decoration:none!important}
.cl-tabs a.on{background:var(--c);color:#fff!important}
.dp{display:grid;grid-template-columns:44px minmax(0,1fr) minmax(0,1.6fr) auto;gap:8px;align-items:center;margin-bottom:8px}
.dp input{border:1.5px solid #dbe7df;border-radius:10px;height:42px;padding:0 10px;font-family:inherit;font-size:14px;min-width:0;width:100%;box-sizing:border-box}
.dp .ic{width:42px;height:42px;border-radius:11px;display:grid;place-items:center;background:var(--cyan-soft);color:var(--teal)}
.dp .bt{display:flex;gap:3px}.dp .bt button{border:0;width:32px;height:32px;border-radius:9px;background:var(--mist);cursor:pointer;color:var(--ink-3)}.dp .bt .rm{background:#fdecef;color:#c0392b}
.stg{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.stg label{position:relative}.stg input{position:absolute;opacity:0;inset:0;margin:0;cursor:pointer}
.stg span{display:flex;flex-direction:column;gap:4px;border:2px solid var(--ad-line);border-radius:16px;padding:14px;height:100%;transition:.2s}
.stg span b{color:var(--ink)}.stg span small{color:var(--muted);font-size:12.5px;line-height:1.7}
.stg input:checked+span{border-color:var(--sc);background:color-mix(in srgb,var(--sc) 8%,#fff)}
.stg input:checked+span b:before{content:"✓ ";color:var(--sc)}
@media(max-width:800px){.stg{grid-template-columns:1fr 1fr}.dp{grid-template-columns:44px minmax(0,1fr) auto}.dp input[name$="[d]"]{grid-column:2/3}}
</style>
<div class="cl-hd" style="--c:<?= $c['c'] ?>"><i class="fas <?= $c['i'] ?>"></i><div><b><?= $h($c['n']) ?></b><span><?= $h($c['tag']) ?></span></div><div class="st"><em><?= count($c['deps']) ?> أقسام</em><?php foreach ($c['st'] as $s): ?><em><?= $UWCIV_STAGES[$s][0] ?></em><?php endforeach; ?></div></div>
<nav class="cl-tabs" style="--c:<?= $c['c'] ?>"><?php foreach ($TABS as $k => $tb): ?><a href="?c=<?= $slug ?>&t=<?= $k ?>" class="<?= $tab === $k ? 'on' : '' ?>"><i class="<?= $tb[0] ?>"></i><?= $tb[1] ?></a><?php endforeach; ?></nav>
<form method="post" class="cp-card"><?= uwciv_csrf_field() ?><input type="hidden" name="tab" value="<?= $tab ?>">
<?php if ($tab === 'id'): ?>
  <h2><i class="fas fa-id-card"></i> الهوية والتعريف</h2><p>الاسم والشعار النصي والنبذة والرؤية والرسالة والأهداف كما تظهر في رأس صفحة الكلية.</p>
  <div class="cp-f">
    <label class="w">اسم الكلية<div class="cp-in"><i class="fas fa-university"></i><input name="n" value="<?= $v('n') ?>" maxlength="90"></div></label>
    <label>الشعار النصي (سطر واحد)<div class="cp-in"><i class="fas fa-quote-right"></i><input name="tag" value="<?= $v('tag') ?>" maxlength="120"></div></label>
    <label>الأيقونة واللون<div class="cp-in"><i class="fas <?= $c['i'] ?>" id="ip"></i><input name="i" value="<?= $v('i') ?>" dir="ltr" placeholder="fa-quran" oninput="document.getElementById('ip').className='fas '+this.value"><input type="color" name="c" value="<?= $h($c['c']) ?>" style="flex:0 0 44px;height:34px;padding:0!important;border-radius:8px"></div></label>
    <label class="w">نبذة عن الكلية<div class="cp-in ta"><i class="fas fa-align-right"></i><textarea name="about" maxlength="1500"><?= $v('about') ?></textarea></div></label>
    <label>الرؤية<div class="cp-in ta"><i class="fas fa-eye"></i><textarea name="vision" style="min-height:90px"><?= $v('vision') ?></textarea></div></label>
    <label>الرسالة<div class="cp-in ta"><i class="fas fa-bullseye"></i><textarea name="mission" style="min-height:90px"><?= $v('mission') ?></textarea></div></label>
    <label class="w">الأهداف (هدف في كل سطر)<div class="cp-in ta"><i class="fas fa-flag-checkered"></i><textarea name="goals"><?= $v('goals') ?></textarea></div></label>
  </div>
<?php elseif ($tab === 'deps'): ?>
  <h2><i class="fas fa-sitemap"></i> الأقسام العلمية <small style="color:var(--muted);font-weight:700;font-size:13px">(<?= count($c['deps']) ?>)</small></h2><p>اسم كل قسم ووصفه وأيقونته — تظهر في تبويب «الأقسام» بصفحة الكلية وفي دليل الكليات ونماذج التسجيل.</p>
  <div id="dps"><?php foreach ($c['deps'] as $i => $d): ?>
    <div class="dp"><span class="ic"><i class="fas <?= $h($d[2]) ?>"></i></span><input name="dep[<?= $i ?>][n]" value="<?= $h($d[0]) ?>" placeholder="اسم القسم"><input name="dep[<?= $i ?>][d]" value="<?= $h($d[1]) ?>" placeholder="وصف القسم"><span class="bt"><input type="hidden" name="dep[<?= $i ?>][i]" value="<?= $h($d[2]) ?>"><input type="hidden" name="dep[<?= $i ?>][u]" value="<?= $h($d[3] ?? '') ?>"><button type="button" data-u title="أعلى"><i class="fas fa-arrow-up"></i></button><button type="button" class="rm" data-r title="حذف"><i class="fas fa-trash"></i></button></span></div>
  <?php endforeach; ?></div>
  <button type="button" class="cp-b g" id="dpa"><i class="fas fa-plus"></i> قسم جديد</button>
<?php elseif ($tab === 'st'): ?>
  <h2><i class="fas fa-layer-group"></i> المراحل والبرامج</h2><p>المراحل الدراسية المتاحة في الكلية، وملاحظات الساعات والرسوم.</p>
  <div class="stg"><?php foreach ($UWCIV_STAGES as $sk => $s): ?><label><input type="checkbox" name="st[]" value="<?= $sk ?>" <?= in_array($sk, $c['st'], true) ? 'checked' : '' ?>><span style="--sc:<?= $s[1] ?>"><b><?= $s[0] ?></b><small><?= $h($s[2]) ?> · <?= $h($s[3]) ?></small></span></label><?php endforeach; ?></div>
  <div class="cp-f" style="margin-top:14px">
    <label>ملاحظة الساعات والمدة<div class="cp-in"><i class="far fa-clock"></i><input name="hours" value="<?= $v('hours') ?>" placeholder="مثال: البكالوريوس 136 ساعة في 4 سنوات"></div></label>
    <label>ملاحظة الرسوم والمنح<div class="cp-in"><i class="fas fa-coins"></i><input name="fees_note" value="<?= $v('fees_note') ?>" placeholder="مثال: خصم 20% لحفظة القرآن"></div></label>
  </div>
<?php elseif ($tab === 'jobs'): ?>
  <h2><i class="fas fa-briefcase"></i> المسارات المهنية</h2><p>مجالات العمل المتاحة لخريجي الكلية — مسار في كل سطر.</p>
  <div class="cp-f"><label class="w"><div class="cp-in ta"><i class="fas fa-user-check"></i><textarea name="jobs" style="min-height:220px"><?= $v('jobs') ?></textarea></div></label></div>
<?php elseif ($tab === 'dean'): ?>
  <h2><i class="fas fa-user-tie"></i> العمادة والتواصل</h2><p>تظهر في صفحة الكلية كـ«كلمة العميد» وبيانات التواصل المباشر مع الكلية.</p>
  <div class="cp-f">
    <label>اسم عميد الكلية<div class="cp-in"><i class="fas fa-user-tie"></i><input name="dean" value="<?= $v('dean') ?>" placeholder="أ.د. …"></div></label>
    <label>بريد الكلية<div class="cp-in"><i class="far fa-envelope"></i><input type="email" name="email" value="<?= $v('email') ?>" dir="ltr"></div></label>
    <label>واتساب الكلية<div class="cp-in"><i class="fab fa-whatsapp"></i><input name="phone" value="<?= $v('phone') ?>" dir="ltr" placeholder="+880…"></div></label>
    <label class="w">كلمة العميد<div class="cp-in ta"><i class="fas fa-quote-right"></i><textarea name="dean_msg" style="min-height:150px"><?= $v('dean_msg') ?></textarea></div></label>
  </div>
<?php else: ?>
  <h2><i class="fas fa-clipboard-check"></i> شروط القبول</h2><p>شرط في كل سطر — تظهر في صفحة الكلية قبل زر «سجّل الآن».</p>
  <div class="cp-f"><label class="w"><div class="cp-in ta"><i class="fas fa-list-ol"></i><textarea name="admission" style="min-height:220px" placeholder="شهادة الثانوية أو ما يعادلها&#10;اجتياز المقابلة الشخصية…"><?= $v('admission') ?></textarea></div></label></div>
<?php endif; ?>
  <div class="cp-btns"><button class="cp-b p"><i class="fas fa-save"></i> حفظ ونشر</button><a class="cp-b g" href="/college/<?= $slug ?>" target="_blank"><i class="fas fa-eye"></i> معاينة</a><?php if (isset($OVS[$slug])): ?><button class="cp-b r" name="act" value="reset" formnovalidate data-confirm="إعادة كل بيانات هذه الكلية إلى الوضع الافتراضي؟"><i class="fas fa-undo"></i> الافتراضي</button><?php endif; ?></div>
</form>
<script>
(function(){var box=document.getElementById('dps');if(!box)return;
function ren(){[].forEach.call(box.children,function(r,i){r.querySelectorAll('input').forEach(function(x){x.name=x.name.replace(/dep\[\d+\]/,'dep['+i+']')});});}
box.addEventListener('click',function(e){var r=e.target.closest('.dp');if(!r)return;if(e.target.closest('[data-r]')&&box.children.length>1){r.remove();ren();}if(e.target.closest('[data-u]')&&r.previousElementSibling){r.previousElementSibling.before(r);ren();}});
document.getElementById('dpa').onclick=function(){var r=box.children[0].cloneNode(true);r.querySelectorAll('input').forEach(function(x){x.value=x.type==='hidden'?'fa-book':''});r.querySelector('.ic i').className='fas fa-book';box.appendChild(r);ren();r.querySelector('input').focus();};
document.querySelector('form [data-confirm]')&&0;})();
</script>
<?php uwciv_admin_close(); ?>
