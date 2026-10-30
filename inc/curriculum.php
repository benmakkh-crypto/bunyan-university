<?php
/*
 * الخطط الدراسية لجامعة بنيان العالمية — المقررات والبرامج والمستويات والكتب
 * البيانات الافتراضية: inc/curriculum_seed.json (يولّدها _install/gen_curriculum.py)
 * تعديلات الموقع ولوحة التحكم تُحفظ كاملة في _private/curriculum.json مع نسخ احتياطية.
 */
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/colleges.php';

function cur_seed_file() { return __DIR__ . '/curriculum_seed.json'; }
function cur_live_file() { return dirname(__DIR__) . '/_private/curriculum.json'; }
function cur_books_dir() { $d = dirname(__DIR__) . '/_private/books'; if (!is_dir($d)) @mkdir($d, 0750, true); return $d; }

function cur_doc($fresh = false)
{
    static $D = null;
    if ($D !== null && !$fresh) return $D;
    $f = is_file(cur_live_file()) ? cur_live_file() : cur_seed_file();
    $D = json_decode((string) @file_get_contents($f), true);
    if (!is_array($D) || empty($D['courses'])) $D = json_decode((string) file_get_contents(cur_seed_file()), true);
    return $D;
}
function cur_save(array $doc)
{
    $f = cur_live_file();
    if (!is_dir(dirname($f))) @mkdir(dirname($f), 0750, true);
    if (is_file($f)) { // نسخة احتياطية (آخر 20)
        $b = dirname($f) . '/curriculum-backups'; if (!is_dir($b)) @mkdir($b, 0750, true);
        @copy($f, $b . '/curriculum-' . date('Ymd-His') . '.json');
        $all = glob($b . '/curriculum-*.json') ?: []; sort($all);
        foreach (array_slice($all, 0, max(0, count($all) - 20)) as $old) @unlink($old);
    }
    $doc['updated'] = date('Y-m-d H:i');
    $ok = @file_put_contents($f, json_encode($doc, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) !== false;
    cur_doc(true);
    return $ok;
}

/* ===== التسميات بأربع لغات ===== */
function cur_lang() { return in_array($GLOBALS['UWCIV_LANG'] ?? 'ar', ['ar', 'en', 'bn', 'ur'], true) ? $GLOBALS['UWCIV_LANG'] : 'ar'; }
function cur_t($k)
{
    static $T = [
        'plans' => ['الخطط الدراسية', 'Study Plans', 'অধ্যয়ন পরিকল্পনা', 'تعلیمی منصوبے'],
        'level' => ['المستوى', 'Level', 'স্তর', 'سطح'],
        'code' => ['الرمز', 'Code', 'কোড', 'کوڈ'],
        'course' => ['المقرر', 'Course', 'কোর্স', 'کورس'],
        'cr' => ['الساعات', 'Credits', 'ক্রেডিট', 'کریڈٹ'],
        'contact' => ['ساعات الاتصال', 'Contact hours', 'যোগাযোগ ঘণ্টা', 'رابطہ گھنٹے'],
        'pre' => ['المتطلب السابق', 'Prerequisite', 'পূর্বশর্ত', 'پیشگی شرط'],
        'type' => ['النوع', 'Type', 'ধরন', 'قسم'],
        'total' => ['المجموع', 'Total', 'মোট', 'کل'],
        'books' => ['الكتب والمراجع', 'Books & References', 'বই ও তথ্যসূত্র', 'کتب و حوالہ جات'],
        'download' => ['تحميل PDF', 'Download PDF', 'PDF ডাউনলোড', 'PDF ڈاؤن لوڈ'],
        'read' => ['قراءة / تحميل', 'Read / Download', 'পড়ুন / ডাউনলোড', 'پڑھیں / ڈاؤن لوڈ'],
        'nofile' => ['يُضاف الملف قريبًا', 'File coming soon', 'শীঘ্রই ফাইল যোগ হবে', 'فائل جلد شامل ہوگی'],
        'chain' => ['شجرة المتطلبات', 'Prerequisite Map', 'পূর্বশর্ত মানচিত্র', 'پیشگی شرائط کا نقشہ'],
        'unlocks' => ['يفتح المقررات', 'Unlocks', 'যা খোলে', 'کھولتا ہے'],
        'outcomes' => ['مخرجات التعلم', 'Learning Outcomes', 'শিখন ফলাফল', 'تعلیمی نتائج'],
        'programs' => ['البرامج التي تتضمنه', 'Programs including it', 'যে প্রোগ্রামে আছে', 'شامل پروگرام'],
        'none' => ['لا يوجد', 'None', 'নেই', 'کوئی نہیں'],
    ];
    $i = array_search(cur_lang(), ['ar', 'en', 'bn', 'ur'], true);
    return $T[$k][$i] ?? ($T[$k][0] ?? $k);
}
function cur_langs() { return ['ar' => ['العربية', 'rtl'], 'en' => ['English', 'ltr'], 'bn' => ['বাংলা', 'ltr'], 'ur' => ['اردو', 'rtl']]; }
function cur_types() { return ['U' => ['متطلب جامعة', '#2c5fd0'], 'C' => ['متطلب كلية', '#0e9aa7'], 'M' => ['تخصص إجباري', '#12935a'], 'E' => ['اختياري', '#c9962a'], 'P' => ['تطبيقي / بحث', '#9b1f45']]; }
function cur_book_cats() { return ['main' => ['أساسي', '#12935a'], 'support' => ['مساند', '#2c5fd0'], 'extra' => ['إثرائي', '#c9962a']]; }
function cur_book_quality() { return ['verified' => ['طبعة محققة', 'fa-certificate'], 'approved' => ['معتمد أكاديميًا', 'fa-award'], 'dept' => ['مذكرة القسم', 'fa-file-alt']]; }
function cur_stage_name($s) { return ['dip' => 'الدبلوم', 'bac' => 'البكالوريوس', 'mas' => 'الماجستير', 'phd' => 'الدكتوراه'][$s] ?? $s; }

/* اسم المقرر حسب لغة الواجهة؛ العربية والإنجليزية دائمًا متاحتان */
function cur_name(array $c)
{
    $l = cur_lang();
    if ($l !== 'ar' && !empty($c[$l])) return $c[$l];
    return $c['n'];
}
function cur_prog_name(array $p)
{
    $l = cur_lang();
    if ($l !== 'ar' && !empty($p[$l])) return $p[$l];
    return $p['n'];
}
function cur_course($code) { $d = cur_doc(); return $d['courses'][$code] ?? null; }
function cur_program($slug) { $d = cur_doc(); return $d['programs'][$slug] ?? null; }

/* إحصاءات البرنامج والتحقق من الجودة */
function cur_program_stats(array $p)
{
    $d = cur_doc(); $tot = 0; $types = []; $lv = []; $issues = []; $seen = [];
    foreach ($p['levels'] as $i => $L) {
        $h = 0;
        foreach ($L['c'] as $code) {
            $c = $d['courses'][$code] ?? null;
            if (!$c) { $issues[] = 'مقرر محذوف من الكتالوج: ' . $code; continue; }
            foreach ($c['pre'] as $pr) if (!isset($seen[$pr])) $issues[] = $code . ' يتطلب ' . $pr . ' ولم يرد قبله في الخطة';
            $h += $c['cr']; $types[$c['t']] = ($types[$c['t']] ?? 0) + $c['cr'];
        }
        foreach ($L['c'] as $code) $seen[$code] = $i;
        if ($p['unit'] === 'cr' && $p['stage'] === 'bac' && $h > 19) $issues[] = $L['n'] . ': ' . $h . ' ساعة (الحد 19)';
        $lv[] = $h; $tot += $h;
    }
    if ($tot !== (int) $p['target']) $issues[] = 'مجموع الخطة ' . $tot . ' والمطلوب ' . $p['target'];
    return ['total' => $tot, 'levels' => $lv, 'types' => $types, 'issues' => $issues, 'courses' => count($seen)];
}
/* المقررات التي يفتحها هذا المقرر داخل برنامج معيّن (أو كل البرامج) */
function cur_unlocks($code, $slug = null)
{
    $d = cur_doc(); $in = null;
    if ($slug && isset($d['programs'][$slug])) { $in = []; foreach ($d['programs'][$slug]['levels'] as $L) foreach ($L['c'] as $c) $in[$c] = 1; }
    $out = [];
    foreach ($d['courses'] as $k => $c) if (in_array($code, $c['pre'], true) && ($in === null || isset($in[$k]))) $out[] = $k;
    return $out;
}
function cur_programs_of($code)
{
    $out = [];
    foreach (cur_doc()['programs'] as $s => $p) foreach ($p['levels'] as $i => $L) if (in_array($code, $L['c'], true)) $out[$s] = $i;
    return $out;
}
/* ساعات التعلم ومعادلة ECTS: الساعة المعتمدة = 45 ساعة تعلم (Permendikbudristek 53/2023) ≈ 1.5 ECTS */
function cur_ects($cr) { return rtrim(rtrim(number_format($cr * 1.5, 1), '0'), '.'); }

/* ===== الصلاحيات ===== */
function cur_can_edit()
{
    if (!function_exists('uwciv_is_super')) @include_once dirname(__DIR__) . '/db_config.php';
    if (function_exists('uwciv_is_super') && uwciv_is_super()) return true;
    if (!function_exists('uwciv_can')) @include_once __DIR__ . '/staff.php';
    return function_exists('uwciv_can') && uwciv_can('curriculum');
}
function cur_csrf()
{
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

/* ===== الكتب والتحميل الآمن ===== */
function cur_trusted_hosts() { return array_values(array_filter(array_map('strtolower', cur_doc()['trusted'] ?? []))); }
function cur_url_ok($url)
{
    $p = parse_url((string) $url);
    if (!$p || ($p['scheme'] ?? '') !== 'https' || empty($p['host'])) return false;
    $h = strtolower($p['host']);
    foreach (cur_trusted_hosts() as $t) if ($h === $t || substr($h, -strlen('.' . $t)) === '.' . $t) return true;
    return false;
}
function cur_book_link($code, array $b)
{
    if (empty($b['file']) && empty($b['url'])) return '';
    return '/book.php?c=' . rawurlencode($code) . '&b=' . rawurlencode($b['id']);
}
function cur_fmt_size($n) { return $n >= 1048576 ? round($n / 1048576, 1) . ' MB' : ($n > 0 ? round($n / 1024) . ' KB' : ''); }
function cur_h($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }
