<?php
/*
 * واجهة تحرير الخطط الدراسية (تستعملها أيقونات التعديل في الموقع ولوحة التحكم)
 * POST فقط + رمز CSRF + صلاحية «الخطط الدراسية». الرد JSON.
 */
require_once dirname(__DIR__) . '/db_config.php';
require_once dirname(__DIR__) . '/inc/staff.php';
require_once dirname(__DIR__) . '/inc/curriculum.php';
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function out($ok, $msg = '', $extra = []) { echo json_encode(['ok' => $ok, 'msg' => $msg] + $extra, JSON_UNESCAPED_UNICODE); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'طلب غير صالح.');
if (!cur_can_edit()) { http_response_code(403); out(false, 'لا تملك صلاحية تعديل الخطط الدراسية.'); }
if (!hash_equals(cur_csrf(), (string) ($_POST['csrf'] ?? ''))) out(false, 'انتهت صلاحية الصفحة، حدّثها ثم أعد المحاولة.');

$doc = cur_doc(true);
$act = (string) ($_POST['act'] ?? '');
$s = function ($k, $max = 300) { return trim(mb_substr(strip_tags((string) ($_POST[$k] ?? '')), 0, $max)); };
$codeOk = function ($c) { return (bool) preg_match('/^[A-Z]{2,4}[0-9]{3}$/', $c); };
$slug = preg_replace('/[^a-z0-9-]/', '', (string) ($_POST['program'] ?? ''));
$code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) ($_POST['code'] ?? '')));
if (isset($doc['programs'][$slug])) $P = &$doc['programs'][$slug]; else $P = null;
$needProgram = function () use ($doc, $slug) { if (!isset($doc['programs'][$slug])) out(false, 'البرنامج غير موجود.'); };
$needCourse = function ($c) use ($doc) { if (!isset($doc['courses'][$c])) out(false, 'المقرر غير موجود: ' . $c); };
$log = function ($t) { if (function_exists('uwciv_staff_log')) uwciv_staff_log('الخطط الدراسية: ' . $t); };

switch ($act) {

case 'course-get':
    $needCourse($code);
    out(true, '', ['course' => $doc['courses'][$code], 'all' => array_map(function ($c) { return $c['n']; }, $doc['courses'])]);

case 'course-save': {
    $orig = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) ($_POST['orig'] ?? '')));
    if (!$codeOk($code)) out(false, 'الرمز يجب أن يكون 2–4 حروف لاتينية كبيرة ثم 3 أرقام، مثل FIQ201.');
    if ($orig !== $code && isset($doc['courses'][$code])) out(false, 'الرمز ' . $code . ' مستخدم لمقرر آخر.');
    $n = $s('n', 160); if ($n === '') out(false, 'اكتب اسم المقرر بالعربية.');
    $cr = (int) ($_POST['cr'] ?? 0); if ($cr < 0 || $cr > 200) out(false, 'عدد الساعات غير صحيح.');
    $t = (string) ($_POST['t'] ?? 'M'); if (!isset(cur_types()[$t])) $t = 'M';
    $pre = array_values(array_unique(array_filter(array_map(function ($x) { return strtoupper(trim($x)); }, preg_split('/[\s,،]+/u', (string) ($_POST['pre'] ?? ''))))));
    foreach ($pre as $p) { if (!isset($doc['courses'][$p]) && $p !== $code) out(false, 'المتطلب ' . $p . ' غير موجود في الكتالوج.'); if ($p === $code || $p === $orig) out(false, 'لا يكون المقرر متطلبًا لنفسه.'); }
    $old = ($orig && isset($doc['courses'][$orig])) ? $doc['courses'][$orig] : ['books' => []];
    $rec = ['n' => $n, 'en' => $s('en', 160), 'bn' => $s('bn', 160), 'ur' => $s('ur', 160), 'cr' => $cr, 'pre' => $pre, 't' => $t, 'd' => $s('d', 1200), 'books' => $old['books'] ?? []];
    if ($orig && $orig !== $code && isset($doc['courses'][$orig])) { // تغيير الرمز: تحديث كل الإحالات
        unset($doc['courses'][$orig]);
        foreach ($doc['courses'] as &$cc) $cc['pre'] = array_map(function ($x) use ($orig, $code) { return $x === $orig ? $code : $x; }, $cc['pre']); unset($cc);
        foreach ($doc['programs'] as &$pp) foreach ($pp['levels'] as &$L) $L['c'] = array_map(function ($x) use ($orig, $code) { return $x === $orig ? $code : $x; }, $L['c']); unset($pp, $L);
    }
    $doc['courses'][$code] = $rec;
    // إضافة إلى الخطة مباشرة عند الإنشاء من داخل مستوى
    if (!$orig && isset($doc['programs'][$slug]) && isset($_POST['level']) && isset($doc['programs'][$slug]['levels'][(int) $_POST['level']])) $doc['programs'][$slug]['levels'][(int) $_POST['level']]['c'][] = $code;
    cur_save($doc) ? $log(($orig ? 'تعديل ' : 'إضافة ') . $code) : out(false, 'تعذّر الحفظ على الخادم.');
    out(true, 'تم حفظ المقرر ' . $code . '.');
}

