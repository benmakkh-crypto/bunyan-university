<?php
/* الموظفون والصلاحيات — للمشرف العام فقط */
require_once dirname(__DIR__) . '/inc/admin_ui.php';
uwciv_admin_gate(['title' => 'الموظفون والصلاحيات', 'perm' => 'staff']);
$ROLES = uwciv_staff_roles(); $PERMS = uwciv_perm_defs(); $G = uwciv_admin_groups();
require_once dirname(__DIR__) . '/inc/colleges.php';
$msg = ''; $err = ''; $newPass = ''; $newUser = '';
$gen = function () { $c = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'; $p = ''; for ($i = 0; $i < 10; $i++) $p .= $c[random_int(0, strlen($c) - 1)]; return 'Uw-' . $p; };

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!uwciv_csrf_ok()) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    else try {
        $all = uwciv_staff_all(); $act = $_POST['act'] ?? ''; $id = (string) ($_POST['id'] ?? '');
        $idx = null; foreach ($all as $i => $s) if ($s['id'] === $id) $idx = $i;
        if ($act === 'save') {
            $name = trim(mb_substr((string) ($_POST['name'] ?? ''), 0, 80));
            $user = mb_strtolower(trim((string) ($_POST['username'] ?? '')));
            $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
            $role = isset($ROLES[$_POST['role'] ?? '']) ? $_POST['role'] : 'custom';
            $perms = array_values(array_intersect(array_keys($PERMS), (array) ($_POST['perms'] ?? [])));
            $pw = (string) ($_POST['pw'] ?? '');
            if (mb_strlen($name) < 3) throw new RuntimeException('اكتب اسم الموظف.');
            if (!preg_match('/^[a-z0-9._-]{3,30}$/', $user)) throw new RuntimeException('اسم الدخول بالإنجليزية والأرقام فقط (3–30 حرفًا)، مثل: ahmed.ali');
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('البريد الإلكتروني غير صحيح.');
            if (in_array($user, array_map('mb_strtolower', $GLOBALS['ADMIN_PANEL_USERS'] ?? ['admin']), true)) throw new RuntimeException('اسم الدخول محجوز للمشرف العام.');
            foreach ($all as $i => $s) if ($i !== $idx && ($s['username'] === $user || ($email !== '' && ($s['email'] ?? '') === $email))) throw new RuntimeException('اسم الدخول أو البريد مستخدم لموظف آخر.');
            if (!$perms) throw new RuntimeException('حدّد صلاحية واحدة على الأقل.');
            if ($pw !== '' && mb_strlen($pw) < 8) throw new RuntimeException('كلمة المرور 8 أحرف على الأقل.');
            $rec = ['name' => $name, 'username' => $user, 'email' => $email, 'phone' => preg_replace('/[^\d+]/', '', (string) ($_POST['phone'] ?? '')), 'unit' => trim(mb_substr((string) ($_POST['unit'] ?? ''), 0, 120)), 'role' => $role, 'perms' => $perms, 'active' => !empty($_POST['active']), 'college' => isset($UWCIV_COLLEGES[$_POST['college'] ?? '']) ? $_POST['college'] : ''];
            if ($idx === null) {
                if ($pw === '') $pw = $newPass = $gen(); else $newPass = $pw;
                $rec += ['id' => bin2hex(random_bytes(6)), 'hash' => password_hash($pw, PASSWORD_BCRYPT), 'created' => date('Y-m-d H:i'), 'last' => ''];
                $all[] = $rec; $msg = 'تمت إضافة الموظف ' . $name . '.'; $newUser = $user;
                uwciv_staff_log('أضاف الموظف ' . $name . ' (' . $ROLES[$role][0] . ')', ['name' => 'المشرف العام']);
            } else {
                $all[$idx] = array_merge($all[$idx], $rec);
                if ($pw !== '') { $all[$idx]['hash'] = password_hash($pw, PASSWORD_BCRYPT); $newPass = $pw; $newUser = $user; }
                $msg = 'تم حفظ بيانات ' . $name . ' وصلاحياته.';
                uwciv_staff_log('عدّل بيانات/صلاحيات ' . $name, ['name' => 'المشرف العام']);
            }
        } elseif ($act === 'reset' && $idx !== null) {
            $newPass = $gen(); $newUser = $all[$idx]['username']; $all[$idx]['hash'] = password_hash($newPass, PASSWORD_BCRYPT);
            $msg = 'كلمة مرور جديدة للموظف ' . $all[$idx]['name']; uwciv_staff_log('أعاد تعيين كلمة مرور ' . $all[$idx]['name'], ['name' => 'المشرف العام']);
        } elseif ($act === 'toggle' && $idx !== null) {
            $all[$idx]['active'] = empty($all[$idx]['active']); $msg = ($all[$idx]['active'] ? 'تم تفعيل ' : 'تم إيقاف ') . $all[$idx]['name'];
            uwciv_staff_log($msg, ['name' => 'المشرف العام']);
        } elseif ($act === 'del' && $idx !== null) {
            $msg = 'تم حذف الموظف ' . $all[$idx]['name']; uwciv_staff_log($msg, ['name' => 'المشرف العام']); array_splice($all, $idx, 1);
        }
        if (!uwciv_staff_save($all)) throw new RuntimeException('تعذّر الحفظ على الخادم.');
        if ($msg && !$newPass) { header('Location: staff.php?ok=' . rawurlencode($msg)); exit; }
    } catch (Throwable $e) { $err = $e instanceof RuntimeException ? $e->getMessage() : 'تعذّرت العملية.'; }
}
if (isset($_GET['ok'])) $msg = (string) $_GET['ok'];
$all = uwciv_staff_all(); $tab = ($_GET['tab'] ?? '') === 'log' ? 'log' : (($_GET['tab'] ?? '') === 'roles' ? 'roles' : 'list');
$active = count(array_filter($all, function ($s) { return !empty($s['active']); }));
$h = 'uwciv_h'; $cols = ['#1f7a45', '#2c5fd0', '#9b1f45', '#c9962a', '#0e9aa7', '#5b4bb7'];
uwciv_admin_open('staff', 'الموظفون والصلاحيات', 'أضف موظفي الجامعة وأسند لكل منهم لوحاته فقط وفق دوره في <a href="/regulations.php#powers" target="_blank">سلّم الصلاحيات</a>. يدخل الموظف من نفس صفحة الدخول باسمه وكلمة مروره.', '<button class="ad-b p" type="button" data-st-new><i class="fas fa-user-plus"></i> موظف جديد</button>');
if (!$newPass) uwciv_admin_toast($err ?: $msg, (bool) $err);
?>
<style>
.sf-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.sf-tabs a{border-radius:14px;padding:10px 16px;font-weight:800;background:#fff;color:var(--ink-3)!important;box-shadow:var(--sh-1);text-decoration:none!important}
.sf-tabs a.on{background:var(--ink-3);color:#fff!important}
.sf-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px}
.sf{background:#fff;border-radius:22px;box-shadow:var(--sh-1);padding:18px;display:flex;flex-direction:column;gap:12px;border-top:5px solid var(--c)}
.sf.off{opacity:.6;border-top-color:#b8c4bd}
.sf-h{display:flex;gap:12px;align-items:center}
.sf-h .ad-av{width:52px;height:52px;border-radius:16px;font-size:20px}
.sf-h b{display:block;font-size:16.5px;color:var(--ink)}.sf-h small{color:var(--muted);font-size:12.5px}
.sf-role{display:inline-flex;gap:6px;align-items:center;background:var(--mist);border-radius:10px;padding:5px 10px;font-weight:800;font-size:13px;color:var(--ink-3)}
.sf-role i{color:var(--c)}
.sf-p{display:flex;gap:6px;flex-wrap:wrap}
.sf-p span{background:#f0f6f2;color:var(--ink-3);border-radius:8px;padding:3px 8px;font-size:12px;font-weight:700}
.sf-p span i{color:var(--teal);margin-inline-end:4px}
.sf-meta{display:flex;gap:14px;flex-wrap:wrap;color:var(--muted);font-size:12.5px}
.sf-f{display:flex;gap:6px;flex-wrap:wrap;margin-top:auto}.sf-f form{margin:0}
.np{display:flex;gap:14px;align-items:center;background:linear-gradient(135deg,#0b3119,#11432a);color:#fff;border-radius:20px;padding:18px 20px;margin-bottom:16px;flex-wrap:wrap}
.np code{font-size:18px;font-weight:900;background:rgba(255,255,255,.1);padding:6px 12px;border-radius:10px;direction:ltr;display:inline-block;color:#fff}
.np .sp{flex:1;min-width:220px}
.rl-pick{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px}
.rl-pick label{position:relative;display:block}
.rl-pick input{position:absolute;opacity:0;inset:0;margin:0;cursor:pointer}
.rl-pick span{display:flex;gap:10px;align-items:center;border:2px solid var(--ad-line);border-radius:14px;padding:10px 12px;font-weight:800;font-size:13.5px;transition:.2s;height:100%}
.rl-pick span i{width:30px;height:30px;border-radius:9px;display:grid;place-items:center;background:var(--mist);color:var(--teal);flex:none}
.rl-pick input:checked+span{border-color:var(--teal);background:var(--cyan-soft)}
.rl-pick input:checked+span i{background:var(--teal);color:#fff}
.pm-g{border:1px solid var(--ad-line);border-radius:16px;padding:12px 14px;margin-bottom:10px}
.pm-g h4{margin:0 0 8px;font-size:14.5px;font-weight:900;color:var(--ink);display:flex;gap:8px;align-items:center}
.pm-g h4 i{color:#b98f25}
.pm-l{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:6px}
.pm-l label{display:flex;gap:8px;align-items:center;font-weight:700;font-size:13.5px;background:var(--mist);border-radius:10px;padding:8px 10px;cursor:pointer}
.pm-l input{width:18px;height:18px;accent-color:#1f7a45}
.pm-l label:has(input:checked){background:#e4f4ea;color:#11432a}
.sw{display:inline-flex;gap:10px;align-items:center;font-weight:800;cursor:pointer}
.sw input{width:44px;height:24px;appearance:none;background:#cfdcd4;border-radius:20px;position:relative;cursor:pointer;transition:.2s}
.sw input:before{content:"";position:absolute;top:3px;right:3px;width:18px;height:18px;border-radius:50%;background:#fff;transition:.2s}
.sw input:checked{background:#1f7a45}.sw input:checked:before{right:23px}
.rl-card{background:#fff;border-radius:18px;box-shadow:var(--sh-1);padding:16px}
.rl-card h4{margin:0 0 4px;display:flex;gap:10px;align-items:center;font-size:16px;color:var(--ink)}
.rl-card h4 i{width:36px;height:36px;border-radius:11px;display:grid;place-items:center;background:var(--cyan-soft);color:var(--teal)}
.rl-card p{margin:0 0 10px;color:var(--muted);font-size:13.5px}
</style>
<?php if ($newPass): ?><div class="np"><i class="fas fa-key" style="font-size:24px;color:#7fe0a6"></i><div class="sp"><b><?= $h($msg) ?></b><br><small style="color:#bfe3cc">أرسل للموظف بيانات الدخول الآن — كلمة المرور لا تظهر مرة أخرى. رابط الدخول: your-domain.com/admin/settings.php</small></div><span>اسم الدخول <code><?= $h($newUser) ?></code></span><span>كلمة المرور <code id="npw"><?= $h($newPass) ?></code></span><button class="ad-b g" type="button" onclick="navigator.clipboard.writeText('بيانات دخولك إلى لوحة جامعة بنيان العالمية:\nالرابط: https://your-domain.com/admin/settings.php\nاسم الدخول: <?= $h($newUser) ?>\nكلمة المرور: '+document.getElementById('npw').textContent+'\nغيّر كلمة المرور بعد أول دخول من «كلمة مروري».').then(function(){uwcToast('نُسخت رسالة الدخول')})"><i class="far fa-copy"></i> نسخ رسالة للموظف</button></div><?php endif; ?>
<div class="cp-kpis">
  <div class="cp-kpi" style="--c:#1f7a45"><i class="fas fa-id-badge"></i><span><b><?= count($all) ?></b><span>موظف</span></span></div>
  <div class="cp-kpi" style="--c:#2c5fd0"><i class="fas fa-user-check"></i><span><b><?= $active ?></b><span>حساب نشط</span></span></div>
  <div class="cp-kpi" style="--c:#c9962a"><i class="fas fa-layer-group"></i><span><b><?= count(array_unique(array_column($all, 'role'))) ?></b><span>دور مستخدم</span></span></div>
  <div class="cp-kpi" style="--c:#9b1f45"><i class="fas fa-user-slash"></i><span><b><?= count($all) - $active ?></b><span>موقوف</span></span></div>
</div>
<nav class="sf-tabs"><a href="?tab=list" class="<?= $tab === 'list' ? 'on' : '' ?>"><i class="fas fa-users"></i> الموظفون</a><a href="?tab=roles" class="<?= $tab === 'roles' ? 'on' : '' ?>"><i class="fas fa-sitemap"></i> الأدوار وصلاحياتها</a><a href="?tab=log" class="<?= $tab === 'log' ? 'on' : '' ?>"><i class="fas fa-history"></i> سجل النشاط</a></nav>

<?php if ($tab === 'roles'): ?>
<div class="sf-g"><?php foreach ($ROLES as $rk => $r): if ($rk === 'custom') continue; ?>
  <div class="rl-card"><h4><i class="<?= $r[1] ?>"></i><?= $h($r[0]) ?></h4><p><?= $h($r[2]) ?></p><div class="sf-p"><?php foreach ($r[3] as $pk) if (isset($PERMS[$pk])): ?><span><i class="<?= $PERMS[$pk][1] ?>"></i><?= $PERMS[$pk][2] ?></span><?php endif; ?></div></div>
<?php endforeach; ?></div>
<p style="color:var(--muted);margin-top:14px"><i class="fas fa-info-circle"></i> الأدوار مبنية على «سلّم الصلاحيات» في دليل اللوائح، ويمكنك تعديل صلاحيات أي موظف يدويًا بعد اختيار دوره. كلمة مرور المشرف العام وإدارة الموظفين تبقى للمشرف العام وحده.</p>

<?php elseif ($tab === 'log'): $logs = uwciv_staff_logs(); ?>
<div class="ad-tw"><table class="ad-table"><thead><tr><th>الوقت</th><th>المستخدم</th><th>الحدث</th><th>IP</th></tr></thead><tbody>
<?php foreach (array_slice($logs, 0, 200) as $l): ?><tr><td dir="ltr" style="text-align:right;color:var(--muted)"><?= $h($l['t']) ?></td><td><b><?= $h($l['who']) ?></b></td><td><?= $h($l['e']) ?></td><td dir="ltr" style="text-align:right;color:var(--muted);font-size:12.5px"><?= $h($l['ip']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php if (!$logs): ?><div class="cp-card"><div class="cp-empty"><i class="fas fa-history"></i>لا يوجد نشاط مسجّل بعد.</div></div><?php endif; ?>

<?php else: ?>
<?php if (!$all): ?><div class="cp-card"><div class="cp-empty"><i class="fas fa-id-badge"></i>لا يوجد موظفون بعد.<br>أضف أول موظف وحدّد دوره، فيدخل إلى لوحاته فقط.<br><button class="cp-b p" style="margin-top:12px" type="button" data-st-new><i class="fas fa-user-plus"></i> موظف جديد</button></div></div><?php endif; ?>
<div class="sf-g">
<?php foreach ($all as $i => $s): $r = $ROLES[$s['role']] ?? $ROLES['custom']; $c = $cols[$i % 6]; ?>
  <article class="sf<?= empty($s['active']) ? ' off' : '' ?>" style="--c:<?= $c ?>">
    <div class="sf-h"><span class="ad-av" style="--c:<?= $c ?>"><?= $h(mb_substr($s['name'], 0, 1)) ?></span><span><b><?= $h($s['name']) ?></b><small dir="ltr"><?= $h($s['username']) ?><?= $s['email'] ? ' · ' . $h($s['email']) : '' ?></small></span><span style="margin-inline-start:auto"><?= empty($s['active']) ? '<span class="cp-pill r">موقوف</span>' : '<span class="cp-pill y">نشط</span>' ?></span></div>
    <div><span class="sf-role"><i class="<?= $r[1] ?>"></i><?= $h($r[0]) ?><?= $s['unit'] ? ' — ' . $h($s['unit']) : '' ?><?= !empty($s['college']) && isset($UWCIV_COLLEGES[$s['college']]) ? ' — ' . $h($UWCIV_COLLEGES[$s['college']]['n']) : '' ?></span></div>
    <div class="sf-p"><?php foreach ($s['perms'] as $pk) if (isset($PERMS[$pk])): ?><span><i class="<?= $PERMS[$pk][1] ?>"></i><?= $PERMS[$pk][2] ?></span><?php endif; ?></div>
    <div class="sf-meta"><span><i class="far fa-calendar-plus"></i> أُضيف <?= $h(substr($s['created'], 0, 10)) ?></span><span><i class="fas fa-sign-in-alt"></i> آخر دخول: <?= $s['last'] ? $h($s['last']) : 'لم يدخل بعد' ?></span></div>
    <div class="sf-f">
      <button class="cp-b p sm" type="button" data-st-edit='<?= $h(json_encode(array_diff_key($s, ['hash' => 1]), JSON_UNESCAPED_UNICODE)) ?>'><i class="fas fa-user-cog"></i> تعديل الصلاحيات</button>
      <form method="post" data-confirm="إنشاء كلمة مرور جديدة لـ <?= $h($s['name']) ?>؟"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="reset"><input type="hidden" name="id" value="<?= $h($s['id']) ?>"><button class="cp-b g sm"><i class="fas fa-key"></i> كلمة جديدة</button></form>
      <form method="post"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="toggle"><input type="hidden" name="id" value="<?= $h($s['id']) ?>"><button class="cp-b g sm"><i class="fas <?= empty($s['active']) ? 'fa-play' : 'fa-pause' ?>"></i> <?= empty($s['active']) ? 'تفعيل' : 'إيقاف' ?></button></form>
      <form method="post" data-confirm="حذف الموظف <?= $h($s['name']) ?> نهائيًا؟"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="del"><input type="hidden" name="id" value="<?= $h($s['id']) ?>"><button class="cp-b r sm" title="حذف"><i class="fas fa-trash"></i></button></form>
    </div>
  </article>
<?php endforeach; ?>
</div>
<?php endif; ?>

<dialog class="ad-dlg" id="stD" style="width:min(900px,96vw)"><form method="post"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="id" id="sId">
  <h3><i class="fas fa-user-plus" style="color:var(--teal)"></i> <span id="sT">موظف جديد</span><button type="button" class="x" data-dlg-close><i class="fas fa-times"></i></button></h3>
  <div class="cp-f three">
    <label>الاسم الكامل *<div class="cp-in"><i class="far fa-user"></i><input name="name" id="sN" required maxlength="80"></div></label>
    <label>اسم الدخول (إنجليزي) *<div class="cp-in"><i class="fas fa-at"></i><input name="username" id="sU" required dir="ltr" pattern="[a-zA-Z0-9._-]{3,30}" placeholder="ahmed.ali"></div></label>
    <label>البريد الإلكتروني<div class="cp-in"><i class="far fa-envelope"></i><input type="email" name="email" id="sE" dir="ltr"></div></label>
    <label>الجوال / واتساب<div class="cp-in"><i class="fab fa-whatsapp"></i><input name="phone" id="sP" dir="ltr"></div></label>
    <label>الجهة / الكلية / القسم<div class="cp-in"><i class="fas fa-building"></i><input name="unit" id="sUn" placeholder="مثال: كلية الشريعة"></div></label>
    <label>الكلية (يقيّد صلاحية «الكليات» بكلية واحدة)<div class="cp-in"><i class="fas fa-university"></i><select name="college" id="sCol"><option value="">— كل الكليات —</option><?php foreach ($UWCIV_COLLEGES as $cs => $cc): ?><option value="<?= $cs ?>"><?= $h($cc['n']) ?></option><?php endforeach; ?></select></div></label>
    <label><span id="sPwL">كلمة المرور (فارغة = توليد تلقائي)</span><div class="cp-in"><i class="fas fa-key"></i><input type="password" name="pw" id="sPw" minlength="8" dir="ltr" autocomplete="new-password"><button type="button" class="eye"><i class="far fa-eye"></i></button></div></label>
  </div>
  <h4 style="margin:18px 0 8px;color:var(--ink)"><i class="fas fa-sitemap" style="color:#b98f25"></i> الدور الوظيفي <small style="color:var(--muted);font-weight:700">— يضبط الصلاحيات تلقائيًا حسب سلّم الصلاحيات</small></h4>
  <div class="rl-pick"><?php foreach ($ROLES as $rk => $r): ?><label title="<?= $h($r[2]) ?>"><input type="radio" name="role" value="<?= $rk ?>" data-perms="<?= $h(implode(',', $r[3])) ?>"><span><i class="<?= $r[1] ?>"></i><?= $h($r[0]) ?></span></label><?php endforeach; ?></div>
  <h4 style="margin:18px 0 8px;color:var(--ink)"><i class="fas fa-shield-alt" style="color:#b98f25"></i> الصلاحيات <small style="color:var(--muted);font-weight:700">— اللوحات التي يستطيع الدخول إليها</small></h4>
  <?php $byG = []; foreach ($PERMS as $pk => $pd) $byG[$pd[0]][$pk] = $pd; foreach ($byG as $g => $ps): ?>
    <div class="pm-g"><h4><i class="<?= $G[$g] ?? 'fas fa-folder' ?>"></i><?= $g ?></h4><div class="pm-l"><?php foreach ($ps as $pk => $pd): ?><label><input type="checkbox" name="perms[]" value="<?= $pk ?>"><i class="<?= $pd[1] ?>" style="color:var(--teal)"></i><?= $pd[2] ?></label><?php endforeach; ?></div></div>
  <?php endforeach; ?>
  <div class="cp-btns"><label class="sw"><input type="checkbox" name="active" id="sA" value="1" checked> الحساب نشط</label><span style="flex:1"></span><button type="button" class="cp-b g" data-dlg-close>إلغاء</button><button class="cp-b p"><i class="fas fa-save"></i> حفظ الموظف</button></div>
</form></dialog>
<script>
(function(){var d=document.getElementById('stD'),f=d.querySelector('form'),$=function(i){return document.getElementById(i)};
function setPerms(list){f.querySelectorAll('[name="perms[]"]').forEach(function(c){c.checked=list.indexOf(c.value)>-1});}
f.querySelectorAll('[name=role]').forEach(function(r){r.addEventListener('change',function(){if(r.value!=='custom')setPerms(r.dataset.perms.split(','));});});
f.querySelectorAll('[name="perms[]"]').forEach(function(c){c.addEventListener('change',function(){var cur=[].filter.call(f.querySelectorAll('[name="perms[]"]'),function(x){return x.checked}).map(function(x){return x.value}).sort().join(','),sel=f.querySelector('[name=role]:checked');if(sel&&sel.value!=='custom'&&sel.dataset.perms.split(',').sort().join(',')!==cur)f.querySelector('[name=role][value=custom]').checked=true;});});
function open(x){f.reset();x=x||{};$('sId').value=x.id||'';$('sN').value=x.name||'';$('sU').value=x.username||'';$('sE').value=x.email||'';$('sP').value=x.phone||'';$('sUn').value=x.unit||'';$('sCol').value=x.college||'';$('sA').checked=x.id?!!x.active:true;
  var role=x.role||'faculty';var r=f.querySelector('[name=role][value="'+role+'"]');if(r)r.checked=true;setPerms(x.perms||r.dataset.perms.split(','));
  $('sT').textContent=x.id?'تعديل '+x.name:'موظف جديد';$('sPwL').textContent=x.id?'كلمة مرور جديدة (اتركها فارغة للإبقاء)':'كلمة المرور (فارغة = توليد تلقائي)';d.showModal();$('sN').focus();}
document.querySelectorAll('[data-st-new]').forEach(function(b){b.onclick=function(){open()}});
document.querySelectorAll('[data-st-edit]').forEach(function(b){b.onclick=function(){open(JSON.parse(b.getAttribute('data-st-edit')))}});
<?php if ($err && ($_POST['act'] ?? '') === 'save'): ?>open(<?= json_encode(['id' => $_POST['id'] ?? '', 'name' => $_POST['name'] ?? '', 'username' => $_POST['username'] ?? '', 'email' => $_POST['email'] ?? '', 'phone' => $_POST['phone'] ?? '', 'unit' => $_POST['unit'] ?? '', 'role' => $_POST['role'] ?? 'custom', 'college' => $_POST['college'] ?? '', 'perms' => array_values((array) ($_POST['perms'] ?? [])), 'active' => !empty($_POST['active'])], JSON_UNESCAPED_UNICODE) ?>);<?php endif; ?>
})();
</script>
<?php uwciv_admin_close(); ?>
