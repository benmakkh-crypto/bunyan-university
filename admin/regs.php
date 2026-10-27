<?php
/* إدارة اللوائح والأنظمة ومصفوفة الصلاحيات — للمشرف العام فقط */
require_once $_SERVER['DOCUMENT_ROOT'] . '/db_config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/admin_ui.php';
uwciv_admin_gate();
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/regs.php';
if (empty($_SESSION['regs_csrf'])) $_SESSION['regs_csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['regs_csrf'];
$R = uwciv_regs(); $CATS = uwciv_reg_cats(); $ST = uwciv_reg_status();
$msg = $err = '';

function regs_pdf($field, &$err)
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) { $err = 'تعذّر رفع الملف (قد يكون أكبر من الحد المسموح في الاستضافة).'; return null; }
    if ($f['size'] > 20 * 1024 * 1024) { $err = 'حجم الملف أكبر من 20 ميجابايت.'; return null; }
    $head = (string) @file_get_contents($f['tmp_name'], false, null, 0, 5);
    if ($head !== '%PDF-') { $err = 'الملف يجب أن يكون PDF.'; return null; }
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/regs';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    if (!is_file($dir . '/.htaccess')) @file_put_contents($dir . '/.htaccess', "<FilesMatch \"\\.(php|phtml|phar|pl|py|cgi|sh)$\">\n  Require all denied\n</FilesMatch>\n");
    $name = 'reg-' . date('Ymd') . '-' . bin2hex(random_bytes(5)) . '.pdf';
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) { $err = 'تعذّر حفظ الملف.'; return null; }
    return '/assets/regs/' . $name;
}
$lines = function ($t) { return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $t)), 'strlen')); };

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    else {
        $act = $_POST['act'] ?? '';
        $id = (string) ($_POST['id'] ?? '');
        $idx = null; foreach ($R['docs'] as $i => $d) if ($d['id'] === $id) { $idx = $i; break; }
        if ($act === 'doc_save' || $act === 'doc_add') {
            $d = $act === 'doc_add' ? ['id' => 'doc-' . bin2hex(random_bytes(4)), 'file' => '', 'articles' => []] : $R['docs'][$idx];
            $d['title'] = trim(mb_substr((string) ($_POST['title'] ?? ''), 0, 160));
            $d['desc'] = trim(mb_substr((string) ($_POST['desc'] ?? ''), 0, 300));
            $d['cat'] = isset($CATS[$_POST['cat'] ?? '']) ? $_POST['cat'] : 'general';
            $d['status'] = isset($ST[$_POST['status'] ?? '']) ? $_POST['status'] : 'draft';
            if (isset($_POST['articles'])) $d['articles'] = $lines($_POST['articles']);
            if (!empty($_POST['rmfile'])) $d['file'] = '';
            $pdf = regs_pdf('pdf', $err); if ($pdf) $d['file'] = $pdf;
            $d['updated'] = date('Y-m-d');
            if ($d['title'] === '') $err = 'اكتب عنوان الوثيقة.';
            if (!$err) { if ($act === 'doc_add') $R['docs'][] = $d; else $R['docs'][$idx] = $d; $msg = $act === 'doc_add' ? 'تمت إضافة الوثيقة.' : 'تم حفظ الوثيقة.'; }
        } elseif ($act === 'doc_del' && $idx !== null) { array_splice($R['docs'], $idx, 1); $msg = 'تم حذف الوثيقة.'; }
        elseif ($act === 'powers') {
            $P = [];
            foreach (($_POST['pw'] ?? []) as $p) {
                $t = trim((string) ($p['title'] ?? '')); if ($t === '' || !empty($p['del'])) continue;
                $P[] = [max(1, min(9, (int) ($p['lv'] ?? 1))), preg_replace('/[^a-z0-9-]/', '', (string) ($p['icon'] ?? 'fa-user')) ?: 'fa-user', $t, trim((string) ($p['desc'] ?? '')), $lines($p['list'] ?? '')];
            }
            usort($P, function ($a, $b) { return $a[0] <=> $b[0]; });
            $R['powers'] = $P; $msg = 'تم حفظ مصفوفة الصلاحيات.';
        }
        if (!$err) {
            if (uwciv_regs_save($R)) { header('Location: /admin/regs.php?ok=' . rawurlencode($msg) . ($act === 'powers' ? '#powers' : '')); exit; }
            $err = 'تعذّر حفظ البيانات على الخادم.';
        }
    }
}
if (isset($_GET['ok'])) $msg = (string) $_GET['ok'];
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
uwciv_admin_open('regs', 'إدارة اللوائح والأنظمة', 'ارفع ملفات PDF، وعدّل نصوص اللوائح وحالتها، وحدّث صلاحيات كل جهة — تظهر التعديلات فورًا في <a href="/regulations.php">صفحة اللوائح</a>.');
function regs_form($d, $CATS, $ST, $h, $csrf)
{
    $new = $d === null; $d = $d ?: ['id' => '', 'title' => '', 'desc' => '', 'cat' => 'general', 'status' => 'draft', 'articles' => [], 'file' => '']; ?>
  <form method="post" enctype="multipart/form-data" class="ar-f">
    <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="act" value="<?= $new ? 'doc_add' : 'doc_save' ?>"><input type="hidden" name="id" value="<?= $h($d['id']) ?>">
    <div class="ar-g">
      <label class="w">العنوان *<input name="title" value="<?= $h($d['title']) ?>" required></label>
      <label class="w">وصف مختصر<input name="desc" value="<?= $h($d['desc']) ?>"></label>
      <label>الفئة<select name="cat"><?php foreach ($CATS as $k => $c): ?><option value="<?= $k ?>" <?= $d['cat'] === $k ? 'selected' : '' ?>><?= $c[0] ?></option><?php endforeach; ?></select></label>
      <label>الحالة<select name="status"><?php foreach ($ST as $k => $s): ?><option value="<?= $k ?>" <?= $d['status'] === $k ? 'selected' : '' ?>><?= $s[0] ?></option><?php endforeach; ?></select></label>
      <?php if (empty($d['college'])): ?><label class="w">مواد اللائحة (كل مادة في سطر — تُعرض كوثيقة إلكترونية إن لم يُرفع ملف)<textarea name="articles" rows="6"><?= $h(implode("\n", $d['articles'] ?? [])) ?></textarea></label>
      <?php else: ?><p class="w ar-note"><i class="fas fa-info-circle"></i> هذا الدليل يُولَّد تلقائيًا من بيانات الكلية. ارفع ملف PDF إن أردت استبداله بنسخة رسمية.</p><?php endif; ?>
      <label class="w">ملف PDF (حتى 20MB)<input type="file" name="pdf" accept="application/pdf">
        <?php if ($d['file']): ?><span class="ar-file"><a href="<?= $h($d['file']) ?>" target="_blank"><i class="far fa-file-pdf"></i> الملف الحالي</a><label class="chk"><input type="checkbox" name="rmfile" value="1"> إزالة الملف</label></span><?php endif; ?></label>
    </div>
    <button class="ar-btn"><i class="fas fa-save"></i> <?= $new ? 'إضافة الوثيقة' : 'حفظ' ?></button>
  </form>
<?php }
?>
<style>
.ar-msg{border-radius:14px;padding:12px 16px;margin:0 0 14px;font-weight:800}.ar-msg.ok{background:#e4f4ea;color:#12935a}.ar-msg.er{background:#fdecef;color:#9b1f45}
.ar-nav{display:flex;gap:8px;flex-wrap:wrap;margin:0 0 18px}.ar-msg+.ar-nav{margin-top:0}
.ar-nav a{background:#fff;border-radius:30px;padding:9px 16px;font-weight:800;box-shadow:var(--sh-1);text-decoration:none!important;color:var(--ink-3)!important}
.ar-h{font-size:22px;font-weight:900;color:var(--ink);margin:26px 0 12px;display:flex;gap:10px;align-items:center}
.ar-item{background:#fff;border-radius:16px;box-shadow:var(--sh-1);margin-bottom:8px;border-inline-start:4px solid var(--c);scroll-margin-top:90px}
.ar-item>summary{list-style:none;cursor:pointer;display:flex;gap:12px;align-items:center;padding:12px 16px}
.ar-item>summary::-webkit-details-marker{display:none}
.ar-item b{color:var(--ink)}.ar-item small{color:var(--muted);font-size:12.5px}
.ar-badge{margin-inline-start:auto;border-radius:20px;padding:2px 10px;font-size:12px;font-weight:800;color:#fff;background:var(--sc)}
.ar-del{margin:0}.ar-del button{border:0;background:#fdecef;color:#c0392b;border-radius:10px;width:34px;height:34px;cursor:pointer}
.ar-f{padding:0 16px 16px;border-top:1px solid #edf3ef}
.ar-g{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding-top:14px}
.ar-g label{display:flex;flex-direction:column;gap:6px;font-weight:800;font-size:13.5px;color:var(--ink-3)}
.ar-g .w{grid-column:1/-1}
.ar-g input:not([type=checkbox]):not([type=file]),.ar-g select,.ar-g textarea{border:2px solid #e3ece6;border-radius:12px;padding:9px 12px;font-family:inherit;font-weight:500;font-size:14.5px;width:100%;box-sizing:border-box}
.ar-g textarea{line-height:1.9}
.ar-file{display:flex;gap:14px;align-items:center;font-weight:600}.chk{flex-direction:row!important;align-items:center;gap:6px}
.ar-note{margin:0;color:var(--muted);font-weight:600;font-size:13.5px}
.ar-btn{margin-top:12px;border:0;border-radius:30px;padding:11px 22px;font-weight:900;background:linear-gradient(135deg,var(--teal),var(--ink-3));color:#fff;cursor:pointer;font-family:inherit}
.ar-add{background:transparent;border:2px dashed #c9d9cf;box-shadow:none}
.pw-ed{background:#fff;border-radius:16px;box-shadow:var(--sh-1);padding:14px 16px;margin-bottom:10px;display:grid;grid-template-columns:70px 150px 1fr 1fr;gap:10px;align-items:start}
.pw-ed input,.pw-ed textarea{border:2px solid #e3ece6;border-radius:10px;padding:8px 10px;font-family:inherit;width:100%;box-sizing:border-box;font-size:14px}
.pw-ed textarea{grid-column:1/-1;line-height:1.9}
.pw-ed .chk{grid-column:1/-1;font-size:13px;color:#c0392b;font-weight:700}
@media(max-width:800px){.ar-g{grid-template-columns:1fr}.pw-ed{grid-template-columns:1fr 1fr}}
</style>
<?php if ($msg): ?><div class="ar-msg ok"><i class="fas fa-check-circle"></i> <?= $h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="ar-msg er"><i class="fas fa-exclamation-circle"></i> <?= $h($err) ?></div><?php endif; ?>
<nav class="ar-nav"><a href="#docs"><i class="fas fa-folder-open"></i> الوثائق (<?= count($R['docs']) ?>)</a><a href="#powers"><i class="fas fa-sitemap"></i> الصلاحيات (<?= count($R['powers']) ?>)</a><a href="/regulations.php" target="_blank"><i class="fas fa-eye"></i> معاينة الصفحة</a><a href="/admin/settings.php"><i class="fas fa-cog"></i> الإعدادات</a></nav>

<h3 class="ar-h" id="docs"><i class="fas fa-folder-open" style="color:var(--teal)"></i> الوثائق والأدلة</h3>
<details class="ar-item ar-add" style="--c:#1f7a45"><summary><i class="fas fa-plus-circle" style="color:var(--teal)"></i><b>إضافة لائحة أو دليل جديد</b></summary><?php regs_form(null, $CATS, $ST, $h, $csrf); ?></details>
<?php foreach ($CATS as $ck => $c): ?>
  <p style="margin:18px 0 8px;font-weight:900;color:<?= $c[2] ?>"><i class="fas <?= $c[1] ?>"></i> <?= $c[0] ?></p>
  <?php foreach ($R['docs'] as $d): if ($d['cat'] !== $ck) continue; $s = $ST[$d['status']] ?? $ST['soon']; ?>
  <details class="ar-item" id="d-<?= $h($d['id']) ?>" style="--c:<?= $c[2] ?>">
    <summary><i class="far <?= $d['file'] ? 'fa-file-pdf' : 'fa-file-alt' ?>" style="color:var(--c);font-size:20px"></i><span><b><?= $h($d['title']) ?></b><br><small><?= $d['file'] ? 'ملف PDF مرفوع' : (!empty($d['college']) ? 'دليل مولَّد من بيانات الكلية' : count($d['articles'] ?? []) . ' مادة') ?></small></span>
      <span class="ar-badge" style="--sc:<?= $s[1] ?>"><?= $s[0] ?></span>
      <form method="post" class="ar-del" onsubmit="return confirm('حذف هذه الوثيقة؟')" onclick="event.stopPropagation()"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="act" value="doc_del"><input type="hidden" name="id" value="<?= $h($d['id']) ?>"><button title="حذف"><i class="fas fa-trash"></i></button></form>
    </summary>
    <?php regs_form($d, $CATS, $ST, $h, $csrf); ?>
  </details>
  <?php endforeach; ?>
<?php endforeach; ?>

<h3 class="ar-h" id="powers"><i class="fas fa-sitemap" style="color:var(--teal)"></i> مصفوفة الصلاحيات</h3>
<p style="color:var(--muted);margin:-4px 0 14px">المستوى 1 أعلى الهرم. اكتب كل صلاحية في سطر. الأيقونة اسم من Font Awesome مثل fa-user-tie.</p>
<form method="post">
  <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="act" value="powers">
  <?php foreach (array_merge($R['powers'], [[9, 'fa-user', '', '', []]]) as $i => $p): ?>
  <div class="pw-ed"<?= $p[2] === '' ? ' style="border:2px dashed #c9d9cf;box-shadow:none"' : '' ?>>
    <input type="number" min="1" max="9" name="pw[<?= $i ?>][lv]" value="<?= (int) $p[0] ?>" title="المستوى">
    <input name="pw[<?= $i ?>][icon]" value="<?= $h($p[1]) ?>" dir="ltr" title="الأيقونة">
    <input name="pw[<?= $i ?>][title]" value="<?= $h($p[2]) ?>" placeholder="<?= $p[2] === '' ? 'إضافة جهة جديدة: اسم المنصب أو المجلس' : '' ?>">
    <input name="pw[<?= $i ?>][desc]" value="<?= $h($p[3]) ?>" placeholder="وصف مختصر">
    <textarea name="pw[<?= $i ?>][list]" rows="<?= max(2, count($p[4])) ?>" placeholder="الصلاحيات — كل صلاحية في سطر"><?= $h(implode("\n", $p[4])) ?></textarea>
    <?php if ($p[2] !== ''): ?><label class="chk"><input type="checkbox" name="pw[<?= $i ?>][del]" value="1"> حذف هذه الجهة</label><?php endif; ?>
  </div>
  <?php endforeach; ?>
  <button class="ar-btn"><i class="fas fa-save"></i> حفظ مصفوفة الصلاحيات</button>
</form>
<script>if(location.hash&&location.hash.indexOf('#d-')===0){var e=document.getElementById(location.hash.slice(1));if(e){e.open=true;e.scrollIntoView({block:'start'});}}</script>
<?php uwciv_admin_close(); ?>
