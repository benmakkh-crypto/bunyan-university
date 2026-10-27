<?php
/* إدارة أعضاء الجامعة — للمشرف العام فقط */
require_once $_SERVER['DOCUMENT_ROOT'] . '/db_config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/admin_ui.php';
uwciv_admin_gate();
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/members.php';
if (empty($_SESSION['mem_csrf'])) $_SESSION['mem_csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['mem_csrf'];
$G = uwciv_member_groups(); $LD = uwciv_member_link_defs();
$M = uwciv_members();
$msg = $err = '';
$cur = isset($G[$_GET['g'] ?? '']) ? $_GET['g'] : 'board';

function mem_photo($field, &$err)
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 3 * 1024 * 1024) { $err = 'الصورة يجب ألا تتجاوز 3 ميجابايت.'; return null; }
    $info = @getimagesize($f['tmp_name']);
    $map = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($map[$info[2]])) { $err = 'الصورة يجب أن تكون JPG أو PNG أو WEBP.'; return null; }
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/assets/members';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    if (!is_file($dir . '/.htaccess')) @file_put_contents($dir . '/.htaccess', "<FilesMatch \"\\.(php|phtml|phar|pl|py|cgi|sh)$\">\n  Require all denied\n</FilesMatch>\n");
    $name = 'm-' . bin2hex(random_bytes(6)) . '.' . $map[$info[2]];
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) { $err = 'تعذّر حفظ الصورة.'; return null; }
    return '/assets/members/' . $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, (string) ($_POST['csrf'] ?? ''))) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    else {
        $act = $_POST['act'] ?? ''; $id = (string) ($_POST['id'] ?? '');
        $idx = null; foreach ($M as $i => $x) if ($x['id'] === $id) { $idx = $i; break; }
        if ($act === 'save' || $act === 'add') {
            $g = isset($G[$_POST['g'] ?? '']) ? $_POST['g'] : $cur;
            $row = $act === 'add' ? ['id' => bin2hex(random_bytes(5)), 'photo' => '', 'lead' => false] : $M[$idx];
            $row['g'] = $g;
            foreach (['name' => 120, 'title' => 120, 'spec' => 160, 'bio' => 900, 'email' => 150] as $k => $n) $row[$k] = trim(mb_substr((string) ($_POST[$k] ?? ''), 0, $n));
            if ($row['email'] !== '' && !filter_var($row['email'], FILTER_VALIDATE_EMAIL)) { $err = 'البريد الإلكتروني غير صحيح.'; }
            $row['links'] = [];
            foreach ($LD as $k => $d) { $v = trim((string) ($_POST['links'][$k] ?? '')); if ($v === '') continue; if (!preg_match('~^https?://~i', $v)) $v = 'https://' . $v; if (filter_var($v, FILTER_VALIDATE_URL)) $row['links'][$k] = $v; }
            $row['lead'] = !empty($_POST['lead']);
            if (!empty($_POST['rmphoto'])) $row['photo'] = '';
            $ph = mem_photo('photo', $err); if ($ph) $row['photo'] = $ph;
            if ($row['title'] === '') $err = 'اكتب المسمّى أو المنصب.';
            if (!$err) {
                if ($act === 'add') $M[] = $row; else $M[$idx] = $row;
                $msg = $act === 'add' ? 'تمت إضافة العضو.' : 'تم حفظ بيانات العضو.'; $cur = $g;
            }
        } elseif ($act === 'del' && $idx !== null) { array_splice($M, $idx, 1); $msg = 'تم حذف العضو.'; }
        elseif (($act === 'up' || $act === 'down') && $idx !== null) {
            $g = $M[$idx]['g']; $step = $act === 'up' ? -1 : 1; $j = $idx + $step;
            while (isset($M[$j]) && $M[$j]['g'] !== $g) $j += $step;
            if (isset($M[$j])) { $t = $M[$j]; $M[$j] = $M[$idx]; $M[$idx] = $t; }
        }
        if (!$err && !uwciv_members_save($M)) $err = 'تعذّر حفظ الملف على الخادم.';
        $back = (string) ($_POST['back'] ?? '');
        if (!$err && strpos($back, '/teacher/') === 0) { header('Location: ' . strtok($back, '#') . (strpos($back, '?') === false ? '?' : '&') . 'ok=' . rawurlencode($msg ?: 'تم.')); exit; }
        if (!$err) { header('Location: /admin/members.php?g=' . $cur . '&ok=' . rawurlencode($msg ?: 'تم.')); exit; }
    }
}
if (isset($_GET['ok'])) $msg = (string) $_GET['ok'];
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
uwciv_admin_open('members', 'إدارة أعضاء الجامعة', 'أضف الأسماء والصور والمناصب، فتظهر فورًا في صفحة <a href="/teacher/index.php">أعضاء الجامعة</a>.');

