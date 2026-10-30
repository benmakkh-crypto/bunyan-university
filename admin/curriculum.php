<?php
/* لوحة الخطط الدراسية: البرامج والتحقق من الجودة والكتب والنطاقات الموثوقة والنسخ الاحتياطية والتصدير والاستيراد */
require_once $_SERVER['DOCUMENT_ROOT'] . '/db_config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/admin_ui.php';
uwciv_admin_gate(['title' => 'الخطط الدراسية']);
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/curriculum.php';

$msg = $err = '';
$bdir = dirname(cur_live_file()) . '/curriculum-backups';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!uwciv_csrf_ok()) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    else switch ($_POST['do'] ?? '') {
        case 'export':
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="bunyan-curriculum-' . date('Ymd-His') . '.json"');
            echo json_encode(cur_doc(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); exit;
        case 'import':
            $j = !empty($_FILES['json']['tmp_name']) ? json_decode((string) file_get_contents($_FILES['json']['tmp_name']), true) : null;
            if (!is_array($j) || empty($j['courses']) || empty($j['programs']) || !is_array($j['courses'])) { $err = 'ملف غير صالح: يجب أن يكون ملف خطط مُصدَّرًا من هذه اللوحة.'; break; }
            foreach ($j['courses'] as $k => $c) if (!preg_match('/^[A-Z]{2,4}[0-9]{3}$/', (string) $k) || !isset($c['n'], $c['cr'], $c['pre'], $c['books'])) { $err = 'بيانات المقرر ' . $k . ' غير مكتملة.'; break 2; }
            cur_save($j); uwciv_staff_log('استيراد الخطط الدراسية'); $msg = 'تم استيراد الخطط (حُفظت نسخة احتياطية من السابقة).'; break;
        case 'reset':
            if (($_POST['confirm'] ?? '') !== 'استعادة') { $err = 'اكتب كلمة «استعادة» للتأكيد.'; break; }
            $seed = json_decode((string) file_get_contents(cur_seed_file()), true); cur_save($seed);
            uwciv_staff_log('استعادة الخطط الافتراضية'); $msg = 'أُعيدت الخطط الافتراضية (حُفظت نسخة احتياطية من الحالية).'; break;
        case 'restore':
            $f = $bdir . '/' . basename((string) ($_POST['file'] ?? ''));
            $j = is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
            if (!is_array($j) || empty($j['courses'])) { $err = 'النسخة غير صالحة.'; break; }
            cur_save($j); uwciv_staff_log('استرجاع نسخة الخطط ' . basename($f)); $msg = 'تم استرجاع النسخة.'; break;
    }
}
$D = cur_doc(true);
$nBooks = $nFiles = $nLinks = $nDl = 0;
foreach ($D['courses'] as $c) foreach ($c['books'] as $b) { $nBooks++; if (!empty($b['file'])) $nFiles++; elseif (!empty($b['url'])) $nLinks++; $nDl += (int) ($b['dl'] ?? 0); }
$backups = glob($bdir . '/curriculum-*.json') ?: []; rsort($backups);
$h = 'uwciv_h';
$_GET['p'] = '';
uwciv_admin_open('curriculum', 'الخطط الدراسية', 'البرامج والمستويات والمقررات والكتب. اضغط «عرض وتعديل» لتحرير أي خطة مباشرة بأيقونات الإضافة والتعديل والحذف.',
    '<a class="ad-b g" href="/plans.php" target="_blank"><i class="fas fa-eye"></i> عرض في الموقع</a><button class="ad-b p" type="button" data-act="program-new"><i class="fas fa-plus"></i> برنامج جديد</button>');
uwciv_admin_toast($err ?: $msg, (bool) $err);
?>
<link rel="stylesheet" href="/assets/css/uwciv-curriculum.css?v=1">
<div class="cp-kpis">
  <div class="cp-kpi" style="--c:#1f7a45"><i class="fas fa-graduation-cap"></i><span><b><?= count($D['programs']) ?></b><span>برنامجًا</span></span></div>
  <div class="cp-kpi" style="--c:#2c5fd0"><i class="fas fa-book"></i><span><b><?= count($D['courses']) ?></b><span>مقررًا في الكتالوج</span></span></div>
  <div class="cp-kpi" style="--c:#c9962a"><i class="fas fa-book-reader"></i><span><b><?= $nBooks ?></b><span>كتابًا (<?= $nFiles ?> PDF مرفوع · <?= $nLinks ?> رابط)</span></span></div>
  <div class="cp-kpi" style="--c:#9b1f45"><i class="fas fa-download"></i><span><b><?= $nDl ?></b><span>عملية تحميل</span></span></div>
</div>

