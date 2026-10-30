<?php
/* الخطة الدراسية لبرنامج: المستويات والمقررات والساعات والرموز والمتطلبات وشجرة التسلسل */
require_once __DIR__ . '/inc/shell.php';
require_once __DIR__ . '/inc/curriculum.php';

$slug = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['p'] ?? ''));
$D = cur_doc();
if (!isset($D['programs'][$slug])) { header('Location: /plans.php'); exit; }
$P = $D['programs'][$slug];
$col = $UWCIV_COLLEGES[$P['college']] ?? ['n' => '', 'c' => '#1f7a45', 'i' => 'fa-university'];
$st = cur_program_stats($P);
$edit = cur_can_edit();
$TY = cur_types();
$unit = $P['unit'] === 'cr' ? cur_t('cr') : cur_t('contact');
$h = 'cur_h';

uwciv_open(cur_prog_name($P), cur_t('plans'), $h($P['desc']));
?>
<link rel="stylesheet" href="/assets/css/uwciv-curriculum.css?v=1">
<div id="cu" class="<?= $edit ? 'cu-edit' : '' ?>" style="--cc:<?= $h($col['c']) ?>" data-program="<?= $h($slug) ?>">

<?php if ($edit): ?>
<div class="cu-adminbar"><i class="fas fa-pen"></i> وضع التحرير مفعّل: عدّل وأضف واحذف مباشرة. تُحفظ التغييرات فورًا مع نسخة احتياطية.
  <span style="flex:1"></span>
  <button class="cu-btn" type="button" data-act="program-edit"><i class="fas fa-cog"></i> بيانات البرنامج</button>
  <button class="cu-btn" type="button" data-act="level-add"><i class="fas fa-layer-group"></i> مستوى جديد</button>
  <a class="cu-btn" href="/admin/curriculum.php"><i class="fas fa-tools"></i> لوحة الخطط</a>
</div>
<?php endif; ?>

<div class="cu-facts">
  <div class="cu-fact"><i class="fas <?= $h($col['i']) ?>"></i><div><b style="font-size:15px"><?= $h($col['n']) ?></b><span>الكلية</span></div></div>
  <div class="cu-fact"><i class="fas fa-layer-group"></i><div><b><?= $h(cur_stage_name($P['stage'])) ?></b><span><?= (int) $P['years'] ?> <?= $P['years'] > 2 || $P['years'] == 0 ? 'سنوات' : ($P['years'] == 2 ? 'سنتان' : 'سنة') ?> · <?= count($P['levels']) ?> مستويات</span></div></div>
  <div class="cu-fact"><i class="fas fa-clock"></i><div><b><?= $st['total'] ?></b><span><?= $P['unit'] === 'cr' ? 'ساعة معتمدة' : 'ساعة اتصال' ?></span></div></div>
  <div class="cu-fact"><i class="fas fa-book"></i><div><b><?= $st['courses'] ?></b><span>مقررًا دراسيًا</span></div></div>
  <?php if ($P['unit'] === 'cr'): ?>
  <div class="cu-fact"><i class="fas fa-globe"></i><div><b>≈ <?= cur_ects($st['total']) ?></b><span>ECTS (معادلة أوروبية)</span></div></div>
  <?php endif; ?>
  <?php if (!empty($P['kkni'])): ?>
  <div class="cu-fact"><i class="fas fa-medal"></i><div><b>KKNI <?= (int) $P['kkni'] ?></b><span>الإطار الوطني الإندونيسي للمؤهلات</span></div></div>
  <?php endif; ?>
</div>

