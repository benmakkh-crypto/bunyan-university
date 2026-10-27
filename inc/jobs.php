<?php
/* نظام التوظيف والانضمام لهيئة التدريس — التخزين في _private/jobs */
require_once __DIR__ . '/colleges.php';

function uwciv_jobs_dir() { $d = dirname(__DIR__) . '/_private/jobs'; if (!is_dir($d)) @mkdir($d, 0750, true); return $d; }

function uwciv_job_tracks()
{
    return [
        'faculty' => ['عضو هيئة تدريس', 'fa-chalkboard-teacher', '#1f7a45'],
        'lecturer'=> ['محاضر متعاون', 'fa-user-clock', '#0e9aa7'],
        'super'   => ['مشرف رسائل علمية', 'fa-user-graduate', '#2c5fd0'],
        'admin'   => ['وظيفة إدارية', 'fa-briefcase', '#5b4bb7'],
        'tech'    => ['تقنية وتعليم إلكتروني', 'fa-laptop-code', '#d0542c'],
        'volunteer' => ['تطوع وتعاون', 'fa-hands-helping', '#8a5a2b'],
    ];
}

function uwciv_job_ranks() { return ['أستاذ', 'أستاذ مشارك', 'أستاذ مساعد', 'محاضر', 'معيد', 'باحث', 'أخرى']; }
function uwciv_job_degrees() { return ['دكتوراه', 'ماجستير', 'بكالوريوس', 'دبلوم', 'إجازة علمية / سند']; }

/* حالات الطلب بالترتيب */
function uwciv_job_status()
{
    return [
        'new'       => ['تم الاستلام', 'fa-inbox', '#2c5fd0', 'وصل طلبك إلى لجنة التوظيف وسيُراجع قريبًا.'],
        'review'    => ['قيد المراجعة', 'fa-search', '#b7791f', 'تراجع اللجنة مؤهلاتك وخبراتك.'],
        'interview' => ['مرشّح للمقابلة', 'fa-video', '#5b4bb7', 'تم ترشيحك للمقابلة، وسنتواصل معك لتحديد الموعد.'],
        'accepted'  => ['مقبول', 'fa-check-circle', '#1f7a45', 'تهانينا! تمت الموافقة على طلبك، وسيصلك خطاب الانضمام.'],
        'waitlist'  => ['قائمة الانتظار', 'fa-hourglass-half', '#0e9aa7', 'طلبك ضمن قائمة الانتظار، وسنتواصل معك عند توفر الشاغر.'],
        'rejected'  => ['لم يُقبل حاليًا', 'fa-times-circle', '#9b1f45', 'نشكر اهتمامك، لم يُقبل الطلب في هذه الدورة ويمكنك التقديم لاحقًا.'],
    ];
}

/* الشواغر الافتراضية — يعدّلها المشرف */
function uwciv_jobs_seed_positions()
{
    $P = []; $i = 0;
    $add = function ($track, $title, $college, $desc, $type = 'عن بُعد') use (&$P, &$i) {
        $P[] = ['id' => 'p' . (++$i), 'track' => $track, 'title' => $title, 'college' => $college, 'desc' => $desc, 'type' => $type, 'open' => true];
    };
    $add('faculty', 'أستاذ الفقه المقارن وأصوله', 'sharia', 'تدريس الفقه المقارن وأصول الفقه والقواعد الفقهية لمرحلتي البكالوريوس والدراسات العليا.');
    $add('faculty', 'أستاذ القضاء والسياسة الشرعية', 'sharia', 'تدريس مقررات قسم الأحوال الشخصية: فقه القضاء والسياسة الشرعية وأحكام الأسرة.');
    $add('faculty', 'أستاذ الاقتصاد الإسلامي', 'sharia', 'تدريس فقه المعاملات والاقتصاد الإسلامي في برنامجي الماجستير والدكتوراه.');
    $add('faculty', 'أستاذ الدعوة والثقافة الإسلامية', 'dawah', 'تدريس مقررات قسم التوجيه والإرشاد ودبلومي تأهيل الدعاة والمعرفين بالإسلام.');
    $add('faculty', 'أستاذة اللغة العربية وطرق تدريسها', 'education', 'تدريس النحو والصرف والبلاغة وطرق التدريس في كلية التربية للبنات.');
    $add('lecturer', 'معلم اللغة العربية للناطقين بغيرها', 'arabic-institute', 'التدريس في مراحل المعهد من التمهيدي إلى المتقدم بنظام الساعات.');
    $add('super', 'مشرف رسائل ماجستير ودكتوراه', '', 'الإشراف العلمي على الرسائل في التخصصات الشرعية والعربية والتربوية.');
    $add('admin', 'منسق القبول والتسجيل', '', 'متابعة طلبات الالتحاق والتواصل مع المتقدمين وتنظيم الملفات.', 'حضوري / عن بُعد');
    $add('tech', 'مصمم تعليمي ومنتج محتوى رقمي', '', 'تصميم المقررات الإلكترونية وإنتاج المحاضرات والمواد التفاعلية.');
    return $P;
}

function uwciv_jobs_positions()
{
    $f = uwciv_jobs_dir() . '/positions.json';
    if (is_file($f)) { $j = json_decode((string) @file_get_contents($f), true); if (is_array($j)) return $j; }
    return uwciv_jobs_seed_positions();
}
function uwciv_jobs_save_positions(array $p) { return @file_put_contents(uwciv_jobs_dir() . '/positions.json', json_encode(array_values($p), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) !== false; }

function uwciv_jobs_apps()
{
    $f = uwciv_jobs_dir() . '/applications.json';
    if (is_file($f)) { $j = json_decode((string) @file_get_contents($f), true); if (is_array($j)) return $j; }
    return [];
}
function uwciv_jobs_save_apps(array $a) { return @file_put_contents(uwciv_jobs_dir() . '/applications.json', json_encode(array_values($a), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) !== false; }

function uwciv_jobs_new_ref(array $apps)
{
    $n = count($apps) + 1;
    do { $ref = 'JOB-' . date('y') . '-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT) . chr(65 + random_int(0, 25)); $n++; }
    while (in_array($ref, array_column($apps, 'ref'), true));
    return $ref;
}
