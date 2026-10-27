<?php
/* إدارة المكتبة الرقمية */
require_once dirname(__DIR__, 2) . '/inc/admin_ui.php';
uwciv_admin_gate(['title' => 'المكتبة الرقمية', 'scope' => 'library', 'hint' => 'أمناء المكتبة: ادخلوا بحساباتكم المعتادة.', 'check' => function ($u, $p) {
    try { $st = db_pdo('logweb')->prepare('SELECT fname, password FROM admins WHERE username = ? LIMIT 1'); $st->execute([$u]); $r = $st->fetch(PDO::FETCH_ASSOC);
        return ($r && password_verify($p, $r['password'])) ? ($r['fname'] ?: $u) : null; } catch (Throwable $e) { return null; }
}]);
$DIR = __DIR__ . '/files'; $msg = ''; $err = '';
try { $db = db_pdo('logweb'); $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); } catch (Throwable $e) { $db = null; $err = 'تعذّر الاتصال بقاعدة البيانات حاليًا.'; }

if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!uwciv_csrf_ok()) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    else try {
        $act = $_POST['act'] ?? '';
        if ($act === 'add') {
            $dep = trim(mb_substr((string) ($_POST['dep'] ?? ''), 0, 150));
            $name = trim(mb_substr((string) ($_POST['name'] ?? ''), 0, 250));
            if ($dep === '') throw new RuntimeException('اختر قسم الكتاب أو اكتبه.');
            $F = $_FILES['pdf'] ?? null; $n = 0;
            if (!$F || !is_array($F['name']) || !array_filter($F['name'])) throw new RuntimeException('اختر ملف PDF واحدًا على الأقل.');
            $count = count(array_filter($F['name']));
            foreach ($F['name'] as $i => $orig) {
                if ($orig === '') continue;
                $_FILES['one'] = ['name' => $orig, 'type' => $F['type'][$i], 'tmp_name' => $F['tmp_name'][$i], 'error' => $F['error'][$i], 'size' => $F['size'][$i]];
                $file = uwciv_admin_upload_pdf('one', $DIR, 60);
                $title = ($count === 1 && $name !== '') ? $name : trim(preg_replace('/[_\-]+/u', ' ', preg_replace('/\.pdf$/i', '', $orig)));
                $db->prepare('INSERT INTO stdlb (book_dep, book_name, pdf_file) VALUES (?, ?, ?)')->execute([$dep, $title, $file]); $n++;
            }
            $msg = $n === 1 ? 'تمت إضافة الكتاب إلى المكتبة.' : "تمت إضافة $n كتب إلى المكتبة.";
        } elseif ($act === 'edit') {
            $db->prepare('UPDATE stdlb SET book_dep = ?, book_name = ? WHERE id = ?')->execute([trim(mb_substr((string) $_POST['dep'], 0, 150)), trim(mb_substr((string) $_POST['name'], 0, 250)), (int) $_POST['id']]);
            $msg = 'تم حفظ التعديل.';
        } elseif ($act === 'del') {
            $st = $db->prepare('SELECT pdf_file FROM stdlb WHERE id = ?'); $st->execute([(int) $_POST['id']]); $f = basename((string) $st->fetchColumn());
            $db->prepare('DELETE FROM stdlb WHERE id = ?')->execute([(int) $_POST['id']]);
            if ($f !== '' && is_file("$DIR/$f")) @unlink("$DIR/$f");
            $msg = 'تم حذف الكتاب.';
        }
        if ($msg) { header('Location: index.php?ok=' . rawurlencode($msg)); exit; }
    } catch (Throwable $e) { $err = $e instanceof RuntimeException ? $e->getMessage() : 'تعذّرت العملية.'; }
}
if (isset($_GET['ok'])) $msg = (string) $_GET['ok'];
$books = []; if ($db) { try { $books = $db->query('SELECT * FROM stdlb ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC); } catch (Throwable $e) {} }
$deps = []; $size = 0; $missing = 0;
foreach ($books as $b) { $k = trim((string) $b['book_dep']) ?: 'عام'; $deps[$k] = ($deps[$k] ?? 0) + 1; $p = "$DIR/" . basename((string) $b['pdf_file']); if (is_file($p)) $size += filesize($p); else $missing++; }
arsort($deps);
require_once dirname(__DIR__, 2) . '/inc/colleges.php';
$sugg = array_keys($deps);
foreach (($UWCIV_COLLEGES ?? []) as $c) $sugg[] = $c['n'] ?? '';
$sugg = array_values(array_unique(array_filter($sugg)));
$pal = ['#1f7a45', '#2c5fd0', '#9b1f45', '#c9962a', '#0e9aa7', '#5b4bb7', '#b3541e', '#2f6f6a'];
$col = function ($d) use ($pal) { return $pal[abs(crc32((string) $d)) % count($pal)]; };
$h = 'uwciv_h';
uwciv_admin_open('library', 'المكتبة الرقمية', 'ارفع الكتب والمراجع بصيغة PDF — يمكنك رفع عدة ملفات دفعة واحدة — فتظهر فورًا في <a href="/librarysystem/index.php" target="_blank">المكتبة الرقمية</a>.', '<a class="ad-b g" href="/librarysystem/index.php" target="_blank"><i class="fas fa-eye"></i> واجهة المكتبة</a><button class="ad-b p" type="button" data-open="lbd"><i class="fas fa-cloud-upload-alt"></i> رفع كتب</button>');
uwciv_admin_toast($err ?: $msg, (bool) $err);
?>
<style>
.lb-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:18px}
.lb-b{background:#fff;border-radius:20px;box-shadow:var(--sh-1);overflow:hidden;display:flex;flex-direction:column;transition:.25s}
.lb-b:hover{transform:translateY(-4px);box-shadow:var(--sh-lift)}
.lb-cv{aspect-ratio:3/3.6;margin:12px 12px 0;border-radius:6px 14px 14px 6px;padding:16px 14px 14px 20px;color:#fff;display:flex;flex-direction:column;background:linear-gradient(160deg,var(--c),color-mix(in srgb,var(--c) 45%,#000));box-shadow:inset 7px 0 0 rgba(0,0,0,.22);position:relative;overflow:hidden}
.lb-cv:after{content:"";position:absolute;inset:0;background:radial-gradient(160px 120px at 100% 0,rgba(255,255,255,.18),transparent 70%)}
.lb-cv small{font-size:10.5px;opacity:.8;font-weight:800;position:relative}
.lb-cv b{font-size:15px;line-height:1.6;margin-top:auto;position:relative;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden}
.lb-cv i{position:absolute;left:10px;top:10px;opacity:.25;font-size:30px}
.lb-in{padding:12px 14px 4px}
.lb-in b{display:block;color:var(--ink);font-size:14px;line-height:1.5;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lb-in small{color:var(--muted);font-size:12px}
.lb-ft{display:flex;gap:6px;padding:8px 12px 14px;margin-top:auto}.lb-ft form{margin:0;margin-inline-start:auto}
.lb-miss{background:#fdecef;color:#9b1f45;border-radius:8px;padding:2px 8px;font-size:11px;font-weight:800}
.lb-files{list-style:none;margin:10px 0 0;padding:0;display:grid;gap:6px;max-height:160px;overflow:auto}
.lb-files li{display:flex;gap:8px;align-items:center;background:var(--mist);border-radius:10px;padding:6px 10px;font-size:13px}
.lb-files li i{color:#c0392b}
</style>
<div class="cp-kpis">
  <div class="cp-kpi" style="--c:#1f7a45"><i class="fas fa-book"></i><span><b><?= count($books) ?></b><span>كتاب ومرجع</span></span></div>
  <div class="cp-kpi" style="--c:#2c5fd0"><i class="fas fa-layer-group"></i><span><b><?= count($deps) ?></b><span>قسم</span></span></div>
  <div class="cp-kpi" style="--c:#c9962a"><i class="fas fa-hdd"></i><span><b><?= number_format($size / 1048576, 1) ?></b><span>ميغابايت</span></span></div>
  <div class="cp-kpi" style="--c:#9b1f45"><i class="fas fa-<?= $missing ? 'exclamation-triangle' : 'check-double' ?>"></i><span><b><?= $missing ?></b><span>ملف مفقود</span><small><?= $missing ? 'احذفها أو أعد رفعها' : 'كل الملفات سليمة' ?></small></span></div>
</div>
<div class="ad-bar">
  <div class="ad-in"><i class="fas fa-search"></i><input placeholder="ابحث باسم الكتاب أو القسم…" data-filter="#lbl"></div>
  <div class="ad-chips"><button class="ad-chip on" type="button" data-chip="all">الكل <em><?= count($books) ?></em></button><?php $i = 0; foreach ($deps as $d => $n): if ($i++ > 7) break; ?><button class="ad-chip" type="button" data-chip="d<?= md5($d) ?>"><?= $h($d) ?> <em><?= $n ?></em></button><?php endforeach; ?></div>
</div>
<div class="lb-g" id="lbl">
<?php foreach ($books as $b): $d = trim((string) $b['book_dep']) ?: 'عام'; $f = basename((string) $b['pdf_file']); $ok = $f !== '' && is_file("$DIR/$f"); ?>
  <article class="lb-b fi" data-s="<?= $h($b['book_name'] . ' ' . $d) ?>" data-g="d<?= md5($d) ?>">
    <div class="lb-cv" style="--c:<?= $col($d) ?>"><i class="fas fa-book-open"></i><small><?= $h($d) ?></small><b><?= $h($b['book_name']) ?></b></div>
    <div class="lb-in"><b title="<?= $h($b['book_name']) ?>"><?= $h($b['book_name']) ?></b><small><?= $ok ? '<span dir="ltr">' . number_format(filesize("$DIR/$f") / 1048576, 2) . ' MB</span>' : '<span class="lb-miss">الملف مفقود</span>' ?></small></div>
    <div class="lb-ft">
      <?php if ($ok): ?><a class="cp-b g sm" href="files/<?= rawurlencode($f) ?>" target="_blank" title="فتح"><i class="far fa-file-pdf"></i></a><?php endif; ?>
      <button class="cp-b g sm" type="button" data-lb-edit='<?= $h(json_encode(['id' => (int) $b['id'], 'name' => $b['book_name'], 'dep' => $d], JSON_UNESCAPED_UNICODE)) ?>' title="تعديل"><i class="fas fa-pen"></i></button>
      <form method="post" data-confirm="حذف «<?= $h(mb_strimwidth($b['book_name'], 0, 50, '…')) ?>» وملفه نهائيًا؟"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="del"><input type="hidden" name="id" value="<?= (int) $b['id'] ?>"><button class="cp-b r sm" title="حذف"><i class="fas fa-trash"></i></button></form>
    </div>
  </article>
<?php endforeach; ?>
</div>
<div class="cp-card fi-empty" <?= $books ? 'hidden' : '' ?>><div class="cp-empty"><i class="fas fa-book-open"></i><?= $books ? 'لا نتائج مطابقة.' : 'المكتبة فارغة — ابدأ برفع أول كتاب.' ?></div></div>

<dialog class="ad-dlg" id="lbd"><form method="post" enctype="multipart/form-data"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="add">
  <h3><i class="fas fa-cloud-upload-alt" style="color:var(--teal)"></i> رفع كتب إلى المكتبة<button type="button" class="x" data-dlg-close><i class="fas fa-times"></i></button></h3>
  <div class="cp-f">
    <label>القسم *<div class="cp-in"><i class="far fa-folder"></i><input name="dep" list="lbdeps" required placeholder="مثال: كلية الشريعة"></div></label>
    <label>اسم الكتاب<div class="cp-in"><i class="fas fa-book"></i><input name="name" id="lbn" placeholder="يُؤخذ من اسم الملف إن تركته فارغًا"></div></label>
    <div class="w"><label class="ad-drop"><input type="file" name="pdf[]" id="lbf" accept="application/pdf,.pdf" multiple required><i class="far fa-file-pdf"></i><b>اسحب ملفات PDF هنا أو اضغط للاختيار</b><small>يمكنك اختيار عدة ملفات دفعة واحدة — حتى 60 ميغابايت للملف</small></label><ul class="lb-files" id="lbfl"></ul></div>
  </div>
  <div class="cp-btns"><button class="cp-b p"><i class="fas fa-upload"></i> رفع وإضافة</button><button type="button" class="cp-b g" data-dlg-close>إلغاء</button></div>
</form></dialog>
<dialog class="ad-dlg" id="lbe" style="width:min(520px,94vw)"><form method="post"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="edit"><input type="hidden" name="id" id="lbei">
  <h3><i class="fas fa-pen" style="color:var(--teal)"></i> تعديل بيانات الكتاب<button type="button" class="x" data-dlg-close><i class="fas fa-times"></i></button></h3>
  <div class="cp-f"><label class="w">اسم الكتاب<div class="cp-in"><i class="fas fa-book"></i><input name="name" id="lben" required></div></label><label class="w">القسم<div class="cp-in"><i class="far fa-folder"></i><input name="dep" id="lbed" list="lbdeps" required></div></label></div>
  <div class="cp-btns"><button class="cp-b p"><i class="fas fa-save"></i> حفظ</button><button type="button" class="cp-b g" data-dlg-close>إلغاء</button></div>
</form></dialog>
<datalist id="lbdeps"><?php foreach ($sugg as $s): ?><option value="<?= $h($s) ?>"><?php endforeach; ?></datalist>
<script>
(function(){var f=document.getElementById('lbf'),l=document.getElementById('lbfl'),n=document.getElementById('lbn');
f.addEventListener('change',function(){l.innerHTML='';[].forEach.call(f.files,function(x){var li=document.createElement('li');li.innerHTML='<i class="far fa-file-pdf"></i><span></span><small style="margin-inline-start:auto;color:#7a8c80"></small>';li.querySelector('span').textContent=x.name;li.querySelector('small').textContent=(x.size/1048576).toFixed(1)+' MB';l.appendChild(li);});n.disabled=f.files.length>1;n.placeholder=f.files.length>1?'تُؤخذ الأسماء من أسماء الملفات':'يُؤخذ من اسم الملف إن تركته فارغًا';});
document.querySelectorAll('[data-lb-edit]').forEach(function(b){b.onclick=function(){var x=JSON.parse(b.getAttribute('data-lb-edit'));document.getElementById('lbei').value=x.id;document.getElementById('lben').value=x.name;document.getElementById('lbed').value=x.dep;document.getElementById('lbe').showModal();}});
document.querySelector('#lbd form').addEventListener('submit',function(e){var b=e.target.querySelector('.cp-b.p');b.innerHTML='<i class="fas fa-spinner fa-spin"></i> جارٍ الرفع…';b.style.pointerEvents='none';});})();
</script>
<?php uwciv_admin_close(); ?>
