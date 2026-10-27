<?php
/* لوحة تحكم المشرف العام — جامعة بنيان العالمية */
require_once dirname(__DIR__) . '/db_config.php';
require_once dirname(__DIR__) . '/inc/settings.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once dirname(__DIR__) . '/inc/admin_ui.php';
uwciv_admin_gate(['title' => 'لوحة التحكم']);

$msg = ''; $err = '';
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];


$S = uwciv_settings();
require dirname(__DIR__) . '/inc/calendar_data.php';
$CAL = (!empty($S['calendar']) && is_array($S['calendar'])) ? $S['calendar'] : $TERMS;
$CALYEAR = $S['year'] ?? '' ?: $YEAR;
$TYPES = ['reg' => 'تسجيل', 'study' => 'دراسة', 'exam' => 'اختبارات', 'result' => 'نتائج', 'holiday' => 'إجازة', 'event' => 'مناسبة'];
$TK_FILE = dirname(__DIR__) . '/_private/tickets.json';

$SUPER = uwciv_is_super(); $ME = uwciv_staff_me();
$need = ['social' => 'social', 'contact' => 'contact', 'mail' => 'mail', 'mailclear' => 'mail', 'testmail' => 'mail', 'password' => 'password', 'calendar' => 'calendar', 'ticket' => 'tickets', 'mypass' => 'overview', 'ticker' => 'ticker'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $what = (string) $_POST['save'];
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) { $err = 'انتهت صلاحية الصفحة، أعد المحاولة.'; }
    elseif (!isset($need[$what]) || !uwciv_can($need[$what]) || ($what === 'mypass' && !$ME)) { $err = 'لا تملك صلاحية هذا الإجراء.'; }
    else {
        if ($what === 'mypass') {
            $a = (string) ($_POST['np'] ?? '');
            if (!password_verify((string) ($_POST['cp'] ?? ''), $ME['hash'])) $err = 'كلمة المرور الحالية غير صحيحة.';
            elseif (mb_strlen($a) < 8) $err = 'كلمة المرور الجديدة 8 أحرف على الأقل.';
            elseif ($a !== ($_POST['np2'] ?? '')) $err = 'كلمتا المرور غير متطابقتين.';
            else { $all = uwciv_staff_all(); foreach ($all as &$x) if ($x['id'] === $ME['id']) $x['hash'] = password_hash($a, PASSWORD_BCRYPT); unset($x); uwciv_staff_save($all); uwciv_staff_log('غيّر كلمة مروره'); header('Location: settings.php?ok=' . rawurlencode('تم تغيير كلمة مرورك.') . '#mypass'); exit; }
        }
        if ($what === 'social') {
            $S['social'] = [];
            foreach (uwciv_social_defs() as $k => $d) {
                $v = trim($_POST['social'][$k] ?? '');
                if ($v !== '' && !preg_match('~^https?://~i', $v)) $v = 'https://' . $v;
                if ($v !== '' && !filter_var($v, FILTER_VALIDATE_URL)) { $err = 'رابط غير صالح: ' . $d[0]; break; }
                if ($v !== '') $S['social'][$k] = $v;
            }
            if (!$err) $msg = 'تم حفظ روابط التواصل الاجتماعي.';
        } elseif ($what === 'contact') {
            $ph = preg_replace('/[^\d+]/', '', $_POST['phone'] ?? '');
            $loc = trim($_POST['location'] ?? '');
            if ($loc !== '' && !preg_match('~^https?://~i', $loc)) $loc = 'https://' . $loc;
            if ($ph !== '' && !preg_match('/^\+?\d{8,15}$/', $ph)) $err = 'رقم التواصل غير صحيح — اكتبه بالمفتاح الدولي مثل +8801703597625';
            elseif ($loc !== '' && !filter_var($loc, FILTER_VALIDATE_URL)) $err = 'رابط الموقع على الخريطة غير صالح.';
            else { $S['phone'] = $ph; $S['location'] = $loc; $S['address'] = trim(mb_substr($_POST['address'] ?? '', 0, 200)); $msg = 'تم حفظ بيانات التواصل والموقع.'; }
        } elseif ($what === 'mail' || $what === 'mailclear') {
            $v = preg_replace('/\s+/', '', $_POST['smtp_pass'] ?? '');
            if ($what === 'mailclear') { $S['smtp_pass'] = ''; $msg = 'تم حذف كلمة مرور التطبيق، والإرسال الآن عبر بريد الاستضافة.'; }
            elseif (strlen($v) !== 16) $err = 'كلمة مرور التطبيق من Google تتكون من 16 حرفًا بالضبط.';
            else { $S['smtp_pass'] = $v; $msg = 'تم حفظ كلمة مرور التطبيق.'; }
        } elseif ($what === 'testmail') {
            require_once dirname(__DIR__) . '/inc/mailer.php';
            $ok = uwciv_mail(UWCIV_MAIL_TO, 'رسالة اختبار من لوحة التحكم', '<p>هذه رسالة اختبار من لوحة تحكم موقع جامعة بنيان العالمية — ' . date('Y-m-d H:i') . '</p>');
            if ($ok) $msg = 'أُرسلت رسالة اختبار إلى ' . UWCIV_MAIL_TO . ' — افحص البريد الوارد ومجلد Spam.'; else $err = 'تعذّر إرسال رسالة الاختبار.';
        } elseif ($what === 'password') {
            $a = $_POST['np'] ?? ''; $b = $_POST['np2'] ?? '';
            if (!uwciv_admin_login('admin', $_POST['cp'] ?? '')) $err = 'كلمة المرور الحالية غير صحيحة.';
            elseif (mb_strlen($a) < 10) $err = 'كلمة المرور الجديدة يجب ألا تقل عن 10 أحرف.';
            elseif ($a !== $b) $err = 'كلمتا المرور غير متطابقتين.';
            else { $S['admin_hash'] = password_hash($a, PASSWORD_BCRYPT); $msg = 'تم تغيير كلمة مرور المشرف العام. استخدمها من الآن في كل لوحات التحكم.'; }
        } elseif ($what === 'ticker') {
            $items = [];
            foreach ((array) ($_POST['tk'] ?? []) as $x) {
                $t = trim(mb_substr(preg_replace('/\s+/u', ' ', strip_tags((string) ($x['t'] ?? ''))), 0, 220)); if ($t === '') continue;
                $u = trim((string) ($x['u'] ?? '')); if ($u !== '' && !preg_match('~^(/[^\s]*|https?://[^\s]+)$~i', $u)) $u = '';
                $i = (string) ($x['i'] ?? ''); if (!preg_match('/^(fas|far|fab) fa-[a-z0-9-]+$/', $i)) $i = 'fas fa-circle';
                $items[] = ['t' => $t, 'u' => $u, 'i' => $i, 'hot' => !empty($x['hot'])];
            }
            $S['ticker'] = ['on' => !empty($_POST['tk_on']), 'auto' => !empty($_POST['tk_auto']), 'speed' => max(15, min(200, (int) ($_POST['tk_speed'] ?? 45))), 'label' => trim(mb_substr(strip_tags((string) ($_POST['tk_label'] ?? '')), 0, 30)) ?: 'آخر الأخبار', 'items' => array_slice($items, 0, 30)];
            $msg = 'تم حفظ شريط الأخبار ونشره في الموقع.';
        } elseif ($what === 'calendar') {
            $terms = [];
            foreach (($_POST['term'] ?? []) as $ti => $tname) {
                $tname = trim($tname); if ($tname === '') continue;
                $rows = [];
                foreach (($_POST['ev'][$ti] ?? []) as $e) {
                    $title = trim($e['title'] ?? ''); $start = $e['start'] ?? ''; $end = $e['end'] ?? '';
                    if ($title === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) continue;
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end) || $end < $start) $end = null;
                    $type = isset($TYPES[$e['type'] ?? '']) ? $e['type'] : 'event';
                    $rows[] = [$type, $title, $start, $end, trim($e['note'] ?? '')];
                }
                usort($rows, function ($x, $y) { return strcmp($x[2], $y[2]); });
                if ($rows) $terms[] = [$tname, $rows];
            }
            if (!$terms) $err = 'أضف حدثًا واحدًا على الأقل بتاريخ صحيح.';
            else { $S['calendar'] = $terms; $S['year'] = trim($_POST['year'] ?? '') ?: $CALYEAR; $CAL = $terms; $CALYEAR = $S['year']; $msg = 'تم حفظ التقويم الرسمي ونشره في صفحة التقويم.'; }
        } elseif ($what === 'ticket') {
            $T = is_file($TK_FILE) ? (json_decode((string) file_get_contents($TK_FILE), true) ?: []) : [];
            foreach ($T as &$t) if ($t['ref'] === ($_POST['ref'] ?? '')) { $t['status'] = ($t['status'] ?? 'open') === 'open' ? 'closed' : 'open'; $msg = 'تم تحديث حالة الرسالة ' . $t['ref']; }
            unset($t); @file_put_contents($TK_FILE, json_encode($T, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
        }
        if (!$err && $ME && $what !== 'mypass') uwciv_staff_log('عدّل الإعدادات: ' . $what);
        if (!$err && $what !== 'testmail' && $what !== 'ticket' && $what !== 'mypass') { if (!uwciv_settings_save($S)) $err = 'تعذّر حفظ الإعدادات على الخادم.'; }
        if (!$err) { header('Location: settings.php?ok=' . rawurlencode($msg) . '#' . ($what === 'testmail' || $what === 'mailclear' ? 'mail' : ($what === 'ticket' ? 'tickets' : $what))); exit; }
    }
}
if (isset($_GET['ok'])) $msg = (string) $_GET['ok'];
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };

