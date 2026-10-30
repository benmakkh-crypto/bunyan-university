<?php
/* صفحة المقرر: الوصف والساعات والمتطلبات وما يفتحه، والكتب PDF بأربع لغات حسب التصنيف والجودة */
require_once __DIR__ . '/inc/shell.php';
require_once __DIR__ . '/inc/curriculum.php';

$code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) ($_GET['c'] ?? '')));
$D = cur_doc();
if (!isset($D['courses'][$code])) { header('Location: /plans.php'); exit; }
$c = $D['courses'][$code];
$from = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['p'] ?? ''));
$progs = cur_programs_of($code);
if (!isset($progs[$from])) $from = $progs ? array_key_first($progs) : '';
$P = $from ? $D['programs'][$from] : null;
$col = $P ? ($UWCIV_COLLEGES[$P['college']] ?? null) : null;
$edit = cur_can_edit();
$TY = cur_types(); $t = $TY[$c['t']] ?? ['—', '#888'];
$CAT = cur_book_cats(); $QL = cur_book_quality(); $LG = cur_langs();
$isCr = !$P || $P['unit'] === 'cr';
$h = 'cur_h';
$byLang = []; foreach ($c['books'] as $b) $byLang[$b['lang']][] = $b;
$unlocks = cur_unlocks($code, $from ?: null);

uwciv_open($code . ' — ' . cur_name($c), cur_t('plans'), $h($c['d']));
?>
<link rel="stylesheet" href="/assets/css/uwciv-curriculum.css?v=1">
<div id="cu" class="<?= $edit ? 'cu-edit' : '' ?>" style="--cc:<?= $h($col['c'] ?? '#1f7a45') ?>" data-program="<?= $h($from) ?>" data-course="<?= $h($code) ?>">

<?php if ($edit): ?>
<div class="cu-adminbar"><i class="fas fa-pen"></i> وضع التحرير مفعّل لهذا المقرر.
  <span style="flex:1"></span>
  <button class="cu-btn" type="button" data-act="course-edit" data-code="<?= $h($code) ?>"><i class="fas fa-pen"></i> تعديل المقرر</button>
  <button class="cu-btn p" type="button" data-act="book-add" data-code="<?= $h($code) ?>"><i class="fas fa-plus"></i> إضافة كتاب</button>
  <button class="cu-btn d" type="button" data-act="course-delete" data-code="<?= $h($code) ?>"><i class="fas fa-trash"></i> حذف المقرر نهائيًا</button>
</div>
<?php endif; ?>

<div class="cu-box">
  <div class="cu-kv">
    <div><small><?= cur_t('code') ?></small><b class="notranslate" dir="ltr"><?= $h($code) ?></b></div>
    <div><small><?= $isCr ? 'الساعات المعتمدة' : cur_t('contact') ?></small><b><?= (int) $c['cr'] ?></b></div>
    <?php if ($isCr): ?>
    <div><small>ساعات التعلم في الفصل</small><b><?= (int) $c['cr'] * 45 ?></b></div>
    <div><small>ما يعادلها ECTS</small><b>≈ <?= cur_ects($c['cr']) ?></b></div>
    <?php endif; ?>
    <div><small><?= cur_t('type') ?></small><b><span class="cu-chip" style="--tc:<?= $t[1] ?>"><?= $h($t[0]) ?></span></b></div>
  </div>
  <h2 style="margin-top:16px"><i class="fas fa-align-right"></i> وصف المقرر</h2>
  <p style="margin:0;line-height:2"><?= $h($c['d']) ?></p>
  <p class="notranslate" dir="ltr" style="margin:8px 0 0;color:var(--muted);text-align:left"><?= $h($c['en']) ?></p>
  <?php foreach (['bn', 'ur'] as $l): if (!empty($c[$l])): ?><p class="notranslate" dir="<?= $LG[$l][1] ?>" style="margin:4px 0 0;color:var(--muted)"><?= $h($LG[$l][0]) ?>: <?= $h($c[$l]) ?></p><?php endif; endforeach; ?>
</div>

<div class="cu-box">
  <h2><i class="fas fa-project-diagram"></i> التسلسل</h2>
  <div style="display:grid;gap:14px">
    <div><b style="font-size:14px"><?= cur_t('pre') ?>:</b>
      <div class="cu-rel" style="margin-top:6px"><?php if ($c['pre']): foreach ($c['pre'] as $pr): $pc = $D['courses'][$pr] ?? null; ?><a href="/course.php?c=<?= rawurlencode($pr) ?>&p=<?= $h($from) ?>"><b class="notranslate"><?= $h($pr) ?></b><?= $h($pc ? cur_name($pc) : '') ?></a><?php endforeach; else: ?><span style="color:var(--muted)"><?= cur_t('none') ?> — يُسجَّل مباشرة</span><?php endif; ?></div></div>
    <div><b style="font-size:14px"><?= cur_t('unlocks') ?>:</b>
      <div class="cu-rel" style="margin-top:6px"><?php if ($unlocks): foreach ($unlocks as $u): ?><a href="/course.php?c=<?= rawurlencode($u) ?>&p=<?= $h($from) ?>"><b class="notranslate"><?= $h($u) ?></b><?= $h(cur_name($D['courses'][$u])) ?></a><?php endforeach; else: ?><span style="color:var(--muted)"><?= cur_t('none') ?></span><?php endif; ?></div></div>
    <div><b style="font-size:14px"><?= cur_t('programs') ?>:</b>
      <div class="cu-rel" style="margin-top:6px"><?php foreach ($progs as $s => $li): $pp = $D['programs'][$s]; ?><a href="/plan.php?p=<?= $h($s) ?>"><i class="fas fa-graduation-cap"></i><?= $h(cur_prog_name($pp)) ?> — <?= $h($pp['levels'][$li]['n']) ?></a><?php endforeach; ?></div></div>
  </div>