case 'course-delete':
    $needCourse($code);
    $used = [];
    foreach ($doc['courses'] as $k => $cc) if (in_array($code, $cc['pre'], true)) $used[] = $k;
    if ($used && empty($_POST['force'])) out(false, 'هذا المقرر متطلب سابق لـ: ' . implode('، ', $used) . '. أكّد الحذف لإزالته من متطلباتها أيضًا.', ['confirm' => true]);
    foreach ($doc['courses'][$code]['books'] as $b) if (!empty($b['file'])) @unlink(cur_books_dir() . '/' . basename($b['file']));
    unset($doc['courses'][$code]);
    foreach ($doc['courses'] as &$cc) $cc['pre'] = array_values(array_diff($cc['pre'], [$code])); unset($cc);
    foreach ($doc['programs'] as &$pp) foreach ($pp['levels'] as &$L) $L['c'] = array_values(array_diff($L['c'], [$code])); unset($pp, $L);
    cur_save($doc); $log('حذف ' . $code);
    out(true, 'تم حذف المقرر ' . $code . ' من الكتالوج وكل الخطط.', ['go' => '/plans.php']);

case 'plan-add':
    $needProgram(); $needCourse($code);
    $li = (int) ($_POST['level'] ?? -1); if (!isset($P['levels'][$li])) out(false, 'المستوى غير موجود.');
    foreach ($P['levels'] as $L) if (in_array($code, $L['c'], true)) out(false, 'المقرر موجود في الخطة بالفعل.');
    $P['levels'][$li]['c'][] = $code; cur_save($doc); $log('إضافة ' . $code . ' إلى ' . $slug);
    out(true, 'أُضيف ' . $code . ' إلى ' . $P['levels'][$li]['n'] . '.');

case 'plan-remove':
    $needProgram();
    foreach ($P['levels'] as &$L) $L['c'] = array_values(array_diff($L['c'], [$code])); unset($L);
    cur_save($doc); $log('إزالة ' . $code . ' من ' . $slug);
    out(true, 'أُزيل ' . $code . ' من الخطة (يبقى في الكتالوج).');

case 'plan-move':
    $needProgram();
    $to = (int) ($_POST['to'] ?? -1); if (!isset($P['levels'][$to])) out(false, 'المستوى غير موجود.');
    foreach ($P['levels'] as &$L) $L['c'] = array_values(array_diff($L['c'], [$code])); unset($L);
    $P['levels'][$to]['c'][] = $code; cur_save($doc); $log('نقل ' . $code . ' في ' . $slug);
    out(true, 'نُقل ' . $code . ' إلى ' . $P['levels'][$to]['n'] . '.');

case 'level-add':
    $needProgram(); $n = $s('name', 60) ?: 'المستوى ' . (count($P['levels']) + 1);
    $P['levels'][] = ['n' => $n, 'c' => []]; cur_save($doc); out(true, 'أُضيف ' . $n . '.');
