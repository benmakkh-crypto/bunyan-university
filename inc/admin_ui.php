<?php
/*
 * واجهة لوحات التحكم الموحّدة — جامعة بنيان العالمية
 * uwciv_admin_gate()  : يتحقق من الدخول ويعرض صفحة دخول موحّدة عند الحاجة
 * uwciv_admin_open()  : يفتح الهيكل (القائمة الجانبية + الشريط العلوي)
 * uwciv_admin_close() : يغلق الهيكل
 */
require_once dirname(__DIR__) . '/db_config.php';
require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/staff.php';
require_once __DIR__ . '/i18n.php';
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();

if (!function_exists('uwciv_h')) { function uwciv_h($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); } }

function uwciv_csrf()
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function uwciv_csrf_ok() { return hash_equals(uwciv_csrf(), (string) ($_POST['csrf'] ?? '')); }
function uwciv_csrf_field() { return '<input type="hidden" name="csrf" value="' . uwciv_csrf() . '">'; }

/* القائمة: المفتاح => [المجموعة، الأيقونة، العنوان، الرابط، داخل صفحة الإعدادات؟] */
function uwciv_admin_nav()
{
    return [
        'overview'     => ['الرئيسية', 'fas fa-th-large', 'نظرة عامة', '/admin/settings.php#overview', 1],
        'applications' => ['الطلبات والرسائل', 'fas fa-user-graduate', 'طلبات الالتحاق', '/student/admin/list.php', 0],
        'jobs'         => ['الطلبات والرسائل', 'fas fa-briefcase', 'التوظيف', '/admin/jobs.php', 0],
        'tickets'      => ['الطلبات والرسائل', 'fas fa-inbox', 'الدعم والشكاوى', '/admin/settings.php#tickets', 1],
        'news'         => ['المحتوى', 'far fa-newspaper', 'الأخبار', '/blog/admin/index.php', 0],
        'events'       => ['المحتوى', 'fas fa-award', 'أخبار الخريجين', '/blgrad/admin/index.php', 0],
        'members'      => ['المحتوى', 'fas fa-users', 'أعضاء الجامعة', '/admin/members.php', 0],
        'regs'         => ['المحتوى', 'fas fa-gavel', 'اللوائح والصلاحيات', '/admin/regs.php', 0],
        'calendar'     => ['المحتوى', 'far fa-calendar-alt', 'التقويم الرسمي', '/admin/settings.php#calendar', 1],
        'ticker'       => ['المحتوى', 'fas fa-bullhorn', 'شريط الأخبار', '/admin/settings.php#ticker', 1],
        'menu'         => ['المحتوى', 'fas fa-stream', 'القائمة الرئيسية للموقع', '/admin/menu.php', 0],
        'import'       => ['المحتوى', 'fas fa-cloud-download-alt', 'استيراد محتوى بنيان', '/admin/import-biu.php', 0],
        'pages'        => ['صفحات المعلومات', 'fas fa-file-alt', 'كل الصفحات', '/admin/pages.php', 0],
        'colleges'     => ['الكليات', 'fas fa-university', 'كل الكليات', '/admin/college.php', 0],
        'library'      => ['الخدمات الأكاديمية', 'fas fa-book', 'المكتبة الرقمية', '/librarysystem/admin/index.php', 0],
        'grad'         => ['الخدمات الأكاديمية', 'fas fa-certificate', 'وثائق التخرج', '/pdf_grad/admin/index.php', 0],
        'exams'        => ['الخدمات الأكاديمية', 'fas fa-laptop-code', 'الامتحانات الإلكترونية', '/onlineexam/dash.php', 0],
        'staff'        => ['النظام', 'fas fa-user-shield', 'الموظفون والصلاحيات', '/admin/staff.php', 0],
        'accounts'     => ['النظام', 'fas fa-user-lock', 'حسابات البوابة', '/syslog/admin/index.php', 0],
        'contact'      => ['النظام', 'fas fa-map-marker-alt', 'التواصل والموقع', '/admin/settings.php#contact', 1],
        'social'       => ['النظام', 'fas fa-share-alt', 'التواصل الاجتماعي', '/admin/settings.php#social', 1],
        'mail'         => ['النظام', 'far fa-envelope', 'بريد الموقع', '/admin/settings.php#mail', 1],
        'password'     => ['النظام', 'fas fa-shield-alt', 'الأمان وكلمة المرور', '/admin/settings.php#password', 1],
    ];
}
function uwciv_admin_groups()
{
    return ['الرئيسية' => 'fas fa-home', 'الطلبات والرسائل' => 'fas fa-inbox', 'المحتوى' => 'fas fa-pen-nib', 'صفحات المعلومات' => 'fas fa-book-open', 'الكليات' => 'fas fa-university', 'الخدمات الأكاديمية' => 'fas fa-graduation-cap', 'النظام' => 'fas fa-cogs'];
}
/* مفتاح اللوحة من مسار الصفحة الحالية */
function uwciv_admin_key_here()
{
    $path = $_SERVER['SCRIPT_NAME'] ?? '';
    foreach (uwciv_admin_nav() as $k => $n) { $u = strtok($n[3], '#'); if ($u === $path) return $k; }
    return '';
}

