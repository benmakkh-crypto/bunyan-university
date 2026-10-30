<?php
require_once __DIR__ . '/inc/shell.php';
require_once __DIR__ . '/inc/colleges.php';

$slug = preg_replace('/[^a-z-]/', '', (string) ($_GET['c'] ?? ''));
if (!isset($UWCIV_COLLEGES[$slug])) { header('Location: /colleges.php'); exit; }
$c = $UWCIV_COLLEGES[$slug];
$g = $UWCIV_GROUPS[$c['g']];
$keys = array_keys($UWCIV_COLLEGES);
$pos = array_search($slug, $keys, true);
$prev = $keys[($pos - 1 + count($keys)) % count($keys)];
$next = $keys[($pos + 1) % count($keys)];
$stageOrder = ['bac' => 0, 'mas' => 1, 'phd' => 2];
$stagePages = ['dip' => '/depl.php', 'bac' => '/depm.php', 'mas' => '/depmst.php', 'phd' => '/depdec.php'];

uwciv_open($c['n'], t('colleges_m'), $c['tag']);
?>
<link rel="stylesheet" href="/assets/css/uwciv-colleges.css?v=1">
<div style="--cc:<?= $c['c'] ?>">

<div class="c-hero">
  <div class="c-ico"><i class="fas <?= $c['i'] ?>"></i></div>
  <div style="position:relative;z-index:1">
    <small><i class="fas <?= $g[1] ?>"></i> مجموعة <?= $g[0] ?> · الكلية <?= $pos + 1 ?> من <?= count($keys) ?></small>
    <h2>نبذة عن الكلية</h2>
    <p><?= $c['about'] ?></p>
  </div>
  <div class="c-cta">
    <a class="c-btn p" href="/student/index.php?c=<?= $slug ?>"><i class="fas fa-user-plus"></i> قدّم الآن</a>
    <a class="c-btn g" href="/form2.php"><i class="fas fa-headset"></i> استفسار</a>
  </div>
</div>

<div class="c-facts">
  <div class="c-fact"><i class="fas fa-sitemap"></i><div><b><?= count($c['deps']) ?></b><span>أقسام علمية</span></div></div>
  <div class="c-fact"><i class="fas fa-layer-group"></i><div><b><?= count($c['st']) ?></b><span>مراحل دراسية</span></div></div>
  <div class="c-fact"><i class="fas fa-briefcase"></i><div><b><?= count($c['jobs']) ?>+</b><span>مسارات مهنية</span></div></div>
  <div class="c-fact"><i class="fas fa-laptop-house"></i><div><b>عن بُعد</b><span>ومدمج حسب البرنامج</span></div></div>
</div>

<div class="c-tabs" role="tablist">
  <button class="c-tab on" data-t="about"><i class="fas fa-info-circle"></i> عن الكلية</button>
  <button class="c-tab" data-t="deps"><i class="fas fa-sitemap"></i> الأقسام</button>
  <button class="c-tab" data-t="progs"><i class="fas fa-graduation-cap"></i> البرامج والقبول</button>
  <button class="c-tab" data-t="jobs"><i class="fas fa-briefcase"></i> مجالات العمل</button>
  <button class="c-tab" data-t="links"><i class="fas fa-link"></i> روابط الكلية</button>
</div>

<div class="c-pane on" id="p-about">
  <div class="c-two">
    <div class="c-box"><h3><i class="fas fa-eye"></i> الرؤية</h3><p><?= $c['vision'] ?></p></div>
    <div class="c-box"><h3><i class="fas fa-bullseye"></i> الرسالة</h3><p><?= $c['mission'] ?></p></div>
  </div>
  <div class="c-box" style="margin-top:16px"><h3><i class="fas fa-flag-checkered"></i> أهداف الكلية</h3>
    <ul class="c-goals"><?php foreach ($c['goals'] as $x): ?><li><i class="fas fa-check-circle"></i><?= $x ?></li><?php endforeach; ?></ul>
  </div>
  <?php if (!empty($c['dean']) || !empty($c['dean_msg'])): ?>
  <div class="c-box c-dean" style="margin-top:16px"><h3><i class="fas fa-user-tie"></i> كلمة عميد الكلية</h3>
    <?php if (!empty($c['dean_msg'])): ?><p style="white-space:pre-line"><?= htmlspecialchars($c['dean_msg']) ?></p><?php endif; ?>
    <?php if (!empty($c['dean'])): ?><p style="margin:10px 0 0;font-weight:900;color:var(--cc)">— <?= htmlspecialchars($c['dean']) ?>، عميد <?= htmlspecialchars($c['n']) ?></p><?php endif; ?>
  </div><?php endif; ?>
  <?php if (!empty($c['email']) || !empty($c['phone'])): ?>
  <div class="c-box" style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:center"><h3 style="margin:0 0 0 10px"><i class="fas fa-headset"></i> تواصل مع الكلية</h3>
    <?php if (!empty($c['phone'])): ?><a class="c-btn" style="background:var(--mist);color:var(--ink-3)" href="https://wa.me/<?= preg_replace('/\D/', '', $c['phone']) ?>" target="_blank" rel="noopener" dir="ltr"><i class="fab fa-whatsapp"></i> <?= htmlspecialchars($c['phone']) ?></a><?php endif; ?>
    <?php if (!empty($c['email'])): ?><a class="c-btn" style="background:var(--mist);color:var(--ink-3)" href="mailto:<?= htmlspecialchars($c['email']) ?>" dir="ltr"><i class="far fa-envelope"></i> <?= htmlspecialchars($c['email']) ?></a><?php endif; ?>
  </div><?php endif; ?>
