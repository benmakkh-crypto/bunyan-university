<?php
/*
 * مدير الأخبار الموحّد — يُستدعى من blog/admin و blgrad/admin
 * $NEWS = ['key','table','cls','dir','url','title','sub','view','noun']
 */
require_once __DIR__ . '/admin_ui.php';
uwciv_admin_gate(['title' => $NEWS['title']]);
$T = $NEWS['table']; $CL = $NEWS['cls'];
$msg = ''; $err = '';
try { $db = db_pdo('logweb'); $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); } catch (Throwable $e) { $db = null; $err = 'تعذّر الاتصال بقاعدة البيانات حاليًا.'; }

if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!uwciv_csrf_ok()) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    else try {
        $act = $_POST['act'] ?? '';
        if ($act === 'del') {
            $id = (int) ($_POST['id'] ?? 0);
            $st = $db->prepare("SELECT image FROM `$T` WHERE id = ?"); $st->execute([$id]); $img = (string) $st->fetchColumn();
            $db->prepare("DELETE FROM `$T` WHERE id = ?")->execute([$id]);
            if ($img !== '' && is_file($NEWS['dir'] . '/' . basename($img))) @unlink($NEWS['dir'] . '/' . basename($img));
            $msg = 'تم حذف ' . $NEWS['noun'] . '.';
        } elseif ($act === 'save') {
            $id = (int) ($_POST['id'] ?? 0);
            $title = trim(mb_substr((string) ($_POST['title'] ?? ''), 0, 250));
            $summary = trim(mb_substr((string) ($_POST['summary'] ?? ''), 0, 1000));
            $content = trim((string) ($_POST['content'] ?? ''));
            $cls = trim(mb_substr((string) ($_POST['cls'] ?? ''), 0, 120)) ?: 'عام';
            $d = (string) ($_POST['date'] ?? '');
            $date = preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $d, $m) ? "$m[1]/$m[2]/$m[3]" : date('Y/m/d');
            if ($title === '') throw new RuntimeException('اكتب عنوان ' . $NEWS['noun'] . '.');
            if ($summary === '') $summary = mb_strimwidth(preg_replace('/\s+/u', ' ', $content), 0, 220, '…');
            $img = uwciv_admin_upload_image('image', $NEWS['dir']);
            if ($id) {
                $old = $db->prepare("SELECT image FROM `$T` WHERE id = ?"); $old->execute([$id]); $oldImg = (string) $old->fetchColumn();
                $db->prepare("UPDATE `$T` SET `date` = ?, title = ?, summary = ?, content = ?, `$CL` = ?, image = ? WHERE id = ?")->execute([$date, $title, $summary, $content, $cls, $img ?: $oldImg, $id]);
                if ($img && $oldImg && $oldImg !== $img && is_file($NEWS['dir'] . '/' . basename($oldImg))) @unlink($NEWS['dir'] . '/' . basename($oldImg));
                $msg = 'تم حفظ التعديلات.';
            } else {
                $db->prepare("INSERT INTO `$T` (`date`, title, summary, content, `$CL`, image) VALUES (?, ?, ?, ?, ?, ?)")->execute([$date, $title, $summary, $content, $cls, $img]);
                $msg = 'تم نشر ' . $NEWS['noun'] . ' بنجاح.';
            }
        }
        if ($msg) { header('Location: index.php?ok=' . rawurlencode($msg)); exit; }
    } catch (Throwable $e) { $err = $e instanceof RuntimeException ? $e->getMessage() : 'تعذّر الحفظ، حاول مرة أخرى.'; }
}
if (isset($_GET['ok'])) $msg = (string) $_GET['ok'];