function uwciv_admin_badges()
{
    static $b = null; if ($b !== null) return $b;
    $b = [];
    $jf = dirname(__DIR__) . '/_private/jobs/applications.json';
    if (is_file($jf)) { $J = json_decode((string) file_get_contents($jf), true) ?: []; $n = 0; foreach ($J as $a) if (($a['status'] ?? '') === 'new') $n++; if ($n) $b['jobs'] = $n; }
    $tf = dirname(__DIR__) . '/_private/tickets.json';
    if (is_file($tf)) { $T = json_decode((string) file_get_contents($tf), true) ?: []; $n = 0; foreach ($T as $t) if (($t['status'] ?? 'open') === 'open') $n++; if ($n) $b['tickets'] = $n; }
    return $b;
}

/* صلاحية محدودة لقسم واحد (مثل أمناء المكتبة) */
function uwciv_admin_scope($scope = null)
{
    if ($scope === null) return '';
    return (string) ($_SESSION['uwciv_scope'][$scope] ?? '');
}

/*
 * $opt = ['scope' => 'library', 'check' => function($u,$p){ return 'الاسم' أو null; }]
 */
function uwciv_admin_gate($opt = [])
{
    $scope = $opt['scope'] ?? null;
    $key = $opt['perm'] ?? uwciv_admin_key_here();
    if (uwciv_is_super() || ($scope && uwciv_admin_scope($scope) !== '')) return;
    if (uwciv_staff_me()) {
        if ($key === '' || uwciv_can($key)) return;
        $navT = uwciv_admin_nav(); $opt['title'] = $opt['title'] ?? ($navT[$key][2] ?? 'هذه اللوحة');
        uwciv_staff_log('محاولة دخول بلا صلاحية: ' . $opt['title']);
        uwciv_admin_open('overview', 'لا تملك صلاحية', 'هذه اللوحة غير مسندة إلى حسابك.');
        echo '<div class="cp-card"><div class="cp-empty"><i class="fas fa-lock"></i><b style="color:var(--ink);font-size:18px">ليست لديك صلاحية «' . uwciv_h($opt['title'] ?? 'هذه اللوحة') . '»</b><br>إن كانت من مهامك فاطلب من المشرف العام إسنادها إليك من «الموظفون والصلاحيات».<br><a class="cp-b p" style="margin-top:14px" href="/admin/settings.php"><i class="fas fa-home"></i> العودة إلى لوحتي</a></div></div>';
        uwciv_admin_close(); exit;
    }
    $err = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uwciv_login'])) {
        $now = time(); $_SESSION['lg_try'] = array_values(array_filter($_SESSION['lg_try'] ?? [], function ($t) use ($now) { return $t > $now - 900; }));
        if (count($_SESSION['lg_try']) >= 8) $err = 'محاولات كثيرة. انتظر 15 دقيقة ثم أعد المحاولة.';
        elseif (!uwciv_csrf_ok()) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
        elseif (trim($_POST['website'] ?? '') !== '') $err = 'تعذّر الدخول.';
        else {
            $u = trim((string) ($_POST['u'] ?? '')); $p = (string) ($_POST['p'] ?? '');
            if (uwciv_admin_login($u, $p)) { session_regenerate_id(true); header('Location: ' . strtok($_SERVER['REQUEST_URI'], '#')); exit; }
            if (uwciv_staff_login($u, $p)) { $sid = $_SESSION['uwciv_staff']; session_regenerate_id(true); $_SESSION['uwciv_staff'] = $sid; header('Location: ' . strtok($_SERVER['REQUEST_URI'], '#')); exit; }
            if ($scope && isset($opt['check']) && is_callable($opt['check'])) {
                $name = $opt['check']($u, $p);
                if ($name) { session_regenerate_id(true); $_SESSION['uwciv_scope'][$scope] = $name; header('Location: ' . strtok($_SERVER['REQUEST_URI'], '#')); exit; }
            }
            $_SESSION['lg_try'][] = $now;
            $err = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
        }
    }
    $nav = uwciv_admin_nav();
    $here = $opt['title'] ?? 'لوحة التحكم';
    uwciv_admin_login_page($here, $err, $scope ? ($opt['hint'] ?? '') : '');
    exit;
}

