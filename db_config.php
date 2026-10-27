<?php
/*
 * إعدادات قواعد البيانات لموقع جامعة بنيان العالمية
 * كل صفحات الموقع تقرأ الاتصال من هذا الملف فقط.
 *
 * ✏️ اكتب كلمة مرور قواعد البيانات بين علامتي التنصيص في السطر التالي فقط:
 */
$DB_PASSWORD = ''; // لم تعد مستخدمة — لكل قاعدة كلمة مرورها أدناه

/*
 * دخول المشرف العام لكل لوحات التحكم والبوابات
 * اسم المستخدم: المشرف العام  (أو admin)
 * كلمة المرور محفوظة مُشفّرة (bcrypt) — لا تُكتب هنا بنص واضح.
 * لتغييرها: اطلب ذلك، أو ضع ناتج password_hash() الجديد مكان القيمة.
 */
$ADMIN_PANEL_USERS = ['المشرف العام', 'admin', 'benmakkh@gmail.com'];
// ⚠️ المستودع عام: البصمة لا تُرفع إلى GitHub. عند النشر انسخ هذا السطر من النسخة المضغوطة المرسلة في المحادثة.
$ADMIN_PANEL_PASSWORD_HASH = '';

/*
 * البريد الإلكتروني
 * UWCIV_MAIL_TO: بريد الجامعة الذي تصله رسائل نماذج التواصل والدعم الفني
 * UWCIV_SMTP_USER / PASS: حساب Gmail الذي يُرسل منه الموقع (يحتاج «كلمة مرور تطبيق» من Google)
 */
require_once __DIR__ . '/inc/settings.php';
if (!defined('UWCIV_MAIL_TO')) {
    define('UWCIV_MAIL_TO',   'your-email@gmail.com');
    define('UWCIV_SMTP_USER', 'your-email@gmail.com');
    // «كلمة مرور التطبيق» تُضبط من صفحة الإعدادات: your-domain.com/admin/settings
    define('UWCIV_SMTP_PASS', (string) (uwciv_settings()['smtp_pass'] ?? ''));
}
// كلمة مرور المشرف العام: إن غُيّرت من صفحة الإعدادات فهي المعتمدة
if (!empty(uwciv_settings()['admin_hash'])) $ADMIN_PANEL_PASSWORD_HASH = uwciv_settings()['admin_hash'];

if (defined('UWCIV_DB_CONFIG')) return;
define('UWCIV_DB_CONFIG', 1);

// نفس سلوك الإصدارات القديمة من PHP: أخطاء الاستعلام لا توقف الصفحة
mysqli_report(MYSQLI_REPORT_OFF);

$GLOBALS['UWCIV_DB'] = [
    // الاسم القديم => [المضيف, المستخدم, كلمة المرور, اسم القاعدة على الاستضافة]
    // ✏️ اكتب هنا بيانات قواعد البيانات الثلاث بعد إنشائها في hPanel ← قواعد البيانات
    'logweb'      => ['localhost', 'اسم_مستخدم_القاعدة_الرئيسية', 'كلمة_المرور', 'اسم_القاعدة_الرئيسية'],
    'courses_std' => ['localhost', 'اسم_مستخدم_قاعدة_الدورات',   'كلمة_المرور', 'اسم_قاعدة_الدورات'],
    'oesm'        => ['localhost', 'اسم_مستخدم_قاعدة_الامتحانات', 'كلمة_المرور', 'اسم_قاعدة_الامتحانات'],
];

$GLOBALS['UWCIV_ADMIN_USERS'] = $ADMIN_PANEL_USERS;
$GLOBALS['UWCIV_ADMIN_HASH'] = $ADMIN_PANEL_PASSWORD_HASH;

/* يتحقق من المشرف العام، ويفتح له جلسة موحّدة في كل الأقسام */
function uwciv_admin_login($user, $pw)
{
    $user = trim((string) $user);
    $ok = in_array($user, $GLOBALS['UWCIV_ADMIN_USERS'], true)
        && $GLOBALS['UWCIV_ADMIN_HASH'] !== ''
        && password_verify((string) $pw, $GLOBALS['UWCIV_ADMIN_HASH']);
    if ($ok) {
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        session_regenerate_id(true);
        $_SESSION['uwciv_super'] = 1;
    }
    return $ok;
}

function uwciv_is_super()
{
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    return !empty($_SESSION['uwciv_super']);
}

/* حماية صفحة إدارية: يحوّل لصفحة الدخول إن لم يكن المشرف مسجلاً */
function uwciv_require_super($loginUrl = '/syslog/admin/login.php')
{
    if (!uwciv_is_super()) { header('Location: ' . $loginUrl); exit; }
}

function db_cfg($key)
{
    return $GLOBALS['UWCIV_DB'][$key];
}

function db_mysqli_raw($host, $user, $pass, $name)
{
    $c = @mysqli_connect($host, $user, $pass, $name);
    if ($c) mysqli_set_charset($c, 'utf8mb4');
    return $c;
}

function db_mysqli($key)
{
    list($h, $u, $p, $n) = db_cfg($key);
    return db_mysqli_raw($h, $u, $p, $n);
}

function db_pdo($key)
{
    list($h, $u, $p, $n) = db_cfg($key);
    return new PDO("mysql:host=$h;dbname=$n;charset=utf8mb4", $u, $p);
}