<div class="cp-card">
  <h2><i class="fas fa-list"></i> البرامج وفحص الجودة</h2>
  <p>يتحقق النظام آليًا من مجموع الساعات، والحد الأقصى للفصل (19 ساعة للبكالوريوس)، وورود كل متطلب سابق قبل المقرر الذي يتطلبه.</p>
  <div style="overflow-x:auto"><table class="cu-t" style="min-width:760px">
    <thead><tr><th>البرنامج</th><th>الكلية</th><th>المرحلة</th><th style="text-align:center">الساعات</th><th style="text-align:center">المستويات</th><th style="text-align:center">المقررات</th><th>الحالة</th><th>الفحص</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($D['programs'] as $s => $p): $st = cur_program_stats($p); ?>
      <tr>
        <td class="nm"><a href="/plan.php?p=<?= $h($s) ?>" target="_blank"><?= $h($p['n']) ?></a><small><?= $h($s) ?></small></td>
        <td><?= $h($UWCIV_COLLEGES[$p['college']]['n'] ?? $p['college']) ?></td>
        <td><?= $h(cur_stage_name($p['stage'])) ?></td>
        <td class="hr"><?= $st['total'] ?> / <?= (int) $p['target'] ?></td>
        <td class="hr"><?= count($p['levels']) ?></td>
        <td class="hr"><?= $st['courses'] ?></td>
        <td><?= ($p['status'] ?? '') === 'approved' ? '<span class="cu-chip" style="--tc:#12935a">معتمدة</span>' : '<span class="cu-chip" style="--tc:#c9962a">مقترحة</span>' ?></td>
        <td><?= $st['issues'] ? '<span style="color:#9b1f45;font-weight:800" title="' . $h(implode(' | ', $st['issues'])) . '"><i class="fas fa-exclamation-triangle"></i> ' . count($st['issues']) . ' ملاحظة</span>' : '<span style="color:#12935a;font-weight:800"><i class="fas fa-check-circle"></i> سليمة</span>' ?></td>
        <td style="white-space:nowrap"><a class="cu-btn p" href="/plan.php?p=<?= $h($s) ?>" target="_blank"><i class="fas fa-pen"></i> عرض وتعديل</a> <button class="cu-ic del" type="button" title="حذف البرنامج" data-act="program-delete" data-program="<?= $h($s) ?>"><i class="fas fa-trash"></i></button></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
</div>

<div class="cp-card">
  <h2><i class="fas fa-shield-alt"></i> مكتبات الروابط الموثوقة</h2>
  <p>لا تُقبل روابط الكتب الخارجية إلا إن كانت https ومن هذه النطاقات. الملفات المرفوعة تُحفظ في مجلد محمي وتُقدَّم عبر بوابة التحميل بعد فحصها.</p>
  <form id="trusted" style="display:flex;gap:10px;flex-wrap:wrap"><input name="trusted" value="<?= $h(implode(', ', $D['trusted'] ?? [])) ?>" dir="ltr" style="flex:1 1 400px;border:1.5px solid #dbe6df;border-radius:11px;padding:10px"><button class="ad-b p" type="submit"><i class="fas fa-save"></i> حفظ</button></form>
</div>

<div class="cp-card">
  <h2><i class="fas fa-database"></i> النسخ الاحتياطية والتصدير</h2>
  <p>كل تعديل يحفظ نسخة احتياطية تلقائيًا (آخر 20 نسخة). آخر تحديث: <b><?= $h($D['updated'] ?? '—') ?></b></p>
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start">
    <form method="post"><?= uwciv_csrf_field() ?><input type="hidden" name="do" value="export"><button class="ad-b g" type="submit"><i class="fas fa-file-export"></i> تصدير الخطط (JSON)</button></form>
    <form method="post" enctype="multipart/form-data" style="display:flex;gap:6px;align-items:center"><?= uwciv_csrf_field() ?><input type="hidden" name="do" value="import"><input type="file" name="json" accept=".json,application/json" required><button class="ad-b g" type="submit"><i class="fas fa-file-import"></i> استيراد</button></form>
    <form method="post" style="display:flex;gap:6px;align-items:center" onsubmit="return confirm('استعادة الخطط الافتراضية؟ ستُحفظ نسخة من الحالية.')"><?= uwciv_csrf_field() ?><input type="hidden" name="do" value="reset"><input name="confirm" placeholder="اكتب: استعادة" style="border:1.5px solid #dbe6df;border-radius:10px;padding:8px;width:130px"><button class="ad-b" type="submit" style="background:#fbe9ee;color:#9b1f45"><i class="fas fa-undo"></i> الخطط الافتراضية</button></form>
  </div>
  <?php if ($backups): ?>
  <div style="margin-top:14px;display:grid;gap:6px;max-height:240px;overflow:auto">
    <?php foreach (array_slice($backups, 0, 20) as $f): ?>
    <form method="post" style="display:flex;gap:10px;align-items:center;background:var(--mist);border-radius:12px;padding:6px 12px"><?= uwciv_csrf_field() ?><input type="hidden" name="do" value="restore"><input type="hidden" name="file" value="<?= $h(basename($f)) ?>"><i class="far fa-clock"></i><span dir="ltr" style="flex:1"><?= $h(basename($f, '.json')) ?></span><button class="ad-b g" type="submit" onclick="return confirm('استرجاع هذه النسخة؟')"><i class="fas fa-history"></i> استرجاع</button></form>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/inc/curriculum_editor.php'; ?>
<script>
document.getElementById('trusted').onsubmit=function(e){e.preventDefault();var fd=new FormData(this);fd.append('act','trusted-save');fd.append('csrf',<?= json_encode(uwciv_csrf()) ?>);
  fetch('/admin/curriculum-api.php',{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json()}).then(function(r){alert(r.msg)})};
</script>
<?php uwciv_admin_close(); ?>