function uwciv_admin_head($title)
{
    ?><!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow"><meta name="theme-color" content="#0b3119">
<title><?= uwciv_h($title) ?> | لوحة تحكم جامعة بنيان العالمية</title>
<link rel="icon" type="image/png" sizes="48x48" href="/assets/app/favicon-48.png"><link rel="shortcut icon" href="/favicon.ico">
<link rel="stylesheet" href="/assets/css/plugins/all.min.css">
<link rel="stylesheet" href="/assets/css/uwciv-theme.css?v=22">
<link rel="stylesheet" href="/assets/css/uwciv-admin.css?v=8">
</head>
<?php
}

function uwciv_admin_login_page($title, $err, $hint)
{
    uwciv_admin_head('تسجيل الدخول');
    $items = [['fas fa-user-graduate', 'طلبات الالتحاق والتوظيف'], ['far fa-newspaper', 'الأخبار والفعاليات'], ['fas fa-book', 'المكتبة ووثائق التخرج'], ['fas fa-laptop-code', 'الامتحانات الإلكترونية'], ['fas fa-gavel', 'اللوائح والصلاحيات']];
    ?>
<body class="ad-auth">
<div class="au">
  <section class="au-brand">
    <div class="au-orb a"></div><div class="au-orb b"></div>
    <a href="/index.php" class="au-logo"><img src="/assets/images/na-light.png" alt="جامعة بنيان العالمية"></a>
    <div class="au-copy">
      <small><i class="fas fa-shield-alt"></i> منطقة إدارية آمنة</small>
      <h1>مركز إدارة<br><span>جامعة بنيان العالمية</span></h1>
      <p>لوحة واحدة تدير منها كل شيء في الجامعة — دخول واحد يفتح لك كل اللوحات.</p>
      <ul><?php foreach ($items as $i => $it): ?><li style="--d:<?= $i * .08 ?>s"><i class="<?= $it[0] ?>"></i><?= $it[1] ?></li><?php endforeach; ?></ul>
    </div>
    <div class="au-foot"><span><i class="fas fa-lock"></i> اتصال مشفّر</span><span><i class="fas fa-history"></i> حماية من التخمين</span></div>
  </section>
  <section class="au-form">
    <form method="post" autocomplete="on" novalidate>
      <?= uwciv_csrf_field() ?><input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;width:1px;height:1px;opacity:0;pointer-events:none;overflow:hidden" aria-hidden="true">
      <div class="au-badge"><i class="fas fa-user-shield"></i></div>
      <h2>مرحبًا بعودتك</h2>
      <p class="au-sub">سجّل الدخول للمتابعة إلى <b><?= uwciv_h($title) ?></b></p>
      <?php if ($err): ?><div class="au-err"><i class="fas fa-exclamation-circle"></i> <?= uwciv_h($err) ?></div><?php endif; ?>
      <label class="au-f">اسم المستخدم أو البريد<div class="ad-in"><i class="far fa-user"></i><input name="u" required autocomplete="username" placeholder="المشرف العام" value="<?= uwciv_h($_POST['u'] ?? '') ?>"></div></label>
      <label class="au-f">كلمة المرور<div class="ad-in"><i class="fas fa-lock"></i><input type="password" name="p" id="aup" required autocomplete="current-password" placeholder="••••••••" dir="ltr"><button type="button" class="ad-eye" aria-label="إظهار كلمة المرور"><i class="far fa-eye"></i></button></div></label>
      <div class="au-caps" id="aucaps"><i class="fas fa-arrow-up"></i> زر الأحرف الكبيرة (Caps Lock) مفعّل</div>
      <button class="ad-b p au-go" name="uwciv_login" value="1"><span>دخول</span><i class="fas fa-arrow-left"></i></button>
      <?php if ($hint): ?><p class="au-hint"><?= uwciv_h($hint) ?></p><?php endif; ?>
      <div class="au-links"><a href="/index.php"><i class="fas fa-globe"></i> الموقع</a><a href="/syslog/index.php"><i class="fas fa-user"></i> بوابة الطلاب</a><a href="/form1.php"><i class="fas fa-life-ring"></i> الدعم الفني</a></div>
    </form>
  </section>
</div>
<script src="/assets/js/uwciv-admin.js?v=6"></script>
</body></html>
<?php
}

