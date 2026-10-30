<?php
/* دليل الخطط الدراسية: كل البرامج حسب الكلية والمرحلة + البحث في كل المقررات */
require_once __DIR__ . '/inc/shell.php';
require_once __DIR__ . '/inc/curriculum.php';

$D = cur_doc();
$edit = cur_can_edit();
$stageCol = ['dip' => '#0e9aa7', 'bac' => '#12935a', 'mas' => '#2c5fd0', 'phd' => '#9b1f45'];
$byCol = []; foreach ($D['programs'] as $s => $p) $byCol[$p['college']][$s] = $p;
$nBooks = 0; $nFiles = 0; foreach ($D['courses'] as $c) foreach ($c['books'] as $b) { $nBooks++; if (!empty($b['file']) || !empty($b['url'])) $nFiles++; }
$h = 'cur_h';

uwciv_open(cur_t('plans'), t('colleges_m'), 'خطط كل البرامج من معهد اللغة العربية إلى الدكتوراه: المستويات، ورموز المقررات، وتوزيع الساعات المعتمدة، والمتطلبات السابقة، والكتب والمراجع بأربع لغات.');
?>
<link rel="stylesheet" href="/assets/css/uwciv-curriculum.css?v=1">
<div id="cu" class="<?= $edit ? 'cu-edit' : '' ?>">
<?php if ($edit): ?><div class="cu-adminbar"><i class="fas fa-pen"></i> أنت مسجّل كمشرف: افتح أي خطة لتعديلها مباشرة، أو أدِر كل الخطط من لوحة التحكم.<span style="flex:1"></span><a class="cu-btn p" href="/admin/curriculum.php"><i class="fas fa-tools"></i> لوحة الخطط الدراسية</a></div><?php endif; ?>

<div class="cu-facts">
  <div class="cu-fact"><i class="fas fa-graduation-cap"></i><div><b><?= count($D['programs']) ?></b><span>برنامجًا دراسيًا</span></div></div>
  <div class="cu-fact"><i class="fas fa-book"></i><div><b><?= count($D['courses']) ?></b><span>مقررًا برموزها وساعاتها</span></div></div>
  <div class="cu-fact"><i class="fas fa-book-reader"></i><div><b><?= $nBooks ?></b><span>كتابًا ومرجعًا</span></div></div>
  <div class="cu-fact"><i class="fas fa-language"></i><div><b>4</b><span>لغات: العربية · English · বাংলা · اردو</span></div></div>
</div>

<div class="cu-bar">
  <div class="cu-in"><i class="fas fa-search"></i><input id="cuq" placeholder="ابحث في كل المقررات بالرمز أو الاسم (مثال: FIQ201 أو أصول الفقه)…" autocomplete="off"></div>
</div>
<div id="cures" class="cu-box" style="display:none"><h2><i class="fas fa-search"></i> نتائج البحث</h2><div class="cu-rel" id="curesl"></div></div>

<?php foreach ($UWCIV_COLLEGES as $ck => $col): if (empty($byCol[$ck])) continue; ?>
<div style="--cc:<?= $h($col['c']) ?>;margin:0 0 30px">
  <div class="k-gh" style="display:flex;align-items:center;gap:12px;margin:0 0 14px"><i class="fas <?= $h($col['i']) ?>" style="width:44px;height:44px;border-radius:13px;display:grid;place-items:center;color:#fff;background:<?= $h($col['c']) ?>;font-size:18px"></i><h2 style="margin:0;font-size:21px;font-weight:900"><a href="/college/<?= $h($ck) ?>" style="color:var(--ink);text-decoration:none"><?= $h($col['n']) ?></a></h2></div>
  <div class="cu-pgrid">
  <?php foreach ($byCol[$ck] as $s => $p): $st = cur_program_stats($p); ?>
    <a class="cu-pcard" href="/plan.php?p=<?= $h($s) ?>" style="--sc:<?= $stageCol[$p['stage']] ?? '#1f7a45' ?>">
      <span class="cu-chip" style="--tc:<?= $stageCol[$p['stage']] ?? '#1f7a45' ?>;align-self:flex-start"><?= $h(cur_stage_name($p['stage'])) ?></span>
      <h3><?= $h(cur_prog_name($p)) ?></h3><small class="notranslate"><?= $h($p['en']) ?></small>
      <div class="m"><span><i class="fas fa-clock"></i> <?= $st['total'] ?> <?= $p['unit'] === 'cr' ? 'ساعة معتمدة' : 'ساعة اتصال' ?></span><span><i class="fas fa-layer-group"></i> <?= count($p['levels']) ?> مستويات</span><span><i class="fas fa-book"></i> <?= $st['courses'] ?> مقررًا</span></div>
    </a>
  <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>

<div class="cu-note"><b><i class="fas fa-award"></i> معايير بناء الخطط:</b> بُنيت الخطط وفق لائحة التعليم العالي الإندونيسية (Permendikbudristek 53/2023): البكالوريوس 144 ساعة معتمدة في 8 فصول، والماجستير 54 ساعة، والدكتوراه في 6 فصول، والساعة المعتمدة = 45 ساعة تعلم ≈ 1.5 ECTS، مع مستويات الإطار الوطني للمؤهلات KKNI (6 للبكالوريوس، 8 للماجستير، 9 للدكتوراه)، ومخرجات تعلم لكل برنامج، وتسلسل متطلبات مُتحقَّق منه آليًا. الخطط مقترحة وتُعتمد من المجلس العلمي.</div>
</div>
<script>
(function(){
  var C=<?= json_encode(array_map(function ($c, $k) { return [$k, $c['n'], $c['en'], $c['cr']]; }, $D['courses'], array_keys($D['courses'])), JSON_UNESCAPED_UNICODE) ?>;
  var q=document.getElementById('cuq'),box=document.getElementById('cures'),list=document.getElementById('curesl');
  q.oninput=function(){var v=q.value.trim().toLowerCase();if(v.length<2){box.style.display='none';return}
    var r=C.filter(function(c){return (c[0]+' '+c[1]+' '+c[2]).toLowerCase().indexOf(v)>-1}).slice(0,40);
    list.innerHTML=r.length?r.map(function(c){return '<a href="/course.php?c='+encodeURIComponent(c[0])+'"><b class="notranslate">'+c[0]+'</b>'+c[1].replace(/</g,'&lt;')+' <small>('+c[3]+')</small></a>'}).join(''):'<span style="color:var(--muted)">لا نتائج.</span>';box.style.display='';};
})();
</script>
<?php uwciv_close(); ?>