function mem_form($x, $G, $LD, $h, $csrf, $cur)
{
    $new = $x === null; $x = $x ?: ['id' => '', 'g' => $cur, 'name' => '', 'title' => '', 'spec' => '', 'bio' => '', 'email' => '', 'photo' => '', 'links' => [], 'lead' => false]; ?>
  <form method="post" enctype="multipart/form-data" class="am-form">
    <input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="id" value="<?= $h($x['id']) ?>"><input type="hidden" name="act" value="<?= $new ? 'add' : 'save' ?>">
    <div class="am-row">
      <label>الاسم الكامل<input name="name" value="<?= $h($x['name']) ?>" placeholder="مثال: أ.د. عبد الله محمد"></label>
      <label>المنصب / المسمّى *<input name="title" value="<?= $h($x['title']) ?>" required></label>
      <label>التخصص أو الجهة<input name="spec" value="<?= $h($x['spec']) ?>"></label>
      <label>الفئة<select name="g"><?php foreach ($G as $k => $g): ?><option value="<?= $k ?>" <?= $x['g'] === $k ? 'selected' : '' ?>><?= $g[0] ?></option><?php endforeach; ?></select></label>
      <label class="w">نبذة مختصرة<textarea name="bio" rows="2"><?= $h($x['bio']) ?></textarea></label>
      <label>البريد الإلكتروني<input name="email" dir="ltr" value="<?= $h($x['email']) ?>"></label>
      <label>الصورة (JPG/PNG حتى 3MB)<input type="file" name="photo" accept="image/jpeg,image/png,image/webp"><?php if ($x['photo']): ?><span class="am-ph"><img src="<?= $h($x['photo']) ?>" alt=""><label class="chk"><input type="checkbox" name="rmphoto" value="1"> حذف الصورة</label></span><?php endif; ?></label>
      <?php foreach ($LD as $k => $d): ?><label><i class="<?= $d[1] ?>"></i> <?= $d[0] ?><input name="links[<?= $k ?>]" dir="ltr" value="<?= $h($x['links'][$k] ?? '') ?>" placeholder="https://"></label><?php endforeach; ?>
      <label class="chk w"><input type="checkbox" name="lead" value="1" <?= !empty($x['lead']) ? 'checked' : '' ?>> بطاقة مميّزة كبيرة (لرئيس المجلس أو القائد)</label>
    </div>
    <button class="am-btn"><i class="fas fa-save"></i> <?= $new ? 'إضافة العضو' : 'حفظ' ?></button>
  </form>
<?php }
?>
<style>
.am-tabs{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:18px}
.am-tabs a{background:#fff;border-radius:30px;padding:9px 16px;font-weight:800;color:var(--ink-3)!important;box-shadow:var(--sh-1);text-decoration:none!important}
.am-tabs a.on{background:linear-gradient(135deg,#f2d77e,#b98f25);color:#2a1c02!important}
.am-msg{border-radius:14px;padding:12px 16px;margin-bottom:14px;font-weight:700}.am-msg.ok{background:#e4f4ea;color:#12935a}.am-msg.er{background:#fdecef;color:#9b1f45}
.am-item{background:#fff;border-radius:18px;box-shadow:var(--sh-1);margin-bottom:10px;overflow:hidden}
.am-item>summary{display:flex;gap:14px;align-items:center;padding:12px 16px;cursor:pointer;list-style:none}
.am-item>summary::-webkit-details-marker{display:none}
.am-av{width:46px;height:52px;border-radius:23px 23px 8px 8px;background:linear-gradient(135deg,#f2d77e,#b98f25);overflow:hidden;flex:none;display:grid;place-items:center;color:#2a1c02}
.am-av img{width:100%;height:100%;object-fit:cover}
.am-item b{display:block;color:var(--ink)}.am-item small{color:var(--muted)}
.am-tools{margin-inline-start:auto;display:flex;gap:6px}
.am-tools form{margin:0}.am-tools button{border:0;background:var(--mist);width:34px;height:34px;border-radius:10px;cursor:pointer;color:var(--ink-3)}
.am-tools button.del{color:#c0392b}
.am-form{padding:16px;border-top:1px solid #edf3ef}
.am-row{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:12px}
.am-row label{display:flex;flex-direction:column;gap:6px;font-weight:700;font-size:13.5px;color:var(--ink-3)}
.am-row label.w{grid-column:1/-1}
.am-row input:not([type=checkbox]):not([type=file]),.am-row select,.am-row textarea{border:2px solid #e3ece6;border-radius:12px;padding:9px 12px;font-family:inherit;font-weight:500}
.am-row label.chk{flex-direction:row;align-items:center}
.am-ph{display:flex;gap:10px;align-items:center}.am-ph img{width:44px;height:50px;object-fit:cover;border-radius:10px}
.am-btn{margin-top:14px;border:0;border-radius:30px;padding:11px 22px;font-weight:900;background:linear-gradient(135deg,var(--teal),var(--ink-3));color:#fff;cursor:pointer}
.am-add{border:2px dashed #c9d9cf;background:transparent}
.am-add>summary{font-weight:900;color:var(--teal)}
</style>
<?php if ($msg): ?><div class="am-msg ok"><i class="fas fa-check-circle"></i> <?= $h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="am-msg er"><i class="fas fa-exclamation-circle"></i> <?= $h($err) ?></div><?php endif; ?>
<div class="am-tabs"><?php foreach ($G as $k => $g): $n = count(array_filter($M, function ($x) use ($k) { return $x['g'] === $k; })); ?><a class="<?= $k === $cur ? 'on' : '' ?>" href="?g=<?= $k ?>"><i class="fas <?= $g[1] ?>"></i> <?= $g[0] ?> (<?= $n ?>)</a><?php endforeach; ?><a href="/teacher/index.php?g=<?= $cur ?>" target="_blank"><i class="fas fa-eye"></i> معاينة الصفحة</a></div>

<details class="am-item am-add"><summary><i class="fas fa-plus-circle"></i> إضافة عضو جديد إلى «<?= $G[$cur][0] ?>»</summary><?php mem_form(null, $G, $LD, $h, $csrf, $cur); ?></details>

<?php foreach ($M as $x): if ($x['g'] !== $cur) continue; ?>
<details class="am-item">
  <summary>
    <span class="am-av"><?= $x['photo'] ? '<img src="' . $h($x['photo']) . '" alt="">' : '<i class="fas fa-user"></i>' ?></span>
    <span><b><?= $x['name'] !== '' ? $h($x['name']) : '<span style="color:#b98f25">— لم يُضف الاسم بعد —</span>' ?></b><small><?= $h($x['title']) ?><?= !empty($x['lead']) ? ' · <i class="fas fa-crown" style="color:#b98f25"></i>' : '' ?></small></span>
    <span class="am-tools" onclick="event.stopPropagation()">
      <?php foreach (['up' => 'fa-arrow-up', 'down' => 'fa-arrow-down', 'del' => 'fa-trash'] as $a => $ic): ?>
      <form method="post" <?= $a === 'del' ? 'onsubmit="return confirm(\'حذف هذا العضو؟\')"' : '' ?>><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="id" value="<?= $h($x['id']) ?>"><input type="hidden" name="act" value="<?= $a ?>"><button class="<?= $a === 'del' ? 'del' : '' ?>" title="<?= ['up' => 'لأعلى', 'down' => 'لأسفل', 'del' => 'حذف'][$a] ?>"><i class="fas <?= $ic ?>"></i></button></form>
      <?php endforeach; ?>
    </span>
  </summary>
  <?php mem_form($x, $G, $LD, $h, $csrf, $cur); ?>
</details>
<?php endforeach; ?>
<?php uwciv_admin_close(); ?>