<div class="cu-box">
  <h2><i class="fas fa-chart-bar"></i> توزيع الساعات حسب نوع المقرر
    <?php if (($P['status'] ?? '') === 'proposed'): ?><span class="cu-draft" style="margin-inline-start:auto"><i class="fas fa-hourglass-half"></i> خطة مقترحة بانتظار اعتماد المجلس العلمي</span><?php endif; ?></h2>
  <div class="cu-dist"><?php foreach ($TY as $k => $t): if (empty($st['types'][$k])) continue; ?><span style="width:<?= round($st['types'][$k] / max(1, $st['total']) * 100, 2) ?>%;background:<?= $t[1] ?>" title="<?= $h($t[0]) ?>"></span><?php endforeach; ?></div>
  <div class="cu-legend"><?php foreach ($TY as $k => $t): if (empty($st['types'][$k])) continue; ?><span><i style="background:<?= $t[1] ?>"></i><?= $h($t[0]) ?>: <b><?= $st['types'][$k] ?></b> (<?= round($st['types'][$k] / max(1, $st['total']) * 100) ?>٪)</span><?php endforeach; ?></div>
  <?php if ($edit && $st['issues']): ?><div class="cu-note" style="margin:12px 0 0;background:#fff4f4;border-color:#f3cfd6;color:#9b1f45"><b>ملاحظات الجودة (تظهر لك فقط):</b><br><?= implode('<br>', array_map($h, $st['issues'])) ?></div><?php endif; ?>
</div>

<div class="cu-bar">
  <div class="cu-seg" role="tablist"><button class="on" data-view="levels" type="button"><i class="fas fa-th-large"></i> المستويات</button><button data-view="map" type="button"><i class="fas fa-project-diagram"></i> <?= cur_t('chain') ?></button></div>
  <div class="cu-in"><i class="fas fa-search"></i><input id="cuq" placeholder="ابحث بالرمز أو اسم المقرر…"></div>
  <button class="cu-btn" type="button" onclick="window.print()"><i class="fas fa-print"></i> طباعة</button>
  <a class="cu-btn" href="/plans.php"><i class="fas fa-th"></i> كل البرامج</a>
</div>

<div id="v-levels" class="cu-levels">
<?php foreach ($P['levels'] as $li => $L): ?>
  <div class="cu-lv" data-level="<?= $li ?>">
    <div class="cu-lvh"><b><?= $h($L['n']) ?></b>
      <?php if ($edit): ?><span class="cu-ed"><button class="cu-ic" type="button" title="تعديل اسم المستوى" data-act="level-rename" data-level="<?= $li ?>"><i class="fas fa-pen"></i></button><button class="cu-ic del" type="button" title="حذف المستوى" data-act="level-delete" data-level="<?= $li ?>"><i class="fas fa-trash"></i></button></span><?php endif; ?>
      <span class="h"><?= $st['levels'][$li] ?> <?= $P['unit'] === 'cr' ? 'ساعة' : 'ساعة اتصال' ?></span></div>
    <table class="cu-t">
      <thead><tr><th><?= cur_t('code') ?></th><th><?= cur_t('course') ?></th><th style="text-align:center"><?= $unit ?></th><th><?= cur_t('pre') ?></th><th class="tp"><?= cur_t('type') ?></th><?php if ($edit): ?><th class="cu-ed"></th><?php endif; ?></tr></thead>
      <tbody>
      <?php foreach ($L['c'] as $code): $c = $D['courses'][$code] ?? null; if (!$c) continue; $t = $TY[$c['t']] ?? ['—', '#888']; ?>
        <tr data-code="<?= $h($code) ?>" data-s="<?= $h(mb_strtolower($code . ' ' . $c['n'] . ' ' . $c['en'])) ?>">
          <td class="cd notranslate"><?= $h($code) ?></td>
          <td class="nm"><a href="/course.php?c=<?= rawurlencode($code) ?>&p=<?= $h($slug) ?>"><?= $h(cur_name($c)) ?></a><small class="notranslate"><?= $h($c['en']) ?></small></td>
          <td class="hr"><?= (int) $c['cr'] ?></td>
          <td class="pr notranslate"><?php if ($c['pre']): foreach ($c['pre'] as $pr): ?><a href="/course.php?c=<?= rawurlencode($pr) ?>&p=<?= $h($slug) ?>"><?= $h($pr) ?></a><?php endforeach; else: ?><span style="color:#a7b5ac">—</span><?php endif; ?></td>
          <td class="tp"><span class="cu-chip" style="--tc:<?= $t[1] ?>"><?= $h($t[0]) ?></span></td>
          <?php if ($edit): ?><td class="cu-ed"><button class="cu-ic" type="button" title="تعديل المقرر" data-act="course-edit" data-code="<?= $h($code) ?>"><i class="fas fa-pen"></i></button><button class="cu-ic" type="button" title="نقل إلى مستوى آخر" data-act="plan-move" data-code="<?= $h($code) ?>" data-level="<?= $li ?>"><i class="fas fa-exchange-alt"></i></button><button class="cu-ic del" type="button" title="حذف من الخطة" data-act="plan-remove" data-code="<?= $h($code) ?>"><i class="fas fa-times"></i></button></td><?php endif; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot><tr><td></td><td><?= cur_t('total') ?></td><td class="hr"><?= $st['levels'][$li] ?></td><td colspan="3"></td></tr></tfoot>
    </table>
    <?php if ($edit): ?><button class="cu-add" type="button" data-act="plan-add" data-level="<?= $li ?>"><i class="fas fa-plus"></i> إضافة مقرر إلى <?= $h($L['n']) ?></button><?php endif; ?>
  </div>
