<?php
/* إدارة وثائق التخرج */
require_once dirname(__DIR__, 2) . '/inc/admin_ui.php';
uwciv_admin_gate(['title' => 'وثائق التخرج', 'scope' => 'grad', 'hint' => 'موظفو شؤون الخريجين: ادخلوا بحساباتكم المعتادة.', 'check' => function ($u, $p) {
    try { $st = db_pdo('logweb')->prepare('SELECT name, password FROM user_adn WHERE username = ? LIMIT 1'); $st->execute([$u]); $r = $st->fetch(PDO::FETCH_ASSOC);
        return ($r && password_verify($p, $r['password'])) ? ($r['name'] ?: $u) : null; } catch (Throwable $e) { return null; }
}]);
$DIR = __DIR__ . '/files'; $msg = ''; $err = '';
try { $db = db_pdo('logweb'); $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); } catch (Throwable $e) { $db = null; $err = 'تعذّر الاتصال بقاعدة البيانات حاليًا.'; }

if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!uwciv_csrf_ok()) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    else try {
        $act = $_POST['act'] ?? ''; $id = (int) ($_POST['id'] ?? 0);
        if ($act === 'save') {
            $ref = trim(mb_substr((string) ($_POST['ref'] ?? ''), 0, 60));
            $pass = trim(mb_substr((string) ($_POST['pass'] ?? ''), 0, 60));
            if ($ref === '' || $pass === '') throw new RuntimeException('اكتب رقم المرجع ورقم الهوية.');
            $st = $db->prepare('SELECT id FROM student WHERE id_ref = ? AND id <> ?'); $st->execute([$ref, $id]);
            if ($st->fetchColumn()) throw new RuntimeException('رقم المرجع ' . $ref . ' مستخدم لوثيقة أخرى.');
            $file = uwciv_admin_upload_pdf('pdf', $DIR, 30);
            if ($id) {
                $o = $db->prepare('SELECT pdf_file FROM student WHERE id = ?'); $o->execute([$id]); $old = basename((string) $o->fetchColumn());
                $db->prepare('UPDATE student SET id_ref = ?, id_pass = ?, pdf_file = ? WHERE id = ?')->execute([$ref, $pass, $file ?: $old, $id]);
                if ($file && $old && is_file("$DIR/$old")) @unlink("$DIR/$old");
                $msg = 'تم حفظ الوثيقة ' . $ref . '.';
            } else {
                if (!$file) throw new RuntimeException('أرفق ملف الوثيقة بصيغة PDF.');
                $db->prepare('INSERT INTO student (id_ref, id_pass, pdf_file) VALUES (?, ?, ?)')->execute([$ref, $pass, $file]);
                $msg = 'تمت إضافة الوثيقة ' . $ref . '.';
            }
        } elseif ($act === 'del') {
            $o = $db->prepare('SELECT pdf_file FROM student WHERE id = ?'); $o->execute([$id]); $old = basename((string) $o->fetchColumn());
            $db->prepare('DELETE FROM student WHERE id = ?')->execute([$id]);
            if ($old && is_file("$DIR/$old")) @unlink("$DIR/$old");
            $msg = 'تم حذف الوثيقة.';
        }
        if ($msg) { header('Location: index.php?ok=' . rawurlencode($msg)); exit; }
    } catch (Throwable $e) { $err = $e instanceof RuntimeException ? $e->getMessage() : 'تعذّرت العملية.'; }
}
if (isset($_GET['ok'])) $msg = (string) $_GET['ok'];
if (isset($_GET['f'])) { /* عرض ملف للمسؤول */
    $f = basename((string) $_GET['f']); if (!is_file("$DIR/$f")) { http_response_code(404); exit('الملف غير موجود'); }
    header('Content-Type: application/pdf'); header('Content-Disposition: inline; filename="' . $f . '"'); header('X-Content-Type-Options: nosniff'); readfile("$DIR/$f"); exit;
}
$rows = []; if ($db) { try { $rows = $db->query('SELECT * FROM student ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC); } catch (Throwable $e) {} }
$missing = 0; foreach ($rows as $r) if (!is_file("$DIR/" . basename((string) $r['pdf_file']))) $missing++;
$h = 'uwciv_h';
uwciv_admin_open('grad', 'وثائق التخرج', 'ارفع وثيقة كل خريج برقم مرجع ورقم هوية، فيستطيع عرضها وتحميلها من صفحة <a href="/pdf_grad/index.php" target="_blank">التحقق من وثيقة التخرج</a>.', '<a class="ad-b g" href="/pdf_grad/index.php" target="_blank"><i class="fas fa-eye"></i> صفحة التحقق</a><button class="ad-b p" type="button" data-gd-new><i class="fas fa-plus"></i> وثيقة جديدة</button>');
uwciv_admin_toast($err ?: $msg, (bool) $err);
?>
<style>
.gd-ref{font-weight:900;color:var(--ink);direction:ltr;display:inline-block;font-family:ui-monospace,Consolas,monospace;letter-spacing:.5px}
.gd-pass{direction:ltr;display:inline-block;font-family:ui-monospace,Consolas,monospace;color:var(--muted);cursor:pointer;filter:blur(4px);transition:.2s;user-select:none}
.gd-pass.on{filter:none;user-select:text}
.gd-act{display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap}.gd-act form{margin:0}
.gd-ic{width:40px;height:48px;border-radius:6px 10px 10px 6px;display:inline-grid;place-items:center;background:linear-gradient(160deg,#9b1f45,#5e0f28);color:#fff;font-size:16px;box-shadow:inset 4px 0 0 rgba(0,0,0,.2);vertical-align:middle}
@media(max-width:760px){.ad-table thead{display:none}.ad-table tr{display:grid;padding:10px 0;border-bottom:1px solid #eef3ef}.ad-table td{border:0;padding:6px 14px}.gd-act{justify-content:flex-start}}
</style>
<div class="cp-kpis">
  <div class="cp-kpi" style="--c:#9b1f45"><i class="fas fa-certificate"></i><span><b><?= count($rows) ?></b><span>وثيقة مرفوعة</span></span></div>
  <div class="cp-kpi" style="--c:#1f7a45"><i class="fas fa-check-double"></i><span><b><?= count($rows) - $missing ?></b><span>جاهزة للعرض</span></span></div>
  <div class="cp-kpi" style="--c:#c9962a"><i class="fas fa-exclamation-triangle"></i><span><b><?= $missing ?></b><span>ملف مفقود</span></span></div>
  <a class="cp-kpi" style="--c:#2c5fd0" href="/pdf_grad/index.php" target="_blank"><i class="fas fa-shield-alt"></i><span><b style="font-size:16px">رقم مرجع + هوية</b><span>طريقة التحقق</span><small>معاينة الصفحة ←</small></span></a>
</div>
<div class="ad-bar"><div class="ad-in"><i class="fas fa-search"></i><input placeholder="ابحث برقم المرجع أو رقم الهوية…" data-filter="#gdl"></div></div>
<div class="ad-tw"><table class="ad-table"><thead><tr><th>الوثيقة</th><th>رقم الهوية</th><th>الملف</th><th></th></tr></thead><tbody id="gdl">
<?php foreach ($rows as $r): $f = basename((string) $r['pdf_file']); $ok = $f !== '' && is_file("$DIR/$f"); ?>
<tr class="fi" data-s="<?= $h($r['id_ref'] . ' ' . $r['id_pass']) ?>">
  <td><div class="ad-who"><span class="gd-ic"><i class="fas fa-award"></i></span><span><b class="gd-ref"><?= $h($r['id_ref']) ?></b><small>#<?= (int) $r['id'] ?></small></span></div></td>
  <td><span class="gd-pass" title="اضغط للإظهار" onclick="this.classList.toggle('on')"><?= $h($r['id_pass']) ?></span></td>
  <td><?= $ok ? '<a class="cp-pill y" href="?f=' . rawurlencode($f) . '" target="_blank"><i class="far fa-file-pdf"></i> عرض</a> <small dir="ltr" style="color:var(--muted)">' . number_format(filesize("$DIR/$f") / 1048576, 2) . ' MB</small>' : '<span class="cp-pill r"><i class="fas fa-exclamation-triangle"></i> مفقود</span>' ?></td>
  <td><div class="gd-act">
    <button class="cp-b g sm" type="button" data-gd-share="<?= $h($r['id_ref']) ?>" title="نسخ رسالة للخريج"><i class="far fa-copy"></i> رسالة للخريج</button>
    <button class="cp-b g sm" type="button" data-gd-edit='<?= $h(json_encode(['id' => (int) $r['id'], 'ref' => $r['id_ref'], 'pass' => $r['id_pass']], JSON_UNESCAPED_UNICODE)) ?>'><i class="fas fa-pen"></i> تعديل</button>
    <form method="post" data-confirm="حذف الوثيقة <?= $h($r['id_ref']) ?> وملفها نهائيًا؟"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="del"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="cp-b r sm" title="حذف"><i class="fas fa-trash"></i></button></form>
  </div></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="cp-card fi-empty" <?= $rows ? 'hidden' : '' ?>><div class="cp-empty"><i class="fas fa-certificate"></i><?= $rows ? 'لا نتائج مطابقة.' : 'لا توجد وثائق بعد — أضف أول وثيقة تخرج.' ?></div></div>

<dialog class="ad-dlg" id="gdd" style="width:min(560px,94vw)"><form method="post" enctype="multipart/form-data"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="id" id="gid">
  <h3><i class="fas fa-certificate" style="color:#9b1f45"></i> <span id="gt">وثيقة جديدة</span><button type="button" class="x" data-dlg-close><i class="fas fa-times"></i></button></h3>
  <div class="cp-f">
    <label>رقم المرجع *<div class="cp-in"><i class="fas fa-hashtag"></i><input name="ref" id="gr" required dir="ltr" placeholder="BIU-2026-0001"><button type="button" id="ggen" title="توليد رقم"><i class="fas fa-magic"></i></button></div></label>
    <label>رقم الهوية / الجواز *<div class="cp-in"><i class="far fa-id-card"></i><input name="pass" id="gp" required dir="ltr"></div></label>
    <div class="w"><label class="ad-drop"><input type="file" name="pdf" accept="application/pdf,.pdf"><i class="far fa-file-pdf"></i><b id="gdb">اسحب ملف الوثيقة (PDF) هنا أو اضغط للاختيار</b><small>حتى 30 ميغابايت</small></label></div>
  </div>
  <div class="cp-btns"><button class="cp-b p"><i class="fas fa-save"></i> حفظ</button><button type="button" class="cp-b g" data-dlg-close>إلغاء</button></div>
</form></dialog>
<script>
(function(){var d=document.getElementById('gdd'),$=function(i){return document.getElementById(i)};
function open(x){x=x||{};d.querySelector('form').reset();$('gid').value=x.id||'';$('gr').value=x.ref||'';$('gp').value=x.pass||'';$('gt').textContent=x.id?'تعديل الوثيقة':'وثيقة جديدة';$('gdb').textContent=x.id?'اضغط لاستبدال ملف الوثيقة (اختياري)':'اسحب ملف الوثيقة (PDF) هنا أو اضغط للاختيار';d.showModal();$('gr').focus();}
document.querySelector('[data-gd-new]').onclick=function(){open()};
document.querySelectorAll('[data-gd-edit]').forEach(function(b){b.onclick=function(){open(JSON.parse(b.getAttribute('data-gd-edit')))}});
$('ggen').onclick=function(){$('gr').value='BIU-'+new Date().getFullYear()+'-'+String(Math.floor(1000+Math.random()*9000));};
document.querySelectorAll('[data-gd-share]').forEach(function(b){b.onclick=function(){var t='السلام عليكم ورحمة الله،\nوثيقة تخرجك من جامعة بنيان العالمية جاهزة.\nرقم المرجع: '+b.getAttribute('data-gd-share')+'\nادخل برقم المرجع ورقم هويتك من الرابط:\nhttps://your-domain.com/pdf_grad/index.php';navigator.clipboard.writeText(t).then(function(){uwcToast('نُسخت الرسالة — الصقها في واتساب أو البريد')});}});})();
</script>
<?php uwciv_admin_close(); ?>