</div>

<div class="c-pane" id="p-deps">
  <div class="c-deps">
    <?php foreach ($c['deps'] as $i => $d): ?>
    <?php $dn = preg_match('/^(قسم|دبلوم|مرحلة|المرحلة|برنامج)/u', $d[0]) ? $d[0] : 'قسم ' . $d[0]; ?>
    <div class="c-dep"><span class="n"><?= $i + 1 ?></span><i class="fas <?= $d[2] ?> d"></i><h4><?= $dn ?></h4><p><?= $d[1] ?></p>
      <?php if (!empty($d[3])): ?><a href="<?= htmlspecialchars($d[3]) ?>" style="display:inline-flex;gap:6px;align-items:center;font-weight:800;font-size:13px;color:var(--cc);margin:2px 0 8px"><i class="fas fa-arrow-left"></i> التفاصيل الكاملة</a><?php endif; ?>
      <div class="k-deg"><?php foreach ($c['st'] as $s): ?><span style="--sc:<?= $UWCIV_STAGES[$s][1] ?>"><?= $UWCIV_STAGES[$s][0] ?></span><?php endforeach; ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="c-pane" id="p-progs">
  <?php require_once __DIR__ . '/inc/curriculum.php'; $__cp = array_filter(cur_doc()['programs'], function ($p) use ($slug) { return $p['college'] === $slug; }); if ($__cp): ?>
  <div class="c-box" style="margin-bottom:16px"><h3><i class="fas fa-project-diagram"></i> الخطط الدراسية والكتب</h3>
    <div class="c-links" style="margin-top:8px"><?php foreach ($__cp as $__s => $__p): $__st = cur_program_stats($__p); ?><a href="/plan.php?p=<?= cur_h($__s) ?>" style="--sc:<?= $UWCIV_STAGES[$__p['stage']][1] ?? '#1f7a45' ?>"><i class="fas fa-layer-group"></i><span><?= cur_h($__p['n']) ?><small><?= count($__p['levels']) ?> مستويات · <?= $__st['total'] ?> <?= $__p['unit'] === 'cr' ? 'ساعة معتمدة' : 'ساعة اتصال' ?> · <?= $__st['courses'] ?> مقررًا</small></span></a><?php endforeach; ?></div>
  </div>
  <?php endif; ?>
  <table class="c-table">
    <thead><tr><th>المرحلة</th><th>المدة</th><th>متطلبات التخرج</th><th>شروط القبول</th></tr></thead>
    <tbody>
    <?php foreach ($UWCIV_STAGES as $sk => $s): $on = in_array($sk, $c['st'], true); ?>
      <tr class="<?= $on ? '' : 'off' ?>" style="--sc:<?= $s[1] ?>">
        <td><span class="sd"></span><?= $s[0] ?><?= $on ? '' : ' <small style="font-weight:500">(غير متاح حاليًا)</small>' ?></td>
        <td><?= $s[2] ?></td><td><?= $s[3] ?></td><td><?= $s[4] ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (!empty($c['admission'])): ?><div class="c-box" style="margin-top:16px"><h3><i class="fas fa-clipboard-check"></i> شروط القبول في الكلية</h3><ul class="c-goals"><?php foreach ($c['admission'] as $x): ?><li><i class="fas fa-check-circle"></i><?= htmlspecialchars($x) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
  <?php if (!empty($c['hours']) || !empty($c['fees_note'])): ?><div class="c-two" style="margin-top:16px"><?php if (!empty($c['hours'])): ?><div class="c-box"><h3><i class="far fa-clock"></i> الساعات والمدة</h3><p><?= htmlspecialchars($c['hours']) ?></p></div><?php endif; ?><?php if (!empty($c['fees_note'])): ?><div class="c-box"><h3><i class="fas fa-coins"></i> الرسوم والمنح</h3><p><?= htmlspecialchars($c['fees_note']) ?></p></div><?php endif; ?></div><?php endif; ?>
  <div class="u-note-box" style="margin-top:14px"><i class="fas fa-info-circle"></i> يُفتح كل برنامج بعد استكمال متطلبات الترخيص والاعتماد، والتفاصيل النهائية في <a href="/regulations.php">اللوائح والأنظمة</a> و<a href="/regulations-view.php?d=guide-<?= $slug ?>" target="_blank">دليل الكلية</a> و<a href="/calendar.php">التقويم الدراسي</a>.</div>
