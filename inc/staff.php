<?php
/*
 * الموظفون والصلاحيات — يضيفهم المشرف العام من /admin/staff.php
 * التخزين: _private/staff.json (كلمات المرور مشفّرة bcrypt) و _private/staff_log.json (سجل النشاط)
 */
function uwciv_staff_file() { return dirname(__DIR__) . '/_private/staff.json'; }
function uwciv_staff_all()
{
    $f = uwciv_staff_file();
    $d = is_file($f) ? json_decode((string) file_get_contents($f), true) : [];
    return is_array($d) ? $d : [];
}
function uwciv_staff_save(array $all)
{
    $f = uwciv_staff_file(); if (!is_dir(dirname($f))) @mkdir(dirname($f), 0750, true);
    return (bool) @file_put_contents($f, json_encode(array_values($all), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
}
function uwciv_staff_log($event, $who = null)
{
    $f = dirname(__DIR__) . '/_private/staff_log.json';
    $L = is_file($f) ? (json_decode((string) file_get_contents($f), true) ?: []) : [];
    $me = $who ?: uwciv_staff_me();
    $L[] = ['t' => date('Y-m-d H:i'), 'who' => $me['name'] ?? ($who['name'] ?? 'المشرف العام'), 'e' => $event, 'ip' => $_SERVER['REMOTE_ADDR'] ?? ''];
    $L = array_slice($L, -400);
    @file_put_contents($f, json_encode($L, JSON_UNESCAPED_UNICODE), LOCK_EX);
}
function uwciv_staff_logs() { $f = dirname(__DIR__) . '/_private/staff_log.json'; return is_file($f) ? array_reverse(json_decode((string) file_get_contents($f), true) ?: []) : []; }

/* الموظف الحالي — يُقرأ من الملف في كل طلب حتى يسري الإيقاف وتعديل الصلاحيات فورًا */
function uwciv_staff_me()
{
    static $me = false; if ($me !== false) return $me;
    $me = null;
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    $id = $_SESSION['uwciv_staff'] ?? '';
    if ($id === '') return $me;
    foreach (uwciv_staff_all() as $s) if ($s['id'] === $id && !empty($s['active'])) { $me = $s; break; }
    if (!$me) unset($_SESSION['uwciv_staff']);
    return $me;
}

/* محاولة دخول موظف — يرجع السجل أو null */
function uwciv_staff_login($u, $p)
{
    $u = mb_strtolower(trim((string) $u));
    if ($u === '' || $p === '') return null;
    $all = uwciv_staff_all();
    foreach ($all as $i => $s) {
        if (empty($s['active'])) continue;
        if ($u !== mb_strtolower($s['username']) && $u !== mb_strtolower($s['email'] ?? '')) continue;
        if (!password_verify($p, $s['hash'])) return null;
        $all[$i]['last'] = date('Y-m-d H:i'); uwciv_staff_save($all);
        $_SESSION['uwciv_staff'] = $s['id'];
        uwciv_staff_log('تسجيل دخول', $s);
        return $s;
    }
    return null;
}

/* هل يملك المستخدم الحالي صلاحية مفتاح لوحة؟ */
function uwciv_can($key)
{
    if (uwciv_is_super()) return true;
    if ($key === 'overview') return (bool) uwciv_staff_me() || !empty($_SESSION['uwciv_scope']);
    if (in_array($key, ['password', 'staff'], true)) return false;
    $me = uwciv_staff_me();
    if ($me && in_array($key, (array) $me['perms'], true)) return true;
    $scopes = ['library' => 'library', 'grad' => 'grad'];
    return isset($scopes[$key]) && !empty($_SESSION['uwciv_scope'][$scopes[$key]]);
}

/* الصلاحيات القابلة للإسناد (من القائمة الجانبية) */
function uwciv_perm_defs()
{
    $out = [];
    foreach (uwciv_admin_nav() as $k => $n) if (!in_array($k, ['overview', 'password', 'staff'], true)) $out[$k] = [$n[0], $n[1], $n[2]];
    return $out;
}

/* الأدوار الجاهزة — مأخوذة من «سلّم الصلاحيات» في دليل اللوائح */
function uwciv_staff_roles()
{
    return [
        'president'  => ['رئيس الجامعة', 'fas fa-user-tie', 'القيادة التنفيذية والأكاديمية — اطلاع وإدارة شاملة للمحتوى والطلبات', ['applications', 'jobs', 'tickets', 'news', 'events', 'members', 'regs', 'calendar', 'colleges', 'library', 'grad', 'exams', 'menu', 'ticker', 'pages']],
        'vp_acad'    => ['نائب الرئيس للشؤون الأكاديمية', 'fas fa-graduation-cap', 'الإشراف على الكليات والبرامج والاختبارات والتقويم', ['applications', 'regs', 'calendar', 'colleges', 'curriculum', 'exams', 'library', 'grad', 'members']],
        'vp_research'=> ['نائب الرئيس للبحث والتطوير', 'fas fa-lightbulb', 'البحث العلمي والمكتبة والمجلة', ['library', 'news', 'regs']],
        'dean_digital'=>['عميد التعلم الرقمي', 'fas fa-laptop-code', 'المنصة والامتحانات الإلكترونية وحسابات الطلاب والدعم', ['exams', 'accounts', 'tickets', 'library']],
        'gm_admin'   => ['المدير العام للشؤون الإدارية والمالية', 'fas fa-building', 'التوظيف والموارد والشكاوى الإدارية', ['jobs', 'members', 'tickets', 'applications']],
        'dean'       => ['عميد الكلية', 'fas fa-university', 'بيانات كليته وطلابها واختباراتها (حدّد الكلية في نموذج الموظف)', ['colleges', 'applications', 'exams', 'calendar']],
        'head_dept'  => ['رئيس القسم العلمي', 'fas fa-sitemap', 'الاختبارات والمحتوى العلمي للقسم', ['exams', 'library']],
        'admission'  => ['مدير القبول والتسجيل', 'fas fa-user-plus', 'طلبات الالتحاق وحسابات البوابة والتقويم', ['applications', 'accounts', 'calendar']],
        'hr'         => ['مدير الموارد البشرية', 'fas fa-id-card', 'طلبات التوظيف والشواغر وبيانات الأعضاء', ['jobs', 'members']],
        'media'      => ['المركز الإعلامي', 'fas fa-bullhorn', 'الأخبار والفعاليات والتواصل الاجتماعي والقائمة الرئيسية', ['news', 'events', 'ticker', 'pages', 'social', 'menu']],
        'librarian'  => ['أمين المكتبة', 'fas fa-book', 'إدارة المكتبة الرقمية', ['library']],
        'alumni'     => ['مسؤول شؤون الخريجين', 'fas fa-user-graduate', 'وثائق التخرج وأخبار الخريجين', ['grad', 'events']],
        'quality'    => ['وحدة الجودة واللوائح', 'fas fa-check-double', 'اللوائح والصلاحيات وأعضاء الجامعة والتقويم وبيانات الكليات', ['regs', 'members', 'calendar', 'colleges', 'pages']],
        'faculty'    => ['عضو هيئة التدريس', 'fas fa-chalkboard-teacher', 'إعداد الامتحانات الإلكترونية ومتابعة نتائجها', ['exams']],
        'support'    => ['الدعم الفني', 'fas fa-life-ring', 'رسائل الدعم وحسابات الطلاب', ['tickets', 'accounts']],
        'custom'     => ['صلاحيات مخصّصة', 'fas fa-sliders-h', 'اختر الصلاحيات يدويًا', []],
    ];
}