function uwciv_admin_open($key, $title, $sub = '', $actions = '')
{
    $GLOBALS['UWCIV_ADMIN_KEY'] = $key;
    $nav = uwciv_admin_nav(); $badges = uwciv_admin_badges();
    $super = uwciv_is_super();
    $onSettings = basename($_SERVER['SCRIPT_NAME']) === 'settings.php' && strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false;
    uwciv_admin_head($title);
    $hour = (int) gmdate('G') + 3; $greet = ($hour % 24) < 12 ? 'صباح الخير' : 'مساء الخير';
    $sm = uwciv_staff_me(); $roles = uwciv_staff_roles();
    $who = $super ? 'المشرف العام' : ($sm ? $sm['name'] : (implode('، ', array_filter($_SESSION['uwciv_scope'] ?? [])) ?: 'مستخدم'));
    ?>
<body class="ad-body">
<div class="ad" id="ad">
  <aside class="ad-side" aria-label="القائمة">
    <a class="ad-brand" href="/index.php" title="الذهاب إلى موقع الجامعة"><img src="/assets/images/na-light.png" alt="جامعة بنيان العالمية"><small>CONTROL CENTER <i class="fas fa-external-link-alt"></i></small></a>
    <button class="ad-find" type="button" data-palette><i class="fas fa-search"></i> انتقال سريع… <kbd>Ctrl K</kbd></button>
    <nav>
    <?php $G = uwciv_admin_groups(); $byG = [];
      foreach ($nav as $k => $n) if (uwciv_can($k)) $byG[$n[0]][$k] = $n;
      foreach ($byG as $g => $items):
        $openG = isset($items[$key]) || $g === 'الرئيسية'; $cnt = 0; foreach ($items as $k => $n) $cnt += (int) ($badges[$k] ?? 0); ?>
      <div class="ad-g<?= $openG ? ' open' : '' ?>" data-g="<?= md5($g) ?>">
        <button type="button" class="ad-gh"><i class="<?= $G[$g] ?? 'fas fa-folder' ?>"></i><b><?= $g ?></b><?= $cnt ? '<em>' . $cnt . '</em>' : '<small>' . ($g === 'الكليات' ? (count($GLOBALS['UWCIV_COLLEGES'] ?? []) ?: 4) : ($g === 'صفحات المعلومات' ? array_sum(array_map(function ($x) { return count($x[1]); }, uwciv_info_page_groups())) : count($items))) . '</small>' ?><i class="fas fa-chevron-down ad-gc"></i></button>
        <div class="ad-gl">
        <?php foreach ($items as $k => $n):
          $attr = ($onSettings && $n[4]) ? ' data-p="' . substr($n[3], strpos($n[3], '#') + 1) . '"' : ''; ?>
          <a class="n<?= $k === $key ? ' on' : '' ?>" href="<?= $n[3] ?>"<?= $attr ?> data-k="<?= $k ?>"><i class="<?= $n[1] ?>"></i><span><?= $n[2] ?></span><?= !empty($badges[$k]) ? '<em>' . $badges[$k] . '</em>' : '' ?></a>
        <?php endforeach; ?>
        <?php if ($g === 'الكليات') echo uwciv_admin_college_tree(); if ($g === 'صفحات المعلومات') echo uwciv_admin_pages_tree(); ?>
        </div>
      </div>
    <?php endforeach; ?>
    </nav>
    <div class="ad-foot"><a href="/index.php" target="_blank" rel="noopener"><i class="fas fa-globe"></i> عرض الموقع</a><a class="out" href="/admin/logout.php"><i class="fas fa-sign-out-alt"></i> خروج</a></div>
  </aside>
  <div class="ad-scrim" data-close></div>
  <main class="ad-main">
    <header class="ad-top">
      <button class="ad-burger" type="button" id="adb" aria-label="القائمة"><i class="fas fa-bars"></i></button>
      <div class="ad-crumb"><a href="/admin/settings.php"><i class="fas fa-home"></i></a><i class="fas fa-chevron-left"></i><span id="adcrumb"><?= uwciv_h($title) ?></span></div>
      <span class="sp"></span>
      <button class="ad-icon" type="button" data-palette title="انتقال سريع (Ctrl+K)"><i class="fas fa-search"></i></button>
      <?php if ($super): ?><a class="ad-icon" href="/admin/settings.php#tickets" title="الرسائل"><i class="far fa-bell"></i><?= !empty($badges['tickets']) || !empty($badges['jobs']) ? '<b></b>' : '' ?></a><?php endif; ?>
      <div class="ad-me"><i class="fas fa-user-shield"></i><span><small><?= $greet ?></small><?= uwciv_h($who) ?></span></div>
    </header>
    <div class="ad-head">
      <div><h1 id="adtitle"><?= uwciv_h($title) ?></h1><?php if ($sub): ?><p><?= $sub ?></p><?php endif; ?></div>
      <?php if ($actions): ?><div class="ad-acts"><?= $actions ?></div><?php endif; ?>
    </div>
    <div class="ad-content">
<?php
}