</div>

<div class="c-pane" id="p-jobs">
  <div class="c-jobs"><?php foreach ($c['jobs'] as $j): ?><span><i class="fas fa-user-check"></i><?= $j ?></span><?php endforeach; ?></div>
</div>

<div class="c-pane" id="p-links">
  <div class="c-links">
    <?php if (!empty($c['legacy'])):
      foreach (['bac', 'mas', 'phd'] as $sk): $ix = $stageOrder[$sk]; $sc = $UWCIV_STAGES[$sk][1]; ?>
      <a href="/<?= $c['legacy']['p'][$ix] ?>.php" style="--sc:<?= $sc ?>"><i class="fas fa-university"></i><span>صفحة الكلية — <?= $UWCIV_STAGES[$sk][0] ?><small>الخطة والمقررات</small></span></a>
      <a href="/<?= $c['legacy']['l'][$ix] ?>.php" style="--sc:<?= $sc ?>"><i class="fas fa-play-circle"></i><span>محاضرات <?= $UWCIV_STAGES[$sk][0] ?><small>المحاضرات المسجلة</small></span></a>
    <?php endforeach; else:
      foreach ($c['st'] as $sk): ?>
      <a href="<?= $stagePages[$sk] ?>" style="--sc:<?= $UWCIV_STAGES[$sk][1] ?>"><i class="fas fa-layer-group"></i><span>مرحلة <?= $UWCIV_STAGES[$sk][0] ?><small>نظام المرحلة وكلياتها</small></span></a>
    <?php endforeach; endif; ?>
    <a href="/regulations-view.php?d=guide-<?= $slug ?>" target="_blank"><i class="fas fa-book"></i><span>دليل الكلية<small>نسخة قابلة للطباعة PDF</small></span></a>
    <a href="/education.php"><i class="fas fa-laptop"></i><span>المنصة التعليمية<small>الدروس والمقررات</small></span></a>
    <a href="/onlineexam/index.php"><i class="fas fa-file-signature"></i><span>الامتحان الإلكتروني<small>اختبارات الطلاب</small></span></a>
    <a href="/librarysystem/index.php"><i class="fas fa-book"></i><span>المكتبة الرقمية<small>مراجع التخصص</small></span></a>
    <a href="/teacher/index.php"><i class="fas fa-chalkboard-teacher"></i><span>أعضاء هيئة التدريس<small>أساتذة الجامعة</small></span></a>
    <a href="/calendar.php"><i class="fas fa-calendar-alt"></i><span>التقويم الدراسي<small>المواعيد المهمة</small></span></a>
    <a href="/student/index.php?c=<?= $slug ?>"><i class="fas fa-user-plus"></i><span>طلب الالتحاق<small>سجّل في الكلية</small></span></a>
  </div>
</div>

<div class="c-sib">
  <h3>كليات أخرى في مجموعة <?= $g[0] ?></h3>
  <div class="k-grid">
    <?php foreach ($UWCIV_COLLEGES as $sk => $o): if ($o['g'] !== $c['g'] || $sk === $slug) continue; ?>
    <a class="k-card" href="<?= uwciv_college_url($sk) ?>" style="--cc:<?= $o['c'] ?>">
      <div class="k-top" style="min-height:0"><i class="fas <?= $o['i'] ?>"></i><h3><?= $o['n'] ?></h3></div>
      <div class="k-body"><div class="k-foot" style="border:0;padding:0"><span><b><?= count($o['deps']) ?></b> أقسام</span><span class="go"><i class="fas fa-arrow-left"></i></span></div></div>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<div class="c-nav">
  <a href="<?= uwciv_college_url($prev) ?>"><i class="fas fa-arrow-right"></i><span><small>الكلية السابقة</small><?= $UWCIV_COLLEGES[$prev]['n'] ?></span></a>
  <a href="/colleges.php" style="flex:0 0 auto"><i class="fas fa-th-large"></i> كل الكليات</a>
  <a class="nx" href="<?= uwciv_college_url($next) ?>"><span><small>الكلية التالية</small><?= $UWCIV_COLLEGES[$next]['n'] ?></span><i class="fas fa-arrow-left"></i></a>
</div>
</div>

<script>
(function(){
  var tabs=document.querySelectorAll('.c-tab');
  function show(t){tabs.forEach(function(b){b.classList.toggle('on',b.dataset.t===t)});document.querySelectorAll('.c-pane').forEach(function(p){p.classList.toggle('on',p.id==='p-'+t)});}
  tabs.forEach(function(b){b.onclick=function(){show(b.dataset.t);history.replaceState(null,'','#'+b.dataset.t);};});
  var h=location.hash.slice(1); if(h&&document.getElementById('p-'+h))show(h);
})();
</script>
<?php uwciv_close(); ?>