/* ===== بيانات لوحة النظرة العامة ===== */
$D = ['apps' => 0, 'apps_week' => 0, 'last_apps' => [], 'jobs' => 0, 'jobs_new' => 0, 'last_jobs' => [], 'tickets' => [], 'tk_open' => 0, 'members' => 0, 'members_named' => 0, 'regs' => 0, 'regs_ready' => 0, 'books' => 0, 'news' => 0, 'docs' => 0, 'exams' => 0];
if (uwciv_can('tickets')) {
    $D['tickets'] = is_file($TK_FILE) ? array_reverse(json_decode((string) file_get_contents($TK_FILE), true) ?: []) : [];
    $D['tk_open'] = count(array_filter($D['tickets'], function ($t) { return ($t['status'] ?? 'open') === 'open'; }));
}
if ($SUPER) {
    try {
        $db = db_pdo('logweb');
        $D['apps'] = (int) $db->query('SELECT COUNT(*) FROM info')->fetchColumn();
        $D['last_apps'] = $db->query('SELECT id, fname, lname, stage, depart, country FROM info ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {}
    foreach (['books' => ['logweb', 'stdlb'], 'news' => ['logweb', 'posts'], 'docs' => ['logweb', 'student'], 'exams' => ['oesm', 'quiz']] as $k => $q) {
        try { $D[$k] = (int) db_pdo($q[0])->query('SELECT COUNT(*) FROM ' . $q[1])->fetchColumn(); } catch (Throwable $e) {}
    }
    require_once dirname(__DIR__) . '/inc/jobs.php';
    $J = uwciv_jobs_apps(); $D['jobs'] = count($J); $D['jobs_new'] = count(array_filter($J, function ($a) { return $a['status'] === 'new'; })); $D['last_jobs'] = array_slice(array_reverse($J), 0, 4);
    $D['tickets'] = is_file($TK_FILE) ? array_reverse(json_decode((string) file_get_contents($TK_FILE), true) ?: []) : [];
    $D['tk_open'] = count(array_filter($D['tickets'], function ($t) { return ($t['status'] ?? 'open') === 'open'; }));
    require_once dirname(__DIR__) . '/inc/members.php';
    $M = uwciv_members(); $D['members'] = count($M); $D['members_named'] = count(array_filter($M, function ($x) { return trim($x['name']) !== ''; }));
    require_once dirname(__DIR__) . '/inc/regs.php';
    $RG = uwciv_regs()['docs']; $D['regs'] = count($RG); $D['regs_ready'] = count(array_filter($RG, function ($d) { return $d['status'] === 'ready'; }));
}
$checks = [
    ['البريد مضبوط بكلمة مرور Gmail', !empty($S['smtp_pass']), '#mail', 'اختياري — الإرسال يعمل حاليًا عبر بريد الاستضافة'],
    ['روابط التواصل الاجتماعي', !empty($S['social']), '#social', 'أضف روابط صفحات الجامعة'],
    ['التقويم الدراسي الرسمي', !empty($S['calendar']), '#calendar', 'اعتمد مواعيد العام الجامعي'],
    ['أسماء أعضاء الجامعة', $D['members'] && $D['members_named'] >= $D['members'] / 2, '/admin/members.php', $D['members_named'] . ' من ' . $D['members'] . ' منصبًا بأسماء'],
    ['اعتماد اللوائح والأنظمة', $D['regs'] && $D['regs_ready'] >= $D['regs'] - 3, '/admin/regs.php', $D['regs_ready'] . ' من ' . $D['regs'] . ' وثيقة معتمدة'],
    ['كلمة مرور خاصة بالمشرف', !empty($S['admin_hash']), '#password', 'غيّر الكلمة المؤقتة إلى كلمة من اختيارك'],
];
$score = round(count(array_filter($checks, function ($c) { return $c[1]; })) / count($checks) * 100);
$hour = (int) date('G') + 3; $greet = ($hour % 24) < 12 ? 'صباح الخير' : 'مساء الخير';
$C = uwciv_contact();
$sub = $greet . '، ' . ($SUPER ? 'المشرف العام' : $h($ME['name'] ?? '')) . ' · <span id="cpd"></span>';
uwciv_admin_open('overview', 'نظرة عامة', $sub);
uwciv_admin_toast($err ?: $msg, (bool) $err);
?>
<style>
.st-hero{position:relative;overflow:hidden;border-radius:26px;padding:26px 28px;margin-bottom:18px;color:#fff;background:radial-gradient(500px 260px at 100% 0,rgba(70,192,122,.35),transparent 60%),radial-gradient(400px 300px at 0 100%,rgba(201,150,42,.25),transparent 60%),linear-gradient(135deg,#0b3119,#06210f);display:grid;grid-template-columns:1fr auto;gap:20px;align-items:center;box-shadow:var(--sh-lift)}
.st-hero:before{content:"";position:absolute;inset:0;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='70' height='70'%3E%3Cpath d='M35 5l8 22 22 8-22 8-8 22-8-22-22-8 22-8z' fill='none' stroke='%23fff' stroke-opacity='.06'/%3E%3C/svg%3E")}
.st-hero>*{position:relative}
.st-hero small{color:#9fe0b8;font-weight:800}
.st-hero h2{margin:6px 0 6px;font-size:clamp(22px,2.6vw,30px);font-weight:900;color:#fff}
.st-hero p{margin:0;color:#cfe6d7;font-size:14px}
.st-mini{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}
.st-mini a{display:flex;gap:8px;align-items:center;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:8px 12px;color:#fff!important;text-decoration:none!important;font-size:13px;font-weight:700;transition:.2s}
.st-mini a:hover{background:rgba(255,255,255,.16)}
.st-mini b{font-size:17px;color:#7fe0a6}
.st-clock{text-align:center;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:20px;padding:14px 22px}
.st-clock b{display:block;font-size:34px;font-weight:900;letter-spacing:1px;font-variant-numeric:tabular-nums;direction:ltr}
.st-clock span{font-size:12px;color:#bfe3cc}
.cp-row{display:grid;grid-template-columns:1.3fr 1fr;gap:18px;margin-bottom:18px}
.cp-box{background:#fff;border-radius:22px;box-shadow:var(--sh-1);padding:20px 22px}
.cp-box h3{margin:0 0 14px;font-size:17px;font-weight:900;color:var(--ink);display:flex;gap:10px;align-items:center}
.cp-box h3 i{color:var(--teal)}
.cp-box h3 a{margin-inline-start:auto;font-size:13px;font-weight:800}
.cp-health{display:grid;grid-template-columns:140px 1fr;gap:20px;align-items:center}
.cp-ring{width:140px;height:140px;border-radius:50%;display:grid;place-items:center;background:conic-gradient(#1f7a45 calc(var(--p)*1%),#e6efe9 0)}
.cp-ring div{width:108px;height:108px;border-radius:50%;background:#fff;display:grid;place-items:center;text-align:center}
.cp-ring b{font-size:30px;font-weight:900;color:var(--ink);line-height:1}.cp-ring span{font-size:12px;color:var(--muted)}
.cp-checks{list-style:none;margin:0;padding:0;display:grid;gap:6px}
.cp-checks a{display:flex;gap:10px;align-items:center;padding:7px 10px;border-radius:10px;text-decoration:none!important;color:var(--text)!important;font-size:14px;transition:.2s}
.cp-checks a:hover{background:var(--mist)}
.cp-checks i{width:24px;height:24px;border-radius:50%;display:grid;place-items:center;font-size:11px;flex:none}
.cp-checks .y i{background:#e4f4ea;color:#1f7a45}.cp-checks .n i{background:#fff4e0;color:#b7791f}
.cp-checks b{font-weight:800}.cp-checks small{color:var(--muted);margin-inline-start:auto;font-size:12px;text-align:left}
.cp-quick{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.cp-quick a{display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center;padding:16px 8px;border-radius:16px;background:var(--mist);text-decoration:none!important;color:var(--ink-3)!important;font-weight:800;font-size:13.5px;transition:.2s}
.cp-quick a:hover{background:var(--cyan-soft);transform:translateY(-3px)}
.cp-quick i{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;background:#fff;color:var(--c);font-size:18px;box-shadow:var(--sh-1)}
.cp-list{list-style:none;margin:0;padding:0}
.cp-list li{display:flex;gap:12px;align-items:center;padding:10px 0;border-top:1px dashed #e5ede8}
.cp-list li:first-child{border:0}
.cp-list .av{width:40px;height:40px;border-radius:12px;display:grid;place-items:center;font-weight:900;color:#fff;background:var(--c,#1f7a45);flex:none}
.cp-list b{display:block;color:var(--ink);font-size:14.5px}.cp-list small{color:var(--muted);font-size:12.5px}
.cp-list .tg{margin-inline-start:auto;font-size:12px;font-weight:800;border-radius:20px;padding:3px 10px;background:var(--mist);color:var(--ink-3);white-space:nowrap}
.cp-steps{counter-reset:s;list-style:none;margin:0 0 16px;padding:0;display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.cp-steps li{counter-increment:s;background:var(--mist);border-radius:14px;padding:12px 14px;font-size:13.5px;line-height:1.8}
.cp-steps li:before{content:counter(s);display:inline-grid;place-items:center;width:24px;height:24px;border-radius:8px;background:var(--ink-3);color:#fff;font-weight:900;font-size:12px;margin-inline-end:6px}
.cp-meter{height:8px;border-radius:6px;background:#e6efe9;overflow:hidden;margin-top:4px}.cp-meter i{display:block;height:100%;width:0;transition:.3s;border-radius:6px}
.cp-meter-t{font-size:12px;font-weight:800;color:var(--muted)}
.cp-soc{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.cp-soc label .cp-in i{color:var(--c)}
.cp-soc-prev{display:flex;gap:8px;margin-top:14px;padding:12px;background:#0b3119;border-radius:14px;min-height:48px;align-items:center;flex-wrap:wrap}
.cp-soc-prev span{width:36px;height:36px;border-radius:50%;display:grid;place-items:center;border:1px solid rgba(255,255,255,.25);color:#fff}
.cp-soc-prev em{color:#9fe0b8;font-style:normal;font-size:13px}
.cp-map{border-radius:16px;overflow:hidden;height:220px;margin-top:14px;border:1px solid #e3ebe6}
.cp-map iframe{width:100%;height:100%;border:0}
.cp-term{border:1px solid #e3eee6;border-radius:18px;padding:16px;margin-bottom:14px;background:#fbfdfb}
.cp-term>input{width:100%;box-sizing:border-box;border:2px solid #e3ece6;border-radius:12px;padding:10px 14px;font-weight:900;font-family:inherit;font-size:16px;margin-bottom:12px}
.cp-ev{display:grid;grid-template-columns:120px 1fr 150px 150px 1fr 40px;gap:8px;margin-bottom:8px}
.cp-ev input,.cp-ev select{border:1.5px solid #dbe7df;border-radius:10px;padding:0 10px;height:40px;font-family:inherit;font-size:14px;background:#fff;min-width:0}
.cp-hd{display:grid;grid-template-columns:120px 1fr 150px 150px 1fr 40px;gap:8px;font-size:12px;color:var(--muted);font-weight:800;margin-bottom:6px}
.cp-del{border:0;background:#fdecef;color:#c0392b;border-radius:10px;cursor:pointer}
.cp-add{border:1.5px dashed #bcd8c6;background:#fff;color:var(--teal);border-radius:12px;padding:8px 14px;font-weight:800;cursor:pointer;font-family:inherit}
.cp-tk{border:1px solid #e3ebe6;border-radius:16px;margin-bottom:10px;overflow:hidden}
.cp-tk summary{list-style:none;cursor:pointer;display:grid;grid-template-columns:140px 1fr 1.4fr auto;gap:12px;align-items:center;padding:12px 16px}
.cp-tk summary::-webkit-details-marker{display:none}
.cp-tk summary b{color:var(--ink)}.cp-tk summary small{display:block;color:var(--muted);font-size:12px}
.cp-tk .body{padding:0 16px 16px;line-height:1.9;font-size:14.5px}
.cp-tk .msg{background:var(--mist);border-radius:12px;padding:12px;margin-bottom:10px}
.cp-tk.closed{opacity:.6}
.tk-r{display:grid;grid-template-columns:150px 40px minmax(0,2.4fr) minmax(0,1fr) auto auto;gap:8px;align-items:center;margin-bottom:8px}
.tk-r input[type=text],.tk-r input:not([type]),.tk-r select{border:1.5px solid #dbe7df;border-radius:10px;height:42px;padding:0 10px;font-family:inherit;font-size:14px;min-width:0;width:100%;box-sizing:border-box;background:#fff}
.tk-r .tki{width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:#fdf7e7;color:#b98f25}
.tk-r .hot{display:flex;gap:6px;align-items:center;font-weight:800;font-size:13px;color:#c0392b;white-space:nowrap}
.tk-r .bt{display:flex;gap:3px}.tk-r .bt button{border:0;width:32px;height:32px;border-radius:9px;background:var(--mist);cursor:pointer}.tk-r .bt .rm{background:#fdecef;color:#c0392b}
.sw-l{display:flex!important;flex-direction:row!important;gap:8px;align-items:center}.sw-l input{width:18px;height:18px;accent-color:#1f7a45}
.tk-prev{display:flex;gap:10px;align-items:center;border:1px solid #eadfbe;background:#fdf7e7;border-radius:14px;padding:8px 12px;margin:14px 0;overflow:hidden}
.tk-prev .tkl{flex:none;background:linear-gradient(135deg,#9b1f45,#c0392b);color:#fff;border-radius:20px;padding:5px 12px;font-weight:900;font-size:13px}
#tkP{white-space:nowrap;overflow:hidden;font-weight:700;color:#11432a;font-size:14px;-webkit-mask-image:linear-gradient(90deg,transparent,#000 8%,#000 92%,transparent)}
@media(max-width:900px){.tk-r{grid-template-columns:40px minmax(0,1fr) auto}.tk-r select{display:none}.tk-r input[name$="[u]"]{grid-column:2/3}}
@media(max-width:1200px){.cp-row{grid-template-columns:minmax(0,1fr)}}
@media(max-width:960px){.cp-soc,.cp-steps{grid-template-columns:minmax(0,1fr)}.cp-ev,.cp-hd{grid-template-columns:1fr 1fr}.cp-hd{display:none}.cp-health{grid-template-columns:1fr;justify-items:center}.cp-tk summary{grid-template-columns:1fr auto}.cp-tk summary>*:nth-child(2){display:none}.st-hero{grid-template-columns:1fr}.st-clock{display:none}}
@media(max-width:560px){.cp-quick{grid-template-columns:repeat(2,1fr)}}
</style>

    <!-- نظرة عامة -->
<?php if ($SUPER): ?>
    <section class="cp-pane" id="p-overview" data-t="نظرة عامة" data-k="overview">
      <div class="st-hero"><div><small><i class="fas fa-star"></i> مركز الإدارة</small><h2><?= $greet ?>، أهلًا بك في لوحة جامعة بنيان العالمية</h2><p>كل ما يحتاج متابعتك اليوم في مكان واحد. اضغط <kbd style="color:#fff">Ctrl K</kbd> للانتقال السريع لأي لوحة.</p>
        <div class="st-mini"><a href="/blog/admin/index.php"><b><?= $D['news'] ?></b> خبر منشور</a><a href="/librarysystem/admin/index.php"><b><?= $D['books'] ?></b> كتاب في المكتبة</a><a href="/pdf_grad/admin/index.php"><b><?= $D['docs'] ?></b> وثيقة تخرج</a><a href="/onlineexam/dash.php"><b><?= $D['exams'] ?></b> امتحان إلكتروني</a><a href="/admin/regs.php"><b><?= $D['regs'] ?></b> لائحة ودليل</a></div></div>
        <div class="st-clock"><b id="stc">--:--</b><span>بتوقيت مكة المكرمة</span></div></div>
      <div class="cp-kpis">
        <a class="cp-kpi" style="--c:#1f7a45" href="/student/admin/list.php"><i class="fas fa-user-graduate"></i><span><b><?= $D['apps'] ?></b><span>طلب التحاق</span><small>عرض الطلبات ←</small></span></a>
        <a class="cp-kpi" style="--c:#2c5fd0" href="/admin/jobs.php"><i class="fas fa-briefcase"></i><span><b><?= $D['jobs'] ?></b><span>طلب توظيف</span><small><?= $D['jobs_new'] ?> جديد بانتظار المراجعة</small></span></a>
        <a class="cp-kpi" style="--c:#9b1f45" href="#tickets" data-go="tickets"><i class="fas fa-inbox"></i><span><b><?= count($D['tickets']) ?></b><span>رسالة دعم وشكوى</span><small><?= $D['tk_open'] ?> مفتوحة</small></span></a>
        <a class="cp-kpi" style="--c:#c9962a" href="/admin/members.php"><i class="fas fa-users"></i><span><b><?= $D['members_named'] ?>/<?= $D['members'] ?></b><span>أعضاء بأسماء</span><small>أكمل الأسماء ←</small></span></a>
      </div>
      <div class="cp-row">
        <div class="cp-box"><h3><i class="fas fa-heartbeat"></i> جاهزية الموقع</h3>
          <div class="cp-health"><div class="cp-ring" style="--p:<?= $score ?>"><div><span><b><?= $score ?>%</b><br>مكتمل</span></div></div>
            <ul class="cp-checks"><?php foreach ($checks as $c): ?><li><a class="<?= $c[1] ? 'y' : 'n' ?>" href="<?= $c[2] ?>" <?= $c[2][0] === '#' ? 'data-go="' . substr($c[2], 1) . '"' : '' ?>><i class="fas <?= $c[1] ? 'fa-check' : 'fa-exclamation' ?>"></i><b><?= $c[0] ?></b><small><?= $c[1] ? 'تم' : $c[3] ?></small></a></li><?php endforeach; ?></ul></div></div>
        <div class="cp-box"><h3><i class="fas fa-bolt"></i> إجراءات سريعة</h3>
          <div class="cp-quick">
            <a href="/admin/members.php" style="--c:#c9962a"><i class="fas fa-user-plus"></i>إضافة عضو</a>
            <a href="/admin/regs.php" style="--c:#9b1f45"><i class="fas fa-file-upload"></i>رفع لائحة</a>
            <a href="/admin/jobs.php?tab=pos" style="--c:#2c5fd0"><i class="fas fa-bullhorn"></i>نشر شاغر</a>
            <a href="/blog/admin/index.php" style="--c:#1f7a45"><i class="far fa-newspaper"></i>نشر خبر</a>
            <a href="#calendar" data-go="calendar" style="--c:#0e9aa7"><i class="far fa-calendar-plus"></i>موعد جديد</a>
            <a href="/librarysystem/admin/index.php" style="--c:#5b4bb7"><i class="fas fa-book-medical"></i>إضافة كتاب</a>
          </div></div>
      </div>
      <div class="cp-row">
        <div class="cp-box"><h3><i class="fas fa-user-graduate"></i> أحدث طلبات الالتحاق <a href="/student/admin/list.php">الكل ←</a></h3>
          <?php if (!$D['last_apps']): ?><div class="cp-empty">لا توجد طلبات بعد.</div><?php else: ?><ul class="cp-list"><?php foreach ($D['last_apps'] as $a): ?><li><span class="av"><?= $h(mb_substr($a['fname'], 0, 1)) ?></span><span><b><?= $h($a['fname'] . ' ' . $a['lname']) ?></b><small><?= $h(mb_strimwidth($a['depart'], 0, 48, '…')) ?></small></span><span class="tg"><?= $h($a['stage']) ?></span></li><?php endforeach; ?></ul><?php endif; ?></div>
        <div class="cp-box"><h3><i class="fas fa-briefcase"></i> أحدث طلبات التوظيف <a href="/admin/jobs.php">الكل ←</a></h3>
          <?php if (!$D['last_jobs']): ?><div class="cp-empty">لا توجد طلبات توظيف بعد. <a href="/jobs" target="_blank">صفحة الوظائف</a></div><?php else: $JS = uwciv_job_status(); ?><ul class="cp-list"><?php foreach ($D['last_jobs'] as $a): $s = $JS[$a['status']]; ?><li><span class="av" style="--c:<?= $s[2] ?>"><?= $h(mb_substr($a['name'], 0, 1)) ?></span><span><b><?= $h($a['name']) ?></b><small><?= $h($a['spec']) ?></small></span><span class="tg" style="color:<?= $s[2] ?>"><?= $s[0] ?></span></li><?php endforeach; ?></ul><?php endif; ?></div>
      </div>
    </section>
<?php else: $roles = uwciv_staff_roles(); $rl = $roles[$ME['role'] ?? 'custom'] ?? $roles['custom']; ?>
    <section class="cp-pane" id="p-overview" data-t="لوحتي" data-k="overview">
      <div class="st-hero"><div><small><i class="<?= $rl[1] ?>"></i> <?= $h($rl[0]) ?></small><h2><?= $greet ?>، <?= $h($ME['name'] ?? '') ?></h2><p>هذه اللوحات المسندة إليك وفق صلاحياتك. لأي صلاحية إضافية تواصل مع المشرف العام.</p></div>
        <div class="st-clock"><b id="stc">--:--</b><span>بتوقيت مكة المكرمة</span></div></div>
      <div class="cp-quick" style="grid-template-columns:repeat(auto-fill,minmax(170px,1fr))">
        <?php foreach (uwciv_admin_nav() as $k => $n): if ($k === 'overview' || !uwciv_can($k)) continue; ?>
          <a href="<?= $n[3] ?>" style="--c:#1f7a45;background:#fff;box-shadow:var(--sh-1)"<?= $n[4] ? ' data-go="' . substr($n[3], strpos($n[3], '#') + 1) . '"' : '' ?>><i class="<?= $n[1] ?>"></i><?= $n[2] ?></a>
        <?php endforeach; ?>
        <a href="#mypass" data-go="mypass" style="--c:#9b1f45;background:#fff;box-shadow:var(--sh-1)"><i class="fas fa-key"></i>كلمة مروري</a>
      </div>
    </section>
    <form method="post" class="cp-pane" id="p-mypass" data-t="كلمة مروري"><input type="hidden" name="csrf" value="<?= $csrf ?>">
      <div class="cp-card"><h2><i class="fas fa-key"></i> تغيير كلمة مروري</h2><p>اسم الدخول: <b dir="ltr"><?= $h($ME['username'] ?? '') ?></b></p>
        <div class="cp-f">
          <label class="w">الكلمة الحالية<div class="cp-in"><i class="fas fa-lock"></i><input type="password" name="cp" required dir="ltr" autocomplete="current-password"><button type="button" class="eye"><i class="far fa-eye"></i></button></div></label>
          <label>الكلمة الجديدة<div class="cp-in"><i class="fas fa-key"></i><input type="password" name="np" required minlength="8" dir="ltr" autocomplete="new-password"></div></label>
          <label>تأكيدها<div class="cp-in"><i class="fas fa-redo"></i><input type="password" name="np2" required minlength="8" dir="ltr" autocomplete="new-password"></div></label>
        </div><div class="cp-btns"><button class="cp-b p" name="save" value="mypass"><i class="fas fa-save"></i> حفظ</button></div></div>
    </form>
<?php endif; ?>

    <!-- الدعم والشكاوى -->
<?php if (uwciv_can('tickets')): ?>
    <section class="cp-pane" id="p-tickets" data-t="الدعم والشكاوى">
      <div class="cp-card"><h2><i class="fas fa-inbox"></i> رسائل الدعم الفني والشكاوى</h2><p>كل ما يصل من نموذجي «الدعم الفني» و«طلب أو شكوى لرئيس الجامعة». تصلك نسخة على البريد أيضًا.</p>
        <?php if (!$D['tickets']): ?><div class="cp-empty"><i class="fas fa-inbox" style="font-size:30px;color:#bfd8c8"></i><br>لا توجد رسائل بعد.</div><?php endif; ?>
        <?php foreach ($D['tickets'] as $t): $open = ($t['status'] ?? 'open') === 'open'; ?>
        <details class="cp-tk<?= $open ? '' : ' closed' ?>"><summary><span><b dir="ltr"><?= $h($t['ref']) ?></b><small><?= date('Y/m/d H:i', strtotime($t['created'])) ?></small></span><span><b><?= $h($t['name']) ?></b><small dir="ltr"><?= $h($t['email']) ?></small></span><span><b><?= $h($t['subject']) ?></b><small><?= $t['kind'] === 'support' ? 'دعم فني' : 'لرئيس الجامعة' ?><?= ($t['prio'] ?? '') === 'high' ? ' · <span style="color:#c0392b">عاجل</span>' : '' ?></small></span><span class="cp-pill <?= $open ? 'n' : 'y' ?>"><?= $open ? 'مفتوحة' : 'مغلقة' ?></span></summary>
          <div class="body"><div class="msg"><?= nl2br($h($t['message'])) ?></div>
            <form method="post" class="cp-btns" style="margin:0"><input type="hidden" name="csrf" value="<?= $csrf ?>"><input type="hidden" name="ref" value="<?= $h($t['ref']) ?>">
              <a class="cp-b p" href="mailto:<?= $h($t['email']) ?>?subject=<?= rawurlencode('رد: ' . $t['subject'] . ' (' . $t['ref'] . ')') ?>"><i class="fas fa-reply"></i> الرد بالبريد</a>
              <?php if (!empty($t['phone'])): ?><a class="cp-b g" href="https://wa.me/<?= preg_replace('/\D/', '', $t['phone']) ?>" target="_blank"><i class="fab fa-whatsapp"></i> واتساب</a><?php endif; ?>
              <button class="cp-b g" name="save" value="ticket"><i class="fas <?= $open ? 'fa-check' : 'fa-undo' ?>"></i> <?= $open ? 'تمت المعالجة' : 'إعادة فتح' ?></button></form></div></details>
        <?php endforeach; ?>
      </div>
    </section>
<?php endif; ?>

    <!-- التواصل -->
<?php if (uwciv_can('contact')): ?>
    <form method="post" class="cp-pane" id="p-contact" data-t="التواصل والموقع"><input type="hidden" name="csrf" value="<?= $csrf ?>">
      <div class="cp-card"><h2><i class="fas fa-map-marker-alt"></i> رقم التواصل وموقع الجامعة</h2><p>يظهر الرقم في الشريط العلوي والتذييل وصفحة «تواصل معنا» (اتصال وواتساب)، وتظهر الخريطة في أسفل كل صفحة.</p>
        <div class="cp-f">
          <label>رقم التواصل (واتساب)<div class="cp-in"><i class="fab fa-whatsapp"></i><input name="phone" dir="ltr" value="<?= $h($C['phone']) ?>" placeholder="+8801703597625"></div></label>
          <label>العنوان النصي<div class="cp-in"><i class="fas fa-city"></i><input name="address" value="<?= $h($S['address'] ?? '') ?>" placeholder="ساجيرا، محافظة ليباك، بانتن، إندونيسيا"></div></label>
          <label class="w">رابط الموقع على خرائط Google<div class="cp-in"><i class="fas fa-map-pin"></i><input name="location" dir="ltr" value="<?= $h($S['location'] ?? '') ?>" placeholder="https://maps.app.goo.gl/…"></div></label>
        </div>
        <div class="cp-map"><iframe src="<?= $h($C['embed']) ?>" loading="lazy" title="معاينة الخريطة"></iframe></div>
        <div class="cp-btns"><button class="cp-b p" name="save" value="contact"><i class="fas fa-save"></i> حفظ</button><a class="cp-b g" href="/contact" target="_blank"><i class="fas fa-eye"></i> معاينة صفحة التواصل</a></div></div>
    </form>
<?php endif; ?>

    <!-- التواصل الاجتماعي -->
<?php if (uwciv_can('social')): ?>
    <form method="post" class="cp-pane" id="p-social" data-t="التواصل الاجتماعي"><input type="hidden" name="csrf" value="<?= $csrf ?>">
      <div class="cp-card"><h2><i class="fas fa-share-alt"></i> روابط التواصل الاجتماعي</h2><p>تظهر الأيقونات في تذييل كل الصفحات وصفحة التواصل. اترك الخانة فارغة لإخفاء الأيقونة.</p>
        <?php $SC = ['youtube' => '#ff0000', 'x' => '#111', 'facebook' => '#1877f2', 'instagram' => '#e1306c', 'linkedin' => '#0a66c2', 'whatsapp' => '#25d366', 'telegram' => '#229ed9', 'tiktok' => '#111', 'snapchat' => '#e6c200']; ?>
        <div class="cp-soc"><?php foreach (uwciv_social_defs() as $k => $d): ?><label style="--c:<?= $SC[$k] ?? '#1f7a45' ?>"><?= $d[0] ?><div class="cp-in"><i class="<?= $d[1] ?>"></i><input name="social[<?= $k ?>]" dir="ltr" value="<?= $h($S['social'][$k] ?? '') ?>" placeholder="https://" data-ic="<?= $d[1] ?>"></div></label><?php endforeach; ?></div>
        <div class="cp-soc-prev" id="socPrev"></div>
        <div class="cp-btns"><button class="cp-b p" name="save" value="social"><i class="fas fa-save"></i> حفظ الروابط</button></div></div>
    </form>
<?php endif; ?>

    <!-- البريد -->
<?php if (uwciv_can('mail')): ?>
    <section class="cp-pane" id="p-mail" data-t="بريد الموقع">
      <form method="post" class="cp-card"><input type="hidden" name="csrf" value="<?= $csrf ?>">
        <h2><i class="far fa-envelope"></i> بريد الموقع — <?= UWCIV_SMTP_USER ?></h2>
        <p>تصل إليه رسائل التسجيل والتوظيف والدعم والشكاوى. الحالة:
          <?= !empty($S['smtp_pass']) ? '<span class="cp-pill y"><i class="fas fa-check"></i> عبر Gmail مباشرة</span>' : '<span class="cp-pill n"><i class="fas fa-server"></i> عبر بريد الاستضافة (قد يصل إلى Spam)</span>' ?></p>
        <ol class="cp-steps"><li>ادخل بحساب <b>info@biuedu.com</b> وفعّل <a href="https://myaccount.google.com/signinoptions/two-step-verification" target="_blank" rel="noopener">التحقق بخطوتين</a>.</li><li>افتح <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">كلمات مرور التطبيقات</a> واكتب «موقع الجامعة» ثم إنشاء.</li><li>انسخ الـ 16 حرفًا والصقها هنا واحفظ.</li></ol>
        <div class="cp-f"><label class="w">كلمة مرور التطبيق<div class="cp-in"><i class="fas fa-key"></i><input type="password" name="smtp_pass" placeholder="xxxx xxxx xxxx xxxx" autocomplete="off" dir="ltr"></div></label></div>
        <div class="cp-btns"><button class="cp-b p" name="save" value="mail"><i class="fas fa-save"></i> حفظ</button><button class="cp-b g" name="save" value="testmail" formnovalidate><i class="fas fa-paper-plane"></i> إرسال رسالة اختبار</button><?php if (!empty($S['smtp_pass'])): ?><button class="cp-b r" name="save" value="mailclear" formnovalidate><i class="fas fa-trash"></i> حذف الكلمة</button><?php endif; ?></div>
      </form>
    </section>
<?php endif; ?>

    <!-- الأمان -->
<?php if ($SUPER): ?>
    <form method="post" class="cp-pane" id="p-password" data-t="الأمان وكلمة المرور"><input type="hidden" name="csrf" value="<?= $csrf ?>">
      <div class="cp-card"><h2><i class="fas fa-shield-alt"></i> كلمة مرور المشرف العام</h2><p>تُستخدم مع «المشرف العام» و admin و benmakkh@gmail.com في كل لوحات التحكم، وتُحفظ مشفّرة ولا يراها أحد.</p>
        <div class="cp-f">
          <label class="w">الكلمة الحالية<div class="cp-in"><i class="fas fa-lock"></i><input type="password" name="cp" required autocomplete="current-password" dir="ltr"><button type="button" class="eye" aria-label="إظهار"><i class="far fa-eye"></i></button></div></label>
          <label>الكلمة الجديدة (10 أحرف على الأقل)<div class="cp-in"><i class="fas fa-key"></i><input type="password" name="np" id="np" required minlength="10" autocomplete="new-password" dir="ltr"><button type="button" class="eye" aria-label="إظهار"><i class="far fa-eye"></i></button></div><div class="cp-meter"><i id="npm"></i></div><span class="cp-meter-t" id="npt">اكتب كلمة قوية: حروف كبيرة وصغيرة وأرقام ورموز</span></label>
          <label>تأكيد الكلمة الجديدة<div class="cp-in"><i class="fas fa-redo"></i><input type="password" name="np2" id="np2" required minlength="10" autocomplete="new-password" dir="ltr"></div><span class="cp-meter-t" id="np2t"></span></label>
        </div>
        <div class="cp-btns"><button class="cp-b p" name="save" value="password"><i class="fas fa-save"></i> تغيير كلمة المرور</button><button type="button" class="cp-b g" id="gen"><i class="fas fa-magic"></i> اقترح كلمة قوية</button></div></div>
      <div class="cp-card"><h2><i class="fas fa-user-lock"></i> نصائح الأمان</h2><ul class="cp-checks"><li><a class="y"><i class="fas fa-check"></i><b>لا تشارك كلمة المرور عبر الواتساب أو البريد</b></a></li><li><a class="y"><i class="fas fa-check"></i><b>سجّل الخروج من الأجهزة المشتركة</b></a></li><li><a class="y"><i class="fas fa-check"></i><b>غيّر الكلمة كل 3–6 أشهر</b></a></li></ul></div>
    </form>
<?php endif; ?>

    <!-- التقويم -->
<?php if (uwciv_can('ticker')): require_once dirname(__DIR__) . '/inc/ticker.php'; $TK = uwciv_ticker_data(); $TKI = ['fas fa-bullhorn', 'fas fa-user-plus', 'fas fa-graduation-cap', 'fas fa-laptop-code', 'far fa-newspaper', 'fas fa-briefcase', 'fas fa-book', 'fas fa-gavel', 'fas fa-certificate', 'fas fa-mobile-alt', 'far fa-calendar-alt', 'fas fa-mosque', 'fas fa-award', 'fas fa-star', 'fas fa-info-circle', 'fas fa-bell']; ?>
    <form method="post" class="cp-pane" id="p-ticker" data-t="شريط الأخبار"><input type="hidden" name="csrf" value="<?= $csrf ?>">
      <div class="cp-card"><h2><i class="fas fa-bullhorn"></i> شريط الأخبار المتحرك</h2><p>يظهر أسفل القائمة الرئيسية في كل صفحات الموقع ويتحرك باستمرار، ويتوقف عند مرور المؤشر عليه. رتّب الأخبار واكتبها كما تشاء.</p>
        <div class="cp-f three">
          <label>عنوان الشريط<div class="cp-in"><i class="fas fa-tag"></i><input name="tk_label" value="<?= $h($TK['label']) ?>" maxlength="30"></div></label>
          <label>السرعة (ثوانٍ لكل دورة — الأكبر أبطأ)<div class="cp-in"><i class="fas fa-tachometer-alt"></i><input type="range" name="tk_speed" min="15" max="200" value="<?= (int) $TK['speed'] ?>" oninput="this.nextElementSibling.textContent=this.value" style="height:48px"><b style="min-width:30px"><?= (int) $TK['speed'] ?></b></div></label>
          <div style="display:flex;flex-direction:column;gap:10px;justify-content:center"><label class="sw-l"><input type="checkbox" name="tk_on" value="1" <?= !empty($TK['on']) ? 'checked' : '' ?>> إظهار الشريط في الموقع</label><label class="sw-l"><input type="checkbox" name="tk_auto" value="1" <?= !empty($TK['auto']) ? 'checked' : '' ?>> إضافة آخر 3 أخبار من «الأخبار» تلقائيًا</label></div>
        </div>
        <div class="tk-prev"><span class="tkl"><i class="fas fa-bullhorn"></i> <span id="tkL"><?= $h($TK['label']) ?></span></span><div id="tkP"></div></div>
        <div id="tkRows"><?php foreach ($TK['items'] as $i => $x): ?>
          <div class="tk-r"><select name="tk[<?= $i ?>][i]"><?php foreach ($TKI as $ic): ?><option value="<?= $ic ?>" <?= ($x['i'] ?? '') === $ic ? 'selected' : '' ?>><?= $ic ?></option><?php endforeach; ?></select><span class="tki"><i class="<?= $h($x['i'] ?? 'fas fa-circle') ?>"></i></span><input name="tk[<?= $i ?>][t]" value="<?= $h($x['t']) ?>" placeholder="نص الخبر" maxlength="220"><input name="tk[<?= $i ?>][u]" value="<?= $h($x['u'] ?? '') ?>" placeholder="/رابط (اختياري)" dir="ltr"><label class="hot"><input type="checkbox" name="tk[<?= $i ?>][hot]" value="1" <?= !empty($x['hot']) ? 'checked' : '' ?>> عاجل</label><span class="bt"><button type="button" data-u><i class="fas fa-arrow-up"></i></button><button type="button" class="rm" data-r><i class="fas fa-trash"></i></button></span></div>
        <?php endforeach; ?></div>
        <div class="cp-btns"><button type="button" class="cp-b g" id="tkAdd"><i class="fas fa-plus"></i> خبر جديد</button><span style="flex:1"></span><a class="cp-b g" href="/index.php" target="_blank"><i class="fas fa-eye"></i> معاينة في الموقع</a><button class="cp-b p" name="save" value="ticker"><i class="fas fa-save"></i> حفظ ونشر</button></div>
      </div>
    </form>
<?php endif; ?>
<?php if (uwciv_can('calendar')): ?>
    <form method="post" class="cp-pane" id="p-calendar" data-t="التقويم الرسمي"><input type="hidden" name="csrf" value="<?= $csrf ?>">
      <div class="cp-card"><h2><i class="far fa-calendar-alt"></i> التقويم الدراسي الرسمي</h2><p>عدّل المواعيد ثم احفظ، فتُنشر فورًا في <a href="/calendar" target="_blank">صفحة التقويم</a> وتختفي عبارة «معتمدة مبدئيًا». <?= !empty($S['calendar']) ? '<span class="cp-pill y"><i class="fas fa-check"></i> معتمد</span>' : '<span class="cp-pill n">مبدئي</span>' ?></p>
        <div class="cp-f" style="margin-bottom:14px"><label>العام الجامعي<div class="cp-in"><i class="fas fa-graduation-cap"></i><input name="year" value="<?= $h($CALYEAR) ?>"></div></label></div>
        <div id="terms"><?php foreach ($CAL as $ti => $tm): ?>
          <div class="cp-term" data-t="<?= $ti ?>"><input name="term[<?= $ti ?>]" value="<?= $h($tm[0]) ?>">
            <div class="cp-hd"><span>النوع</span><span>الحدث</span><span>من</span><span>إلى (اختياري)</span><span>ملاحظة</span><span></span></div>
            <div class="evs"><?php foreach ($tm[1] as $ei => $e): ?>
              <div class="cp-ev"><select name="ev[<?= $ti ?>][<?= $ei ?>][type]"><?php foreach ($TYPES as $tk => $tl): ?><option value="<?= $tk ?>"<?= $e[0] === $tk ? ' selected' : '' ?>><?= $tl ?></option><?php endforeach; ?></select>
                <input name="ev[<?= $ti ?>][<?= $ei ?>][title]" value="<?= $h($e[1]) ?>"><input type="date" name="ev[<?= $ti ?>][<?= $ei ?>][start]" value="<?= $h($e[2]) ?>"><input type="date" name="ev[<?= $ti ?>][<?= $ei ?>][end]" value="<?= $h((string) $e[3]) ?>"><input name="ev[<?= $ti ?>][<?= $ei ?>][note]" value="<?= $h((string) ($e[4] ?? '')) ?>"><button type="button" class="cp-del" title="حذف">✕</button></div>
            <?php endforeach; ?></div>
            <button type="button" class="cp-add">＋ إضافة حدث</button></div>
        <?php endforeach; ?></div>
        <div class="cp-btns"><button class="cp-b p" name="save" value="calendar"><i class="fas fa-save"></i> حفظ ونشر التقويم</button><a class="cp-b g" href="/calendar" target="_blank"><i class="fas fa-eye"></i> معاينة</a></div></div>
    </form>
<?php endif; ?>
<script>
(function(){
  var panes=document.querySelectorAll('.cp-pane'),links=document.querySelectorAll('.ad-side a.n[data-p]'),cp=document.getElementById('ad'),keys=['overview','tickets','calendar','contact','social','mail','password'];
  function show(p){if(!document.getElementById('p-'+p))p='overview';panes.forEach(function(x){x.classList.toggle('on',x.id==='p-'+p)});links.forEach(function(a){a.classList.toggle('on',a.dataset.p===p);if(a.dataset.p===p&&a.closest('.ad-g'))a.closest('.ad-g').classList.add('open');});var t=document.getElementById('p-'+p).dataset.t;document.getElementById('adtitle').textContent=t;document.getElementById('adcrumb').textContent=t;document.title=t+' | لوحة التحكم';cp.classList.remove('open');window.scrollTo(0,0);}
  window.addEventListener('hashchange',function(){show(location.hash.slice(1))});
  links.forEach(function(a){a.addEventListener('click',function(e){e.preventDefault();if(location.hash==='#'+a.dataset.p)show(a.dataset.p);else location.hash=a.dataset.p;})});
  function clk(){try{document.getElementById('stc').textContent=new Intl.DateTimeFormat('en-GB',{hour:'2-digit',minute:'2-digit',timeZone:'Asia/Riyadh'}).format(new Date());}catch(e){}}clk();setInterval(clk,20000);
  document.querySelectorAll('[data-go]').forEach(function(a){a.addEventListener('click',function(e){e.preventDefault();location.hash=a.dataset.go;})});
  show(location.hash.slice(1)||'overview');
  try{document.getElementById('cpd').textContent=new Intl.DateTimeFormat('ar',{weekday:'long',day:'numeric',month:'long',year:'numeric'}).format(new Date())+' · '+new Intl.DateTimeFormat('ar-SA-u-ca-islamic-umalqura',{day:'numeric',month:'long',year:'numeric'}).format(new Date());}catch(e){}
  var np=document.getElementById('np'),np2=document.getElementById('np2'),m=document.getElementById('npm'),mt=document.getElementById('npt'),t2=document.getElementById('np2t');
  function meter(){var v=np.value,s=0;if(v.length>=10)s++;if(v.length>=14)s++;if(/[A-Z]/.test(v)&&/[a-z]/.test(v))s++;if(/\d/.test(v))s++;if(/[^A-Za-z0-9]/.test(v))s++;var L=[['#c0392b','ضعيفة جدًا'],['#c0392b','ضعيفة'],['#b7791f','مقبولة'],['#2c5fd0','جيدة'],['#1f7a45','قوية'],['#1f7a45','قوية جدًا']][s];m.style.width=(s/5*100)+'%';m.style.background=L[0];mt.textContent=v?'القوة: '+L[1]:'اكتب كلمة قوية: حروف كبيرة وصغيرة وأرقام ورموز';mt.style.color=v?L[0]:'';match();}
  function match(){if(!np2.value){t2.textContent='';return;}var ok=np.value===np2.value;t2.textContent=ok?'✓ متطابقتان':'✗ غير متطابقتين';t2.style.color=ok?'#1f7a45':'#c0392b';}
  if(np){np.addEventListener('input',meter);np2.addEventListener('input',match);
    document.getElementById('gen').onclick=function(){var c='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789',p='Uwc-',r=new Uint32Array(14);crypto.getRandomValues(r);for(var i=0;i<14;i++){p+=c[r[i]%c.length];if(i===6)p+='-';}np.value=np2.value=p;np.type=np2.type='text';meter();};}
  var prev=document.getElementById('socPrev');function soc(){if(!prev)return;var h='';document.querySelectorAll('[data-ic]').forEach(function(i){if(i.value.trim())h+='<span><i class="'+i.dataset.ic+'"></i></span>';});prev.innerHTML=h||'<em>معاينة التذييل: لا توجد روابط بعد</em>';}
  document.querySelectorAll('[data-ic]').forEach(function(i){i.addEventListener('input',soc)});soc();
  document.addEventListener('click',function(e){
    if(e.target.classList.contains('cp-del')){e.target.closest('.cp-ev').remove();}
    if(e.target.classList.contains('cp-add')){var term=e.target.closest('.cp-term'),tt=term.dataset.t,box=term.querySelector('.evs'),i='n'+Date.now(),opts=<?= json_encode($TYPES, JSON_UNESCAPED_UNICODE) ?>,sel='';for(var k in opts)sel+='<option value="'+k+'">'+opts[k]+'</option>';
      var row=document.createElement('div');row.className='cp-ev';row.innerHTML='<select name="ev['+tt+']['+i+'][type]">'+sel+'</select><input name="ev['+tt+']['+i+'][title]" placeholder="اسم الحدث"><input type="date" name="ev['+tt+']['+i+'][start]"><input type="date" name="ev['+tt+']['+i+'][end]"><input name="ev['+tt+']['+i+'][note]" placeholder="ملاحظة"><button type="button" class="cp-del" title="حذف">✕</button>';box.appendChild(row);row.querySelector('input').focus();}
  });
})();
</script>
<script>
(function(){var box=document.getElementById('tkRows');if(!box)return;var P=document.getElementById('tkP');
function ren(){[].forEach.call(box.children,function(r,i){r.querySelectorAll('[name]').forEach(function(x){x.name=x.name.replace(/tk\[\d+\]/,'tk['+i+']')});});prev();}
function prev(){P.textContent=[].map.call(box.querySelectorAll('input[name$="[t]"]'),function(x){return x.value}).filter(Boolean).join('  ۞  ');}
box.addEventListener('input',prev);box.addEventListener('change',function(e){if(e.target.tagName==='SELECT')e.target.nextElementSibling.innerHTML='<i class="'+e.target.value+'"></i>';});
box.addEventListener('click',function(e){var r=e.target.closest('.tk-r');if(!r)return;if(e.target.closest('[data-r]')&&box.children.length>1){r.remove();ren();}if(e.target.closest('[data-u]')&&r.previousElementSibling){r.previousElementSibling.before(r);ren();}});
document.getElementById('tkAdd').onclick=function(){var r=box.children[0].cloneNode(true);r.querySelectorAll('input').forEach(function(x){if(x.type==='checkbox')x.checked=false;else x.value='';});box.prepend(r);ren();r.querySelector('input[name$="[t]"]').focus();};
document.querySelector('[name=tk_label]').addEventListener('input',function(){document.getElementById('tkL').textContent=this.value;});prev();})();
</script>
<?php uwciv_admin_close(); ?>