</div>

<div class="cu-box" id="books">
  <h2><i class="fas fa-book-open"></i> <?= cur_t('books') ?></h2>
  <div class="cu-tabs" role="tablist">
    <?php $first = true; foreach ($LG as $l => $lg): ?><button type="button" class="<?= $first ? 'on' : '' ?>" data-lang="<?= $l ?>"><span class="notranslate"><?= $h($lg[0]) ?></span> <em>(<?= count($byLang[$l] ?? []) ?>)</em></button><?php $first = false; endforeach; ?>
  </div>
  <?php $first = true; foreach ($LG as $l => $lg): $list = $byLang[$l] ?? []; usort($list, function ($a, $b) { $o = ['main' => 0, 'support' => 1, 'extra' => 2]; return ($o[$a['cat']] ?? 3) <=> ($o[$b['cat']] ?? 3); }); ?>
  <div class="cu-books" data-pane="<?= $l ?>" style="<?= $first ? '' : 'display:none' ?>" dir="<?= $lg[1] ?>">
    <?php if (!$list): ?><div class="cu-empty">لا توجد كتب بهذه اللغة بعد.<?= $edit ? ' استخدم «إضافة كتاب».' : '' ?></div><?php endif; ?>
    <?php foreach ($list as $b): $ct = $CAT[$b['cat']] ?? ['—', '#888']; $q = $QL[$b['q']] ?? ['', 'fa-book']; $link = cur_book_link($code, $b); ?>
    <div class="cu-book">
      <span class="ic"><i class="fas fa-file-pdf"></i></span>
      <div class="bd"><b class="notranslate"><?= $h($b['title']) ?></b><span class="notranslate"><?= $h($b['author']) ?></span>
        <div class="tags" dir="rtl"><em style="background:<?= $ct[1] ?>;color:#fff"><?= $h($ct[0]) ?></em><em><i class="fas <?= $q[1] ?>"></i> <?= $h($q[0]) ?></em><?php if (!empty($b['size'])): ?><em>PDF · <?= cur_fmt_size($b['size']) ?></em><?php endif; ?><?php if (!empty($b['url']) && empty($b['file'])): ?><em><i class="fas fa-shield-alt"></i> <?= $h(parse_url($b['url'], PHP_URL_HOST)) ?></em><?php endif; ?><?php if (!empty($b['dl'])): ?><em><i class="fas fa-download"></i> <?= (int) $b['dl'] ?></em><?php endif; ?></div></div>
      <div class="act" dir="rtl">
        <?php if ($link): ?><a class="cu-btn p" href="<?= $h($link) ?>" rel="nofollow noopener"<?= empty($b['file']) ? ' target="_blank"' : '' ?>><i class="fas <?= empty($b['file']) ? 'fa-external-link-alt' : 'fa-download' ?>"></i> <?= empty($b['file']) ? cur_t('read') : cur_t('download') ?></a>
        <?php else: ?><span class="cu-soon"><i class="far fa-clock"></i> <?= cur_t('nofile') ?></span><?php endif; ?>
        <?php if ($edit): ?><span class="cu-ed"><button class="cu-ic" type="button" title="تعديل الكتاب ورفع PDF" data-act="book-edit" data-code="<?= $h($code) ?>" data-book="<?= $h($b['id']) ?>"><i class="fas fa-pen"></i></button><button class="cu-ic del" type="button" title="حذف الكتاب" data-act="book-delete" data-code="<?= $h($code) ?>" data-book="<?= $h($b['id']) ?>"><i class="fas fa-trash"></i></button></span><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php $first = false; endforeach; ?>
  <p style="margin:14px 0 0;font-size:12.5px;color:var(--muted)"><i class="fas fa-shield-alt"></i> التحميل آمن: الملفات المرفوعة تُفحص عند الرفع وتُقدَّم من خادم الجامعة، والروابط الخارجية مقصورة على مكتبات موثوقة (<span class="notranslate" dir="ltr"><?= $h(implode('، ', cur_trusted_hosts())) ?></span>).</p>
</div>

<div style="display:flex;gap:10px;flex-wrap:wrap">
  <?php if ($from): ?><a class="cu-btn p" href="/plan.php?p=<?= $h($from) ?>"><i class="fas fa-arrow-right"></i> العودة إلى خطة <?= $h(cur_prog_name($P)) ?></a><?php endif; ?>
  <a class="cu-btn" href="/plans.php"><i class="fas fa-th"></i> كل الخطط الدراسية</a>
</div>
</div>
<?php include __DIR__ . '/inc/curriculum_editor.php'; ?>
<script>
document.querySelectorAll('[data-lang]').forEach(function(b){b.onclick=function(){document.querySelectorAll('[data-lang]').forEach(function(x){x.classList.toggle('on',x===b)});document.querySelectorAll('[data-pane]').forEach(function(p){p.style.display=p.dataset.pane===b.dataset.lang?'':'none'})}});
</script>
<?php uwciv_close(); ?>
