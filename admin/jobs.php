<?php
/* لوحة التوظيف — للمشرف العام فقط */
require_once $_SERVER['DOCUMENT_ROOT'] . '/db_config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/admin_ui.php';
uwciv_admin_gate();
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/jobs.php';
if (empty($_SESSION['jadm_csrf'])) $_SESSION['jadm_csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['jadm_csrf'];
$TR = uwciv_job_tracks(); $ST = uwciv_job_status();

/* عرض السيرة الذاتية */
if (isset($_GET['cv'])) {
    $f = basename((string) $_GET['cv']); $p = uwciv_jobs_dir() . '/files/' . $f;
    if (!preg_match('/^cv-[\w-]+\.pdf$/', $f) || !is_file($p)) { http_response_code(404); exit('الملف غير موجود'); }
    header('Content-Type: application/pdf'); header('Content-Disposition: inline; filename="' . $f . '"'); header('X-Content-Type-Options: nosniff'); readfile($p); exit;
}

$apps = uwciv_jobs_apps(); $pos = uwciv_jobs_positions(); $msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) {
    $act = $_POST['act'] ?? '';
    if ($act === 'status') {
        foreach ($apps as &$a) if ($a['ref'] === ($_POST['ref'] ?? '')) {
            $s = isset($ST[$_POST['status'] ?? '']) ? $_POST['status'] : $a['status'];
            if ($s !== $a['status']) { $a['history'][] = [$s, date('c'), '']; $a['status'] = $s; }
            $a['note'] = trim(mb_substr((string) ($_POST['note'] ?? ''), 0, 1500));
            $a['internal'] = trim(mb_substr((string) ($_POST['internal'] ?? ''), 0, 1500));
            if (!empty($_POST['notify'])) {
                require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/mailer.php';
                uwciv_mail($a['email'], 'تحديث حالة طلبك ' . $a['ref'] . ' — ' . $ST[$s][0], '<h3 style="color:#12935a">' . htmlspecialchars($ST[$s][0]) . '</h3><p>' . htmlspecialchars($ST[$s][3]) . '</p>' . ($a['note'] ? '<p><b>رسالة اللجنة:</b><br>' . nl2br(htmlspecialchars($a['note'])) . '</p>' : '') . '<p>تابع طلبك: <a href="https://your-domain.com/jobs#track">your-domain.com/jobs</a></p>');
            }
            $msg = 'تم تحديث الطلب ' . $a['ref'];
        }
        unset($a); uwciv_jobs_save_apps($apps);
    } elseif ($act === 'pos_save') {
        $id = (string) ($_POST['id'] ?? ''); $row = ['id' => $id ?: 'p' . bin2hex(random_bytes(3)), 'track' => isset($TR[$_POST['track'] ?? '']) ? $_POST['track'] : 'faculty', 'title' => trim((string) ($_POST['title'] ?? '')), 'college' => isset($UWCIV_COLLEGES[$_POST['college'] ?? '']) ? $_POST['college'] : '', 'desc' => trim((string) ($_POST['desc'] ?? '')), 'type' => trim((string) ($_POST['type'] ?? 'عن بُعد')) ?: 'عن بُعد', 'open' => !empty($_POST['open'])];
        if ($row['title'] !== '') { $hit = false; foreach ($pos as &$p) if ($p['id'] === $id) { $p = $row; $hit = true; } unset($p); if (!$hit) $pos[] = $row; uwciv_jobs_save_positions($pos); $msg = 'تم حفظ الشاغر.'; }
    } elseif ($act === 'pos_del') {
        $pos = array_values(array_filter($pos, function ($p) { return $p['id'] !== ($_POST['id'] ?? ''); })); uwciv_jobs_save_positions($pos); $msg = 'تم حذف الشاغر.';
    }
    header('Location: /admin/jobs.php?ok=' . rawurlencode($msg ?: 'تم.') . (isset($_POST['tab']) ? '&tab=' . rawurlencode($_POST['tab']) : '')); exit;
}
$msg = (string) ($_GET['ok'] ?? '');
$tab = ($_GET['tab'] ?? '') === 'pos' ? 'pos' : 'apps';
$fs = (string) ($_GET['s'] ?? ''); $q = trim((string) ($_GET['q'] ?? ''));
$cnt = array_fill_keys(array_keys($ST), 0); foreach ($apps as $a) $cnt[$a['status']] = ($cnt[$a['status']] ?? 0) + 1;
$list = array_reverse(array_filter($apps, function ($a) use ($fs, $q) { return (!$fs || $a['status'] === $fs) && (!$q || mb_stripos($a['name'] . ' ' . $a['email'] . ' ' . $a['ref'] . ' ' . $a['spec'], $q) !== false); }));
$posT = []; foreach ($pos as $p) $posT[$p['id']] = $p['title'];
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
uwciv_admin_open('jobs', 'لوحة التوظيف', 'راجع طلبات الانضمام، وحدّث حالتها، وأدر الشواغر المعروضة في صفحة <a href="/jobs">الوظائف</a>.');
?>
<style>
.ja-top{margin:0 0 16px;display:grid;grid-template-columns:repeat(7,1fr);gap:10px}
.ja-k{background:#fff;border-radius:16px;padding:14px;box-shadow:var(--sh-1);text-decoration:none!important;border-bottom:3px solid var(--c);color:var(--text)!important}
.ja-k b{display:block;font-size:24px;font-weight:900;color:var(--c)}.ja-k span{font-size:12.5px;color:var(--muted)}
.ja-k.on{box-shadow:0 0 0 2px var(--c)}
.ja-tabs{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap}
.ja-tabs a{background:#fff;border-radius:12px;padding:10px 16px;font-weight:900;box-shadow:var(--sh-1);color:var(--ink-3)!important;text-decoration:none!important}
.ja-tabs a.on{background:var(--ink-3);color:#fff!important}
.ja-msg{background:#e4f4ea;color:#12935a;border-radius:14px;padding:12px 16px;font-weight:800;margin-bottom:14px}
.ja-q{display:flex;gap:8px;margin-bottom:14px}.ja-q input{flex:1;border:2px solid #e3ece6;border-radius:12px;padding:10px 14px;font-family:inherit}.ja-q button{border:0;border-radius:12px;padding:0 18px;background:var(--teal);color:#fff;font-weight:800}
.ja-app{background:#fff;border-radius:18px;box-shadow:var(--sh-1);margin-bottom:10px;overflow:hidden;border-inline-start:5px solid var(--c)}
.ja-app>summary{list-style:none;cursor:pointer;display:grid;grid-template-columns:130px 1.4fr 1.4fr 140px 20px;gap:12px;align-items:center;padding:14px 18px}
.ja-app>summary::-webkit-details-marker{display:none}
.ja-app summary b{display:block;color:var(--ink)}.ja-app summary small{color:var(--muted);font-size:12.5px}
.ja-badge{justify-self:start;background:var(--c);color:#fff;border-radius:20px;padding:4px 12px;font-size:12.5px;font-weight:800}
.ja-body{display:grid;grid-template-columns:1.3fr 1fr;gap:18px;padding:16px 18px;border-top:1px solid #edf3ef}
.ja-dl{display:grid;grid-template-columns:120px 1fr;gap:6px 12px;font-size:14px;margin:0}.ja-dl dt{color:var(--muted)}.ja-dl dd{margin:0;font-weight:700;word-break:break-word}
.ja-cover{grid-column:1/-1;background:var(--mist);border-radius:12px;padding:12px;line-height:1.9;font-size:14px;margin-top:8px}
.ja-btns{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.ja-btns a{display:inline-flex;gap:6px;align-items:center;background:var(--mist);border-radius:10px;padding:8px 12px;font-weight:800;font-size:13.5px;color:var(--ink-3)!important;text-decoration:none!important}
.ja-btns a.p{background:#fdecef;color:#c0392b!important}
.ja-st form{display:flex;flex-direction:column;gap:10px}
.ja-st label{display:flex;flex-direction:column;gap:6px;font-weight:800;font-size:13.5px;color:var(--ink-3)}
.ja-st select,.ja-st textarea,.ja-pf input,.ja-pf select,.ja-pf textarea{border:2px solid #e3ece6;border-radius:12px;padding:9px 12px;font-family:inherit;font-weight:500;width:100%;box-sizing:border-box}
.ja-st .chk{flex-direction:row;align-items:center;gap:8px;font-weight:700}
.ja-st button,.ja-pf button.s{border:0;border-radius:12px;padding:11px;background:linear-gradient(135deg,var(--teal),var(--ink-3));color:#fff;font-weight:900;font-family:inherit;cursor:pointer}
.ja-hist{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}.ja-hist span{background:var(--mist);border-radius:8px;padding:3px 8px;font-size:12px}
.ja-empty{text-align:center;color:var(--muted);background:#fff;border-radius:18px;padding:40px;border:1px dashed #cfdcd4}
.ja-pf{background:#fff;border-radius:16px;box-shadow:var(--sh-1);padding:14px 16px;margin-bottom:10px;display:grid;grid-template-columns:2fr 1fr 1.4fr 1fr auto;gap:10px;align-items:start;border-inline-start:4px solid var(--c)}
.ja-pf textarea{grid-column:1/-2}
.ja-pf .chk{display:flex;gap:6px;align-items:center;font-weight:800;font-size:13.5px}
.ja-pf .acts{display:flex;gap:6px;grid-column:-2/-1;grid-row:1/3;flex-direction:column}
.ja-pf .d{border:0;border-radius:12px;padding:10px;background:#fdecef;color:#c0392b;cursor:pointer}
@media(max-width:1000px){.ja-top{grid-template-columns:repeat(3,1fr)}.ja-app>summary{grid-template-columns:1fr auto}.ja-app>summary>*:nth-child(3),.ja-app>summary>*:nth-child(5){display:none}.ja-body{grid-template-columns:1fr}.ja-pf{grid-template-columns:1fr}.ja-pf textarea,.ja-pf .acts{grid-column:auto;grid-row:auto}}
</style>
<div class="ja-top">
  <a class="ja-k<?= !$fs ? ' on' : '' ?>" style="--c:#0b3119" href="?"><b><?= count($apps) ?></b><span>كل الطلبات</span></a>
  <?php foreach ($ST as $k => $s): ?><a class="ja-k<?= $fs === $k ? ' on' : '' ?>" style="--c:<?= $s[2] ?>" href="?s=<?= $k ?>"><b><?= $cnt[$k] ?? 0 ?></b><span><?= $s[0] ?></span></a><?php endforeach; ?>
</div>
<?php if ($msg): ?><div class="ja-msg"><i class="fas fa-check-circle"></i> <?= $h($msg) ?></div><?php endif; ?>
<nav class="ja-tabs"><a class="<?= $tab === 'apps' ? 'on' : '' ?>" href="?"><i class="fas fa-inbox"></i> الطلبات</a><a class="<?= $tab === 'pos' ? 'on' : '' ?>" href="?tab=pos"><i class="fas fa-briefcase"></i> الشواغر (<?= count($pos) ?>)</a><a href="/jobs" target="_blank"><i class="fas fa-eye"></i> صفحة الوظائف</a></nav>

<?php if ($tab === 'apps'): ?>
<form class="ja-q"><input name="q" value="<?= $h($q) ?>" placeholder="بحث بالاسم أو البريد أو رقم الطلب أو التخصص"><?php if ($fs): ?><input type="hidden" name="s" value="<?= $h($fs) ?>"><?php endif; ?><button>بحث</button></form>
<?php if (!$list): ?><div class="ja-empty"><i class="fas fa-inbox" style="font-size:34px;color:#bfd8c8"></i><br>لا توجد طلبات <?= $fs || $q ? 'مطابقة' : 'بعد' ?>.</div><?php endif; ?>
<?php foreach ($list as $a): $s = $ST[$a['status']] ?? $ST['new']; ?>
<details class="ja-app" style="--c:<?= $s[2] ?>">
  <summary><span><b dir="ltr"><?= $h($a['ref']) ?></b><small><?= date('Y/m/d', strtotime($a['created'])) ?></small></span><span><b><?= $h($a['name']) ?></b><small dir="ltr"><?= $h($a['email']) ?></small></span><span><b><?= $h($a['pos'] ? ($posT[$a['pos']] ?? 'شاغر محذوف') : 'تقديم عام') ?></b><small><?= $h($a['degree'] . ' — ' . $a['spec']) ?></small></span><span class="ja-badge"><?= $s[0] ?></span><i class="fas fa-chevron-down"></i></summary>
  <div class="ja-body">
    <div>
      <dl class="ja-dl"><dt>المسار</dt><dd><?= $h($TR[$a['track']][0] ?? '') ?></dd><dt>الكلية</dt><dd><?= $h($a['college'] ? ($UWCIV_COLLEGES[$a['college']]['n'] ?? '') : '—') ?></dd><dt>الرتبة</dt><dd><?= $h($a['rank'] ?: '—') ?></dd><dt>الخبرة</dt><dd><?= $h($a['exp'] !== '' ? $a['exp'] . ' سنوات' : '—') ?></dd><dt>اللغات</dt><dd><?= $h(implode('، ', $a['langs'] ?? []) ?: '—') ?></dd><dt>الدولة</dt><dd><?= $h($a['country']) ?></dd><dt>الجوال</dt><dd dir="ltr" style="text-align:right"><?= $h($a['phone']) ?></dd><?php if ($a['link']): ?><dt>رابط الأعمال</dt><dd><a href="<?= $h($a['link']) ?>" target="_blank" rel="noopener"><?= $h($a['link']) ?></a></dd><?php endif; ?></dl>
      <?php if ($a['cover']): ?><div class="ja-cover"><?= nl2br($h($a['cover'])) ?></div><?php endif; ?>
      <div class="ja-btns"><a class="p" href="?cv=<?= $h($a['cv']) ?>" target="_blank"><i class="far fa-file-pdf"></i> السيرة الذاتية</a><a href="mailto:<?= $h($a['email']) ?>?subject=<?= rawurlencode('طلب الانضمام ' . $a['ref']) ?>"><i class="far fa-envelope"></i> مراسلة</a><a href="https://wa.me/<?= preg_replace('/\D/', '', $a['phone']) ?>" target="_blank"><i class="fab fa-whatsapp"></i> واتساب</a></div>
      <div class="ja-hist"><?php foreach ($a['history'] as $hx): ?><span><?= $h($ST[$hx[0]][0] ?? $hx[0]) ?> · <?= date('Y/m/d', strtotime($hx[1])) ?></span><?php endforeach; ?></div>
    </div>
    <div class="ja-st"><form method="post"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="act" value="status"><input type="hidden" name="ref" value="<?= $h($a['ref']) ?>">
      <label>حالة الطلب<select name="status"><?php foreach ($ST as $k => $x): ?><option value="<?= $k ?>" <?= $a['status'] === $k ? 'selected' : '' ?>><?= $x[0] ?></option><?php endforeach; ?></select></label>
      <label>رسالة تظهر للمتقدم عند المتابعة<textarea name="note" rows="3" placeholder="مثال: موعد المقابلة يوم … الساعة …"><?= $h($a['note'] ?? '') ?></textarea></label>
      <label>ملاحظة داخلية (لا يراها المتقدم)<textarea name="internal" rows="2"><?= $h($a['internal'] ?? '') ?></textarea></label>
      <label class="chk"><input type="checkbox" name="notify" value="1" checked> إشعار المتقدم بالبريد</label>
      <button><i class="fas fa-save"></i> حفظ التحديث</button></form></div>
  </div>
</details>
<?php endforeach; ?>

<?php else: ?>
<?php foreach (array_merge($pos, [['id' => '', 'track' => 'faculty', 'title' => '', 'college' => '', 'desc' => '', 'type' => 'عن بُعد', 'open' => true]]) as $p): $t = $TR[$p['track']] ?? $TR['faculty']; ?>
<form method="post" class="ja-pf" style="--c:<?= $t[2] ?><?= $p['id'] === '' ? ';border-style:dashed;box-shadow:none;border:2px dashed #c9d9cf' : '' ?>">
  <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="act" value="pos_save"><input type="hidden" name="tab" value="pos"><input type="hidden" name="id" value="<?= $h($p['id']) ?>">
  <input name="title" value="<?= $h($p['title']) ?>" placeholder="<?= $p['id'] === '' ? 'إضافة شاغر جديد: المسمى الوظيفي' : '' ?>" <?= $p['id'] !== '' ? 'required' : '' ?>>
  <select name="track"><?php foreach ($TR as $k => $x): ?><option value="<?= $k ?>" <?= $p['track'] === $k ? 'selected' : '' ?>><?= $x[0] ?></option><?php endforeach; ?></select>
  <select name="college"><option value="">— بدون كلية —</option><?php foreach ($UWCIV_COLLEGES as $s => $c): ?><option value="<?= $s ?>" <?= $p['college'] === $s ? 'selected' : '' ?>><?= $h($c['n']) ?></option><?php endforeach; ?></select>
  <input name="type" value="<?= $h($p['type']) ?>" placeholder="نوع العمل">
  <div class="acts"><button class="s"><i class="fas fa-save"></i> <?= $p['id'] === '' ? 'إضافة' : 'حفظ' ?></button><?php if ($p['id'] !== ''): ?><button class="d" formaction="?" name="act" value="pos_del" onclick="return confirm('حذف هذا الشاغر؟')"><i class="fas fa-trash"></i></button><?php endif; ?></div>
  <textarea name="desc" rows="2" placeholder="وصف مختصر للمهام"><?= $h($p['desc']) ?></textarea>
  <label class="chk"><input type="checkbox" name="open" value="1" <?= !empty($p['open']) ? 'checked' : '' ?>> معروض للتقديم</label>
</form>
<?php endforeach; ?>
<?php endif; ?>
<?php uwciv_admin_close(); ?>