/* صفحات المعلومات مجمّعة */
function uwciv_info_page_groups()
{
    return [
        'عن الجامعة' => ['fas fa-info-circle', ['about_biu', 'study_system', 'hq', 'agreements', 'org_chart', 'scholarships', 'accred', 'branches', 'calendar']],
        'الأقسام والدبلومات' => ['fas fa-sitemap', ['sharia_prog', 'personal_status', 'guidance', 'preachers_dip', 'introducers_dip', 'arabic_dept']],
        'إدارة الجامعة' => ['fas fa-landmark', ['council', 'president', 'vice', 'vicedep', 'scicouncil', 'promo', 'finance', 'adminaff']],
        'العمادات والوحدات' => ['fas fa-university', ['deanships', 'support_u', 'plans']],
        'المراكز العلمية' => ['fas fa-flask', ['c_train', 'c_lang', 'c_manus', 'c_dawah']],
        'السياسات' => ['fas fa-file-contract', ['privacy', 'terms', 'credits']],
    ];
}
function uwciv_info_page_icon($k)
{
    $I = ['council' => 'fas fa-landmark', 'president' => 'fas fa-user-tie', 'vice' => 'fas fa-user-friends', 'vicedep' => 'fas fa-user-cog', 'scicouncil' => 'fas fa-microscope', 'promo' => 'fas fa-bullhorn', 'finance' => 'fas fa-coins', 'adminaff' => 'fas fa-briefcase', 'deanships' => 'fas fa-university', 'support_u' => 'fas fa-hands-helping', 'plans' => 'fas fa-list-alt', 'c_train' => 'fas fa-chalkboard-teacher', 'c_lang' => 'fas fa-language', 'c_manus' => 'fas fa-scroll', 'c_dawah' => 'fas fa-mosque', 'branches' => 'fas fa-code-branch', 'accred' => 'fas fa-award', 'calendar' => 'far fa-calendar-alt', 'privacy' => 'fas fa-user-lock', 'about_biu' => 'fas fa-info-circle', 'study_system' => 'fas fa-graduation-cap', 'hq' => 'fas fa-map-marker-alt', 'agreements' => 'fas fa-handshake', 'org_chart' => 'fas fa-sitemap', 'scholarships' => 'fas fa-hand-holding-heart', 'sharia_prog' => 'fas fa-book-open', 'personal_status' => 'fas fa-gavel', 'guidance' => 'fas fa-compass', 'preachers_dip' => 'fas fa-microphone-alt', 'introducers_dip' => 'fas fa-globe-asia', 'arabic_dept' => 'fas fa-pen-nib', 'terms' => 'fas fa-file-contract', 'credits' => 'far fa-images'];
    return $I[$k] ?? 'fas fa-file-alt';
}
function uwciv_admin_pages_tree()
{
    $cur = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'pages.php' ? (string) ($_GET['p'] ?? '') : '';
    $cols = ['#0e7a52', '#2c5fd0', '#5b4bb7', '#c9962a', '#9b1f45'];
    $h = '<div class="ad-cols">'; $n = 0;
    foreach (uwciv_info_page_groups() as $g => $d) {
        $on = in_array($cur, $d[1], true);
        $h .= '<div class="ad-g2' . ($on ? ' open' : '') . '" style="--c:' . $cols[$n++ % 5] . '"><a class="ad-g2h" href="/admin/pages.php"><i class="' . $d[0] . '"></i><span>' . $g . '</span><i class="fas fa-chevron-down ad-gc"></i></a><div class="ad-g2l">';
        foreach ($d[1] as $k) $h .= '<a class="n2' . ($cur === $k ? ' on' : '') . '" href="/admin/pages.php?p=' . $k . '"><i class="' . uwciv_info_page_icon($k) . '"></i>' . uwciv_h(t($k)) . '</a>';
        $h .= '</div></div>';
    }
    return $h . '</div>';
}