<?php endforeach; ?>
</div>

<div id="v-map" class="cu-map" style="display:none">
  <p style="margin:0 0 12px;color:var(--muted);font-size:13.5px"><i class="fas fa-hand-pointer"></i> مرّر المؤشر أو اضغط على أي مقرر لإظهار متطلباته السابقة وما يفتحه من مقررات لاحقة.</p>
  <div class="cu-map-in" id="cumap">
    <svg id="cusvg"></svg>
    <?php foreach ($P['levels'] as $li => $L): ?>
    <div class="cu-col"><h4><?= $h($L['n']) ?></h4>
      <?php foreach ($L['c'] as $code): $c = $D['courses'][$code] ?? null; if (!$c) continue; ?>
      <a class="cu-node" href="/course.php?c=<?= rawurlencode($code) ?>&p=<?= $h($slug) ?>" data-code="<?= $h($code) ?>" data-pre="<?= $h(implode(',', $c['pre'])) ?>" style="--tc:<?= ($TY[$c['t']] ?? ['', '#888'])[1] ?>"><b class="notranslate"><?= $h($code) ?> · <?= (int) $c['cr'] ?></b><?= $h(cur_name($c)) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="cu-box" style="margin-top:18px">
  <h2><i class="fas fa-bullseye"></i> <?= cur_t('outcomes') ?> (PLOs)</h2>
  <ul class="cu-plos"><?php foreach ($P['plos'] as $i => $o): ?><li><b class="notranslate">PLO<?= $i + 1 ?></b><span><?= $h($o) ?></span></li><?php endforeach; ?></ul>
</div>

<div class="cu-note">
  <b><i class="fas fa-info-circle"></i> عن نظام الساعات والرموز:</b>
  <?php if ($P['unit'] === 'cr'): ?>
  الساعة المعتمدة = 45 ساعة تعلم في الفصل (محاضرة وتكليفات وتعلم ذاتي) وفق لائحة التعليم العالي الإندونيسية Permendikbudristek 53/2023، وتعادل نحو 1.5 وحدة ECTS. الحد الأقصى في الفصل 19 ساعة.
  <?php else: ?>
  ساعات هذا البرنامج ساعات اتصال تعليمي مباشر، وفق توصيف الجامعة.
  <?php endif; ?>
  الرقم الأول في رمز المقرر يدل على السنة الدراسية (1–4 للبكالوريوس، 5–6 للماجستير، 7–8 للدكتوراه، 0 للتمهيدي)، والحروف تدل على القسم العلمي. لا يُسجَّل المقرر إلا بعد اجتياز متطلبه السابق.