case 'level-rename':
    $needProgram(); $li = (int) ($_POST['level'] ?? -1); $n = $s('name', 60);
    if (!isset($P['levels'][$li]) || $n === '') out(false, 'بيانات غير صحيحة.');
    $P['levels'][$li]['n'] = $n; cur_save($doc); out(true, 'تم تعديل اسم المستوى.');
case 'level-delete':
    $needProgram(); $li = (int) ($_POST['level'] ?? -1);
    if (!isset($P['levels'][$li])) out(false, 'المستوى غير موجود.');
    if ($P['levels'][$li]['c']) out(false, 'انقل مقررات هذا المستوى أو احذفها أولًا.');
    array_splice($P['levels'], $li, 1); cur_save($doc); out(true, 'حُذف المستوى.');

case 'program-get':
    $needProgram(); out(true, '', ['program' => $P]);
case 'program-save': {
    $new = !isset($doc['programs'][$slug]);
    if ($new && !preg_match('/^[a-z][a-z0-9-]{2,40}$/', $slug)) out(false, 'معرّف البرنامج بحروف لاتينية صغيرة وشرطات، مثل quran-ba.');
    $college = (string) ($_POST['college'] ?? ''); if (!isset($GLOBALS['UWCIV_COLLEGES'][$college])) out(false, 'اختر الكلية.');
    $stage = (string) ($_POST['stage'] ?? ''); if (!in_array($stage, ['dip', 'bac', 'mas', 'phd'], true)) out(false, 'اختر المرحلة.');
    $rec = $new ? ['levels' => [], 'plos' => []] : $doc['programs'][$slug];
    $rec['college'] = $college; $rec['stage'] = $stage; $rec['n'] = $s('n', 160); $rec['en'] = $s('en', 160); $rec['bn'] = $s('bn', 160); $rec['ur'] = $s('ur', 160);
    $rec['target'] = max(0, (int) ($_POST['target'] ?? 0)); $rec['unit'] = ($_POST['unit'] ?? 'cr') === 'contact' ? 'contact' : 'cr';
    $rec['kkni'] = max(0, min(9, (int) ($_POST['kkni'] ?? 0))); $rec['years'] = max(0, min(8, (int) ($_POST['years'] ?? 0)));
    $rec['desc'] = $s('desc', 1200); $rec['status'] = ($_POST['status'] ?? '') === 'approved' ? 'approved' : 'proposed';
    $rec['plos'] = array_values(array_filter(array_map('trim', explode("\n", strip_tags((string) ($_POST['plos'] ?? ''))))));
    if ($rec['n'] === '') out(false, 'اكتب اسم البرنامج.');
    if ($new) { $nl = max(1, min(12, (int) ($_POST['levels'] ?? 8))); for ($i = 1; $i <= $nl; $i++) $rec['levels'][] = ['n' => 'المستوى ' . $i, 'c' => []]; }
    $doc['programs'][$slug] = $rec; cur_save($doc); $log(($new ? 'إنشاء برنامج ' : 'تعديل برنامج ') . $slug);
    out(true, 'تم حفظ البرنامج.', ['go' => '/plan.php?p=' . $slug]);
}
case 'program-delete':
    $needProgram(); unset($doc['programs'][$slug]); cur_save($doc); $log('حذف برنامج ' . $slug);
    out(true, 'حُذف البرنامج (تبقى مقرراته في الكتالوج).', ['go' => '/admin/curriculum.php']);