/* شجرة الكليات في القائمة الجانبية: كل كلية عنوان رئيسي وتحتها أقسام بياناتها */
function uwciv_admin_college_tree()
{
    if (!function_exists('uwciv_college_counts')) require dirname(__DIR__) . '/inc/colleges.php';
    $C = $GLOBALS['UWCIV_COLLEGES'] ?? []; if (!$C) return '';
    $me = uwciv_staff_me(); $only = (!uwciv_is_super() && $me && !empty($me['college'])) ? $me['college'] : '';
    $cur = basename($_SERVER['SCRIPT_NAME'] ?? '') === 'college.php' ? (string) ($_GET['c'] ?? '') : ''; $curT = (string) ($_GET['t'] ?? 'id');
    $T = ['id' => ['fas fa-id-card', 'الهوية والتعريف'], 'deps' => ['fas fa-sitemap', 'الأقسام العلمية'], 'st' => ['fas fa-layer-group', 'المراحل والبرامج'], 'jobs' => ['fas fa-briefcase', 'المسارات المهنية'], 'dean' => ['fas fa-user-tie', 'العمادة والتواصل'], 'adm' => ['fas fa-clipboard-check', 'شروط القبول']];
    $h = '<div class="ad-cols">';
    foreach ($C as $s => $c) {
        if ($only && $s !== $only) continue;
        $on = $cur === $s;
        $h .= '<div class="ad-g2' . ($on ? ' open' : '') . '" style="--c:' . uwciv_h($c['c']) . '"><a class="ad-g2h" href="/admin/college.php?c=' . $s . '"><i class="fas ' . uwciv_h($c['i']) . '"></i><span>' . uwciv_h(preg_replace('/^كلية\s+/u', '', $c['n'])) . '</span><i class="fas fa-chevron-down ad-gc"></i></a><div class="ad-g2l">';
        foreach ($T as $k => $t) $h .= '<a class="n2' . ($on && $curT === $k ? ' on' : '') . '" href="/admin/college.php?c=' . $s . '&amp;t=' . $k . '"><i class="' . $t[0] . '"></i>' . $t[1] . '</a>';
        $h .= '<a class="n2" href="/college/' . $s . '" target="_blank"><i class="fas fa-external-link-alt"></i>صفحة الكلية</a></div></div>';
    }
    return $h . '</div>';
}