$rows = [];
if ($db) { try { $rows = $db->query("SELECT * FROM `$T` ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC); } catch (Throwable $e) { $err = 'تعذّر قراءة الأخبار.'; } }
$cats = []; $month = 0; $ym = date('Y/m');
foreach ($rows as $r) { $c = trim((string) $r[$CL]) ?: 'عام'; $cats[$c] = ($cats[$c] ?? 0) + 1; if (strpos(str_replace('-', '/', (string) $r['date']), $ym) === 0) $month++; }
arsort($cats);
$h = 'uwciv_h';
$acts = '<a class="ad-b g" href="' . $NEWS['front'] . '" target="_blank"><i class="fas fa-eye"></i> عرض في الموقع</a><button class="ad-b p" type="button" data-new><i class="fas fa-plus"></i> ' . $NEWS['add'] . '</button>';
uwciv_admin_open($NEWS['key'], $NEWS['title'], $NEWS['sub'], $acts);
uwciv_admin_toast($err ?: $msg, (bool) $err);
?>
<style>
.nw-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:18px}
.nw{background:#fff;border-radius:22px;overflow:hidden;box-shadow:var(--sh-1);display:flex;flex-direction:column;transition:.25s}
.nw:hover{transform:translateY(-4px);box-shadow:var(--sh-lift)}
.nw .im{aspect-ratio:16/9;background:linear-gradient(135deg,#dfeee4,#c7dfcf) center/cover;position:relative;display:grid;place-items:center;color:#9cc3a9;font-size:34px}
.nw .im span{position:absolute;top:12px;right:12px;background:rgba(6,33,15,.82);color:#fff;border-radius:20px;padding:4px 12px;font-size:12px;font-weight:800;backdrop-filter:blur(4px)}
.nw .bd{padding:16px 18px;display:flex;flex-direction:column;gap:6px;flex:1}
.nw small{color:var(--teal);font-weight:800;font-size:12.5px}
.nw h3{margin:0;font-size:16.5px;font-weight:900;color:var(--ink);line-height:1.55}
.nw p{margin:0;color:var(--muted);font-size:13.5px;line-height:1.8;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.nw .ft{display:flex;gap:6px;padding:0 18px 16px;margin-top:auto}
.nw .ft form{margin:0;margin-inline-start:auto}
.nw-cnt{font-size:12px;color:var(--muted);font-weight:700;text-align:left}
</style>
<div class="cp-kpis">
  <div class="cp-kpi" style="--c:#1f7a45"><i class="far fa-newspaper"></i><span><b><?= count($rows) ?></b><span>إجمالي <?= $NEWS['plural'] ?></span></span></div>
  <div class="cp-kpi" style="--c:#2c5fd0"><i class="far fa-calendar-check"></i><span><b><?= $month ?></b><span>هذا الشهر</span></span></div>
  <div class="cp-kpi" style="--c:#c9962a"><i class="fas fa-tags"></i><span><b><?= count($cats) ?></b><span>تصنيف</span></span></div>
  <div class="cp-kpi" style="--c:#9b1f45"><i class="far fa-clock"></i><span><b style="font-size:17px"><?= $rows ? $h($rows[0]['date']) : '—' ?></b><span>آخر نشر</span></span></div>
</div>
<div class="ad-bar">
  <div class="ad-in"><i class="fas fa-search"></i><input placeholder="ابحث في العناوين والملخصات…" data-filter="#nwl" data-count="#nwn"></div>
  <div class="ad-chips"><button class="ad-chip on" data-chip="all" type="button">الكل <em><?= count($rows) ?></em></button><?php $ci = 0; foreach ($cats as $c => $n): if ($ci++ > 6) break; ?><button class="ad-chip" type="button" data-chip="c<?= md5($c) ?>"><?= $h($c) ?> <em><?= $n ?></em></button><?php endforeach; ?></div>
</div>
<div class="nw-grid" id="nwl">
<?php foreach ($rows as $r): $c = trim((string) $r[$CL]) ?: 'عام'; $img = $r['image'] && is_file($NEWS['dir'] . '/' . basename($r['image'])) ? $NEWS['url'] . '/' . rawurlencode(basename($r['image'])) : '';
  $data = ['id' => (int) $r['id'], 'title' => $r['title'], 'summary' => $r['summary'], 'content' => $r['content'], 'cls' => $c, 'date' => str_replace('/', '-', (string) $r['date']), 'img' => $img]; ?>
  <article class="nw fi" data-s="<?= $h($r['title'] . ' ' . $r['summary'] . ' ' . $c) ?>" data-g="c<?= md5($c) ?>">
    <div class="im"<?= $img ? ' style="background-image:url(\'' . $h($img) . '\')"' : '' ?>><?= $img ? '' : '<i class="far fa-image"></i>' ?><span><?= $h($c) ?></span></div>
    <div class="bd"><small><i class="far fa-calendar"></i> <?= $h($r['date']) ?></small><h3><?= $h($r['title']) ?></h3><p><?= $h($r['summary']) ?></p></div>
    <div class="ft">
      <button class="cp-b p sm" type="button" data-edit='<?= $h(json_encode($data, JSON_UNESCAPED_UNICODE)) ?>'><i class="fas fa-pen"></i> تعديل</button>
      <a class="cp-b g sm" href="<?= $NEWS['view'] . (int) $r['id'] ?>" target="_blank" title="عرض"><i class="fas fa-eye"></i></a>
      <form method="post" data-confirm="حذف «<?= $h(mb_strimwidth($r['title'], 0, 60, '…')) ?>» نهائيًا؟"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="del"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="cp-b r sm" title="حذف"><i class="fas fa-trash"></i></button></form>
    </div>
  </article>
<?php endforeach; ?>
</div>
<div class="cp-card fi-empty" <?= $rows ? 'hidden' : '' ?>><div class="cp-empty"><i class="far fa-newspaper"></i><?= $rows ? 'لا نتائج مطابقة.' : 'لا يوجد شيء منشور بعد — ابدأ بـ«' . $NEWS['add'] . '».' ?></div></div>

<dialog class="ad-dlg" id="nwd">
  <form method="post" enctype="multipart/form-data"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="id" id="nwid">
    <h3><i class="far fa-newspaper" style="color:var(--teal)"></i> <span id="nwt"><?= $NEWS['add'] ?></span><button type="button" class="x" data-dlg-close aria-label="إغلاق"><i class="fas fa-times"></i></button></h3>
    <div class="cp-f">
      <label class="w">العنوان *<div class="cp-in"><i class="fas fa-heading"></i><input name="title" id="nwti" required maxlength="250" placeholder="عنوان واضح ومختصر"></div></label>
      <label>التصنيف<div class="cp-in"><i class="fas fa-tag"></i><input name="cls" id="nwc" list="nwcats" placeholder="عام"></div></label>
      <label>تاريخ النشر<div class="cp-in"><i class="far fa-calendar"></i><input type="date" name="date" id="nwda" value="<?= date('Y-m-d') ?>"></div></label>
      <label class="w">الملخص <span class="nw-cnt"><span id="nwsc">0</span>/1000</span><div class="cp-in ta"><i class="fas fa-align-right"></i><textarea name="summary" id="nws" maxlength="1000" style="min-height:80px" placeholder="سطران يظهران في بطاقة الخبر (يُولَّد تلقائيًا إن تركته فارغًا)"></textarea></div></label>
      <label class="w">نص <?= $NEWS['noun'] ?><div class="cp-in ta"><i class="fas fa-paragraph"></i><textarea name="content" id="nwct" style="min-height:170px" placeholder="التفاصيل الكاملة…"></textarea></div></label>
      <div class="w"><label class="ad-drop"><input type="file" name="image" accept="image/jpeg,image/png,image/webp"><i class="fas fa-cloud-upload-alt"></i><b>اسحب الصورة هنا أو اضغط للاختيار</b><small>JPG أو PNG أو WEBP — حتى 6 ميغابايت</small><img id="nwim" hidden alt=""></label></div>
    </div>
    <div class="cp-btns"><button class="cp-b p"><i class="fas fa-paper-plane"></i> <span id="nwb">نشر</span></button><button type="button" class="cp-b g" data-dlg-close>إلغاء</button></div>
  </form>
</dialog>
<datalist id="nwcats"><?php foreach (array_keys($cats) as $c): ?><option value="<?= $h($c) ?>"><?php endforeach; ?></datalist>
<script>
(function(){
  var d=document.getElementById('nwd'),f=d.querySelector('form'),$=function(i){return document.getElementById(i)},s=$('nws');
  function cnt(){$('nwsc').textContent=s.value.length}s.addEventListener('input',cnt);
  function open(x){f.reset();x=x||{};$('nwid').value=x.id||'';$('nwti').value=x.title||'';$('nwc').value=x.cls||'';$('nwda').value=x.date||'<?= date('Y-m-d') ?>';s.value=x.summary||'';$('nwct').value=x.content||'';
    var im=$('nwim');if(x.img){im.src=x.img;im.hidden=false}else{im.hidden=true;im.removeAttribute('src')}f.querySelector('.ad-drop b').textContent=x.img?'اضغط لاستبدال الصورة':'اسحب الصورة هنا أو اضغط للاختيار';
    $('nwt').textContent=x.id?'تعديل <?= $NEWS['noun'] ?>':'<?= $NEWS['add'] ?>';$('nwb').textContent=x.id?'حفظ التعديلات':'نشر';cnt();d.showModal();$('nwti').focus();}
  document.querySelectorAll('[data-new]').forEach(function(b){b.addEventListener('click',function(){open()})});
  document.querySelectorAll('[data-edit]').forEach(function(b){b.addEventListener('click',function(){open(JSON.parse(b.getAttribute('data-edit')))})});
  var m=location.search.match(/[?&]edit=(\d+)/);if(m){var b=[].slice.call(document.querySelectorAll('[data-edit]')).filter(function(x){return JSON.parse(x.getAttribute('data-edit')).id==m[1]})[0];if(b)b.click();}
  if(location.hash==='#new')open();
  <?php if ($err && ($_POST['act'] ?? '') === 'save'): ?>open(<?= json_encode(['id' => (int) ($_POST['id'] ?? 0), 'title' => $_POST['title'] ?? '', 'summary' => $_POST['summary'] ?? '', 'content' => $_POST['content'] ?? '', 'cls' => $_POST['cls'] ?? '', 'date' => $_POST['date'] ?? ''], JSON_UNESCAPED_UNICODE) ?>);<?php endif; ?>
})();
</script>
<?php uwciv_admin_close(); ?>
