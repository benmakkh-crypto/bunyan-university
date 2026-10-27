<?php
/* حسابات بوابة الطلاب — إدارة المشرف العام */
require_once dirname(__DIR__, 2) . '/inc/admin_ui.php';
uwciv_admin_gate(['title' => 'حسابات البوابة']);
$msg = ''; $err = ''; $newPass = '';
try { $db = db_pdo('logweb'); $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); } catch (Throwable $e) { $db = null; $err = 'تعذّر الاتصال بقاعدة البيانات حاليًا.'; }

/* يحفظ كلمة المرور مشفّرة bcrypt، ويرجع إلى md5 إن كان العمود قصيرًا */
function acc_set_pass(PDO $db, $id, $plain)
{
    $db->prepare('UPDATE user SET pass = ? WHERE id = ?')->execute([password_hash($plain, PASSWORD_BCRYPT), $id]);
    $st = $db->prepare('SELECT pass FROM user WHERE id = ?'); $st->execute([$id]);
    if (!password_verify($plain, (string) $st->fetchColumn())) $db->prepare('UPDATE user SET pass = ? WHERE id = ?')->execute([md5($plain), $id]);
}
function acc_gen() { $c = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'; $p = ''; for ($i = 0; $i < 10; $i++) $p .= $c[random_int(0, strlen($c) - 1)]; return 'Uw-' . $p; }

if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!uwciv_csrf_ok()) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    else try {
        $act = $_POST['act'] ?? ''; $id = (int) ($_POST['id'] ?? 0);
        if ($act === 'save') {
            $name = trim(mb_substr((string) ($_POST['name'] ?? ''), 0, 100));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $pw = (string) ($_POST['pw'] ?? '');
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('اكتب الاسم وبريدًا إلكترونيًا صحيحًا.');
            $st = $db->prepare('SELECT id FROM user WHERE LOWER(email) = ? AND id <> ?'); $st->execute([$email, $id]);
            if ($st->fetchColumn()) throw new RuntimeException('هذا البريد مسجّل لحساب آخر.');
            if ($pw !== '' && mb_strlen($pw) < 8) throw new RuntimeException('كلمة المرور 8 أحرف على الأقل.');
            if ($id) {
                $db->prepare('UPDATE user SET name = ?, email = ? WHERE id = ?')->execute([$name, $email, $id]);
                if ($pw !== '') acc_set_pass($db, $id, $pw);
                $msg = 'تم حفظ بيانات الحساب.';
            } else {
                if ($pw === '') $pw = $newPass = acc_gen();
                $db->prepare("INSERT INTO user (name, email, pass, code) VALUES (?, ?, ?, '')")->execute([$name, $email, md5($pw)]);
                acc_set_pass($db, (int) $db->lastInsertId(), $pw);
                $msg = 'تم إنشاء حساب ' . $email . '.';
            }
        } elseif ($act === 'reset') {
            $newPass = acc_gen(); acc_set_pass($db, $id, $newPass);
            $st = $db->prepare('SELECT email FROM user WHERE id = ?'); $st->execute([$id]);
            $msg = 'كلمة مرور جديدة للحساب ' . $st->fetchColumn();
        } elseif ($act === 'activate') {
            $db->prepare("UPDATE user SET code = '' WHERE id = ?")->execute([$id]); $msg = 'تم تفعيل الحساب.';
        } elseif ($act === 'del') {
            $db->prepare('DELETE FROM user WHERE id = ?')->execute([$id]); $msg = 'تم حذف الحساب.';
        }
        if ($msg && !$newPass) { header('Location: index.php?ok=' . rawurlencode($msg)); exit; }
    } catch (Throwable $e) { $err = $e instanceof RuntimeException ? $e->getMessage() : 'تعذّرت العملية.'; }
}
if (isset($_GET['ok'])) $msg = (string) $_GET['ok'];
$rows = [];
if ($db) { try { $rows = $db->query('SELECT id, name, email, pass, code FROM user ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC); } catch (Throwable $e) { $err = 'تعذّر قراءة الحسابات.'; } }
$pending = count(array_filter($rows, function ($r) { return (string) $r['code'] !== ''; }));
$legacy = count(array_filter($rows, function ($r) { return strlen((string) $r['pass']) <= 40; }));
$h = 'uwciv_h'; $colors = ['#1f7a45', '#2c5fd0', '#9b1f45', '#c9962a', '#0e9aa7', '#5b4bb7'];
uwciv_admin_open('accounts', 'حسابات بوابة الطلاب', 'الحسابات التي يدخل بها الطلاب إلى البوابة الإلكترونية. كلمات المرور محفوظة مشفّرة ولا تُعرض لأحد — يمكنك إعادة تعيينها فقط.', '<button class="ad-b p" type="button" data-acc-new><i class="fas fa-user-plus"></i> حساب جديد</button>');
if (!$newPass) uwciv_admin_toast($err ?: $msg, (bool) $err);
?>
<style>
.acc-np{display:flex;gap:14px;align-items:center;background:linear-gradient(135deg,#0b3119,#11432a);color:#fff;border-radius:20px;padding:18px 20px;margin-bottom:16px;flex-wrap:wrap}
.acc-np i.k{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;background:rgba(127,224,166,.18);color:#7fe0a6;font-size:20px}
.acc-np code{font-size:20px;font-weight:900;background:rgba(255,255,255,.1);padding:6px 14px;border-radius:10px;letter-spacing:1px;direction:ltr;display:inline-block;color:#fff}
.acc-np .sp{flex:1;min-width:200px}
.acc-act{display:flex;gap:6px;justify-content:flex-end;flex-wrap:wrap}.acc-act form{margin:0}
@media(max-width:760px){.ad-table thead{display:none}.ad-table tr{display:grid;grid-template-columns:1fr;padding:10px 0;border-bottom:1px solid #eef3ef}.ad-table td{border:0;padding:6px 14px}.acc-act{justify-content:flex-start}}
</style>
<?php if ($newPass): ?><div class="acc-np"><i class="fas fa-key k"></i><div class="sp"><b>انسخ كلمة المرور الآن وأرسلها لصاحب الحساب — لن تظهر مرة أخرى</b><br><small style="color:#bfe3cc"><?= $h($msg) ?></small></div><code id="np"><?= $h($newPass) ?></code><button class="ad-b g" type="button" onclick="navigator.clipboard.writeText(document.getElementById('np').textContent).then(function(){uwcToast('تم النسخ')})"><i class="far fa-copy"></i> نسخ</button></div><?php endif; ?>
<div class="cp-kpis">
  <div class="cp-kpi" style="--c:#1f7a45"><i class="fas fa-users"></i><span><b><?= count($rows) ?></b><span>حساب مسجّل</span></span></div>
  <div class="cp-kpi" style="--c:#2c5fd0"><i class="fas fa-user-check"></i><span><b><?= count($rows) - $pending ?></b><span>حساب مفعّل</span></span></div>
  <div class="cp-kpi" style="--c:#c9962a"><i class="fas fa-hourglass-half"></i><span><b><?= $pending ?></b><span>بانتظار التفعيل</span></span></div>
  <div class="cp-kpi" style="--c:#9b1f45"><i class="fas fa-shield-alt"></i><span><b><?= count($rows) - $legacy ?></b><span>بتشفير قوي</span><small><?= $legacy ?> بتشفير قديم</small></span></div>
</div>
<div class="ad-bar">
  <div class="ad-in"><i class="fas fa-search"></i><input placeholder="ابحث بالاسم أو البريد…" data-filter="#accl"></div>
  <div class="ad-chips"><button class="ad-chip on" type="button" data-chip="all">الكل</button><button class="ad-chip" type="button" data-chip="ok">مفعّل</button><button class="ad-chip" type="button" data-chip="wait">بانتظار التفعيل</button></div>
</div>
<div class="ad-tw"><table class="ad-table"><thead><tr><th>الحساب</th><th>الحالة</th><th>الأمان</th><th></th></tr></thead><tbody id="accl">
<?php foreach ($rows as $i => $r): $wait = (string) $r['code'] !== ''; $strong = strlen((string) $r['pass']) > 40; $c = $colors[$r['id'] % 6]; ?>
<tr class="fi" data-s="<?= $h($r['name'] . ' ' . $r['email']) ?>" data-g="<?= $wait ? 'wait' : 'ok' ?>">
  <td><div class="ad-who"><span class="ad-av" style="--c:<?= $c ?>"><?= $h(mb_substr((string) $r['name'], 0, 1) ?: '?') ?></span><span><b><?= $h($r['name']) ?></b><small dir="ltr"><?= $h($r['email']) ?></small></span></div></td>
  <td><?= $wait ? '<span class="cp-pill n"><i class="fas fa-hourglass-half"></i> بانتظار التفعيل</span>' : '<span class="cp-pill y"><i class="fas fa-check"></i> مفعّل</span>' ?></td>
  <td><?= $strong ? '<span class="cp-pill b"><i class="fas fa-lock"></i> قوي</span>' : '<span class="cp-pill k"><i class="fas fa-unlock"></i> قديم</span>' ?></td>
  <td><div class="acc-act">
    <button class="cp-b g sm" type="button" data-acc-edit='<?= $h(json_encode(['id' => (int) $r['id'], 'name' => $r['name'], 'email' => $r['email']], JSON_UNESCAPED_UNICODE)) ?>'><i class="fas fa-pen"></i> تعديل</button>
    <?php if ($wait): ?><form method="post"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="activate"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="cp-b g sm"><i class="fas fa-user-check"></i> تفعيل</button></form><?php endif; ?>
    <form method="post" data-confirm="إنشاء كلمة مرور جديدة لهذا الحساب؟ ستتوقف الكلمة القديمة."><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="reset"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="cp-b g sm"><i class="fas fa-key"></i> كلمة جديدة</button></form>
    <form method="post" data-confirm="حذف حساب <?= $h($r['email']) ?> نهائيًا؟"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="del"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="cp-b r sm" title="حذف"><i class="fas fa-trash"></i></button></form>
  </div></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<div class="cp-card fi-empty" <?= $rows ? 'hidden' : '' ?>><div class="cp-empty"><i class="fas fa-user-lock"></i><?= $rows ? 'لا نتائج مطابقة.' : 'لا توجد حسابات بعد.' ?></div></div>

<dialog class="ad-dlg" id="accd" style="width:min(520px,94vw)"><form method="post"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="id" id="aid">
  <h3><i class="fas fa-user-plus" style="color:var(--teal)"></i> <span id="at">حساب جديد</span><button type="button" class="x" data-dlg-close><i class="fas fa-times"></i></button></h3>
  <div class="cp-f">
    <label class="w">الاسم الكامل *<div class="cp-in"><i class="far fa-user"></i><input name="name" id="an" required maxlength="100"></div></label>
    <label class="w">البريد الإلكتروني *<div class="cp-in"><i class="far fa-envelope"></i><input type="email" name="email" id="ae" required dir="ltr"></div></label>
    <label class="w"><span id="apl">كلمة المرور (اتركها فارغة لتوليد كلمة قوية)</span><div class="cp-in"><i class="fas fa-key"></i><input type="password" name="pw" id="ap" minlength="8" autocomplete="new-password" dir="ltr"><button type="button" class="eye"><i class="far fa-eye"></i></button></div></label>
  </div>
  <div class="cp-btns"><button class="cp-b p"><i class="fas fa-save"></i> حفظ</button><button type="button" class="cp-b g" data-dlg-close>إلغاء</button></div>
</form></dialog>
<script>
(function(){var d=document.getElementById('accd'),$=function(i){return document.getElementById(i)};
function open(x){x=x||{};d.querySelector('form').reset();$('aid').value=x.id||'';$('an').value=x.name||'';$('ae').value=x.email||'';$('at').textContent=x.id?'تعديل الحساب':'حساب جديد';$('apl').textContent=x.id?'كلمة مرور جديدة (اتركها فارغة للإبقاء على الحالية)':'كلمة المرور (اتركها فارغة لتوليد كلمة قوية)';d.showModal();$('an').focus();}
document.querySelector('[data-acc-new]').onclick=function(){open()};
document.querySelectorAll('[data-acc-edit]').forEach(function(b){b.onclick=function(){open(JSON.parse(b.getAttribute('data-acc-edit')))}});})();
</script>
<?php uwciv_admin_close(); ?>