function uwciv_admin_toast($msg, $bad = false)
{
    if ($msg === '' || $msg === null) return;
    echo '<div class="ad-toast ' . ($bad ? 'bad' : 'ok') . '" data-toast><i class="fas ' . ($bad ? 'fa-exclamation-circle' : 'fa-check-circle') . '"></i><span>' . uwciv_h($msg) . '</span></div>';
}

function uwciv_admin_close()
{
    $nav = uwciv_admin_nav(); $super = uwciv_is_super(); $items = [];
    foreach ($nav as $k => $n) if (uwciv_can($k)) $items[] = ['t' => $n[2], 'u' => $n[3], 'i' => $n[1], 'g' => $n[0]];
    if ($super) {
        foreach ([['صفحة الوظائف', '/jobs', 'fas fa-external-link-alt'], ['صفحة اللوائح', '/regulations', 'fas fa-external-link-alt'], ['صفحة الأعضاء', '/teacher/index.php', 'fas fa-external-link-alt'], ['الصفحة الرئيسية للموقع', '/index.php', 'fas fa-external-link-alt'], ['المكتبة الرقمية (الواجهة)', '/librarysystem/index.php', 'fas fa-external-link-alt'], ['بوابة الامتحانات (الواجهة)', '/onlineexam/index.php', 'fas fa-external-link-alt']] as $x) $items[] = ['t' => $x[0], 'u' => $x[1], 'i' => $x[2], 'g' => 'صفحات الموقع'];
    }
    ?>
    </div>
    <footer class="ad-foot2">© <?= date('Y') ?> جامعة بنيان العالمية · مركز الإدارة</footer>
  </main>
</div>
<div class="ad-pal" id="adpal" hidden><div class="ad-pal-box" role="dialog" aria-label="انتقال سريع"><div class="ad-in"><i class="fas fa-search"></i><input id="adpq" placeholder="اكتب اسم اللوحة أو الصفحة…" autocomplete="off"><kbd>Esc</kbd></div><ul id="adpl"></ul></div></div>
<script>window.UWCIV_NAV=<?= json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
<script src="/assets/js/uwciv-admin.js?v=6"></script>
</body>
</html>
<?php
}

/* رفع صورة آمن: يعيد اسم الملف أو '' — يتحقق من النوع الحقيقي */
function uwciv_admin_upload_image($field, $dir, $maxMB = 6)
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return '';
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('تعذّر رفع الصورة.');
    if ($f['size'] > $maxMB * 1048576) throw new RuntimeException('حجم الصورة أكبر من ' . $maxMB . ' ميغابايت.');
    $info = @getimagesize($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$info['mime'] ?? ''] ?? '';
    if (!$ext) throw new RuntimeException('الملف ليس صورة صالحة (JPG أو PNG أو WEBP).');
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $name = date('Ymd') . '-' . bin2hex(random_bytes(5)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], rtrim($dir, '/') . '/' . $name)) throw new RuntimeException('تعذّر حفظ الصورة على الخادم.');
    return $name;
}

/* رفع PDF آمن */
function uwciv_admin_upload_pdf($field, $dir, $maxMB = 30)
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return '';
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('تعذّر رفع الملف (قد يكون أكبر من الحد المسموح على الخادم).');
    if ($f['size'] > $maxMB * 1048576) throw new RuntimeException('حجم الملف أكبر من ' . $maxMB . ' ميغابايت.');
    $fh = fopen($f['tmp_name'], 'rb'); $head = fread($fh, 5); fclose($fh);
    if ($head !== '%PDF-') throw new RuntimeException('الملف ليس PDF صالحًا.');
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    $name = uniqid() . '.pdf';
    if (!move_uploaded_file($f['tmp_name'], rtrim($dir, '/') . '/' . $name)) throw new RuntimeException('تعذّر حفظ الملف على الخادم.');
    return $name;
}