case 'book-save': {
    $needCourse($code);
    $id = preg_replace('/[^a-z0-9-]/', '', (string) ($_POST['book'] ?? ''));
    $lang = (string) ($_POST['lang'] ?? 'ar'); if (!isset(cur_langs()[$lang])) $lang = 'ar';
    $cat = (string) ($_POST['cat'] ?? 'main'); if (!isset(cur_book_cats()[$cat])) $cat = 'main';
    $q = (string) ($_POST['q'] ?? 'approved'); if (!isset(cur_book_quality()[$q])) $q = 'approved';
    $title = $s('title', 250); if ($title === '') out(false, 'اكتب عنوان الكتاب.');
    $url = trim((string) ($_POST['url'] ?? ''));
    if ($url !== '' && !cur_url_ok($url)) out(false, 'الرابط يجب أن يكون https ومن مكتبة موثوقة: ' . implode('، ', cur_trusted_hosts()) . ' (يمكن إضافة نطاق من لوحة الخطط).');
    $books = &$doc['courses'][$code]['books'];
    $idx = null; foreach ($books as $i => $b) if ($b['id'] === $id) $idx = $i;
    $rec = $idx !== null ? $books[$idx] : ['id' => strtolower($code) . '-' . bin2hex(random_bytes(3)), 'file' => '', 'size' => 0, 'dl' => 0];
    $rec = array_merge($rec, ['lang' => $lang, 'cat' => $cat, 'q' => $q, 'title' => $title, 'author' => $s('author', 200), 'url' => $url]);
    if (!empty($_FILES['pdf']) && $_FILES['pdf']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['pdf'];
        if ($f['error'] !== UPLOAD_ERR_OK) out(false, 'تعذّر رفع الملف (قد يكون أكبر من حد الخادم).');
        if ($f['size'] > 60 * 1048576) out(false, 'حجم الملف أكبر من 60 ميغابايت.');
        $fh = fopen($f['tmp_name'], 'rb'); $magic = fread($fh, 5); fclose($fh);
        if ($magic !== '%PDF-') out(false, 'الملف ليس PDF صالحًا.');
        $raw = file_get_contents($f['tmp_name'], false, null, 0, 2 * 1048576);
        if (preg_match('~/(JavaScript|JS|Launch|EmbeddedFile)\b~', (string) $raw)) out(false, 'رُفض الملف: يحتوي على شيفرات أو ملفات مضمّنة غير آمنة.');
        $name = strtolower($code) . '-' . bin2hex(random_bytes(8)) . '.pdf';
        if (!move_uploaded_file($f['tmp_name'], cur_books_dir() . '/' . $name)) out(false, 'تعذّر حفظ الملف على الخادم.');
        if (!empty($rec['file'])) @unlink(cur_books_dir() . '/' . basename($rec['file']));
        $rec['file'] = $name; $rec['size'] = (int) $f['size'];
    }
    if (!empty($_POST['remove_file']) && !empty($rec['file'])) { @unlink(cur_books_dir() . '/' . basename($rec['file'])); $rec['file'] = ''; $rec['size'] = 0; }
    if ($idx !== null) $books[$idx] = $rec; else $books[] = $rec;
    unset($books);
    cur_save($doc); $log('كتاب في ' . $code . ': ' . $title);
    out(true, 'تم حفظ الكتاب.');
}
case 'book-get':
    $needCourse($code); $id = (string) ($_POST['book'] ?? '');
    foreach ($doc['courses'][$code]['books'] as $b) if ($b['id'] === $id) out(true, '', ['book' => $b]);
    out(false, 'الكتاب غير موجود.');
case 'book-delete':
    $needCourse($code); $id = (string) ($_POST['book'] ?? '');
    foreach ($doc['courses'][$code]['books'] as $i => $b) if ($b['id'] === $id) {
        if (!empty($b['file'])) @unlink(cur_books_dir() . '/' . basename($b['file']));
        array_splice($doc['courses'][$code]['books'], $i, 1); cur_save($doc); $log('حذف كتاب من ' . $code);
        out(true, 'حُذف الكتاب.');
    }
    out(false, 'الكتاب غير موجود.');

case 'trusted-save':
    $list = array_values(array_unique(array_filter(array_map(function ($x) { $x = strtolower(trim($x)); return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $x) ? $x : ''; }, preg_split('/[\s,،]+/u', (string) ($_POST['trusted'] ?? ''))))));
    $doc['trusted'] = $list; cur_save($doc); out(true, 'تم حفظ النطاقات الموثوقة.');

default: out(false, 'إجراء غير معروف.');
}