</div>

</div>
<?php include __DIR__ . '/inc/curriculum_editor.php'; ?>
<script>
(function(){
  var root=document.getElementById('cu');
  // التبديل بين العرضين
  root.querySelectorAll('[data-view]').forEach(function(b){b.onclick=function(){root.querySelectorAll('[data-view]').forEach(function(x){x.classList.toggle('on',x===b)});document.getElementById('v-levels').style.display=b.dataset.view==='levels'?'':'none';document.getElementById('v-map').style.display=b.dataset.view==='map'?'':'none';if(b.dataset.view==='map')draw();};});
  // البحث
  document.getElementById('cuq').oninput=function(){var q=this.value.trim().toLowerCase();root.querySelectorAll('tr[data-s]').forEach(function(r){r.style.display=!q||r.dataset.s.indexOf(q)>-1?'':'none'});root.querySelectorAll('.cu-node').forEach(function(n){n.style.outline=q&&(n.textContent.toLowerCase().indexOf(q)>-1)?'3px solid #c9962a':''})};
  // شجرة المتطلبات
  var map=document.getElementById('cumap'),svg=document.getElementById('cusvg'),wrap=document.getElementById('v-map'),nodes={};
  map.querySelectorAll('.cu-node').forEach(function(n){nodes[n.dataset.code]=n});
  function draw(){
    var r0=map.getBoundingClientRect();svg.setAttribute('width',map.scrollWidth);svg.setAttribute('height',map.scrollHeight);svg.innerHTML='';
    var rtl=getComputedStyle(map).direction==='rtl';
    Object.keys(nodes).forEach(function(k){var n=nodes[k];(n.dataset.pre?n.dataset.pre.split(','):[]).forEach(function(p){var a=nodes[p];if(!a)return;var ra=a.getBoundingClientRect(),rb=n.getBoundingClientRect();
      var x1=(rtl?ra.left:ra.right)-r0.left,y1=ra.top+ra.height/2-r0.top,x2=(rtl?rb.right:rb.left)-r0.left,y2=rb.top+rb.height/2-r0.top,mx=(x1+x2)/2;
      var path=document.createElementNS('http://www.w3.org/2000/svg','path');path.setAttribute('d','M'+x1+','+y1+' C'+mx+','+y1+' '+mx+','+y2+' '+x2+','+y2);path.dataset.a=p;path.dataset.b=k;svg.appendChild(path);});});
  }
  function related(code){var up={},down={};(function u(c){(nodes[c]&&nodes[c].dataset.pre?nodes[c].dataset.pre.split(','):[]).forEach(function(p){if(!up[p]){up[p]=1;u(p)}})})(code);
    (function d(c){Object.keys(nodes).forEach(function(k){if(!down[k]&&nodes[k].dataset.pre.split(',').indexOf(c)>-1){down[k]=1;d(k)}})})(code);return Object.assign(up,down);}
  function hl(code){if(!code){wrap.classList.remove('hl');return}var rel=related(code);wrap.classList.add('hl');
    Object.keys(nodes).forEach(function(k){nodes[k].classList.toggle('on',!!rel[k]);nodes[k].classList.toggle('me',k===code)});
    svg.querySelectorAll('path').forEach(function(p){p.classList.toggle('on',(rel[p.dataset.a]||p.dataset.a===code)&&(rel[p.dataset.b]||p.dataset.b===code))});}
  map.addEventListener('mouseover',function(e){var n=e.target.closest('.cu-node');hl(n&&n.dataset.code)});
  map.addEventListener('mouseleave',function(){hl(null)});
  window.addEventListener('resize',function(){if(wrap.style.display!=='none')draw()});
})();
</script>
<?php uwciv_close(); ?>
