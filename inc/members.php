<?php
/* أعضاء الجامعة — البيانات تُحفظ في _private/members.json وتُعدَّل من /admin/members.php */
require_once __DIR__ . '/colleges.php';

function uwciv_member_groups()
{
    return [
        'board'    => ['مجلس الأمناء',        'fa-landmark',          'الجهة الحاكمة التي ترسم السياسات العامة وتحفظ رسالة الجامعة وهويتها.'],
        'faculty'  => ['هيئة التدريس',        'fa-chalkboard-teacher','نخبة من العلماء والأساتذة المتخصصين في كليات الجامعة.'],
        'deans'    => ['عمداء الكليات',       'fa-university',        'قيادات الكليات الأكاديمية والإشراف على أقسامها وبرامجها.'],
        'advisors' => ['المستشارون',          'fa-chess-queen',       'خبراء يقدمون المشورة العلمية والاستراتيجية لقيادة الجامعة.'],
        'legal'    => ['المستشارون القانونيون','fa-balance-scale',     'يضمنون توافق أعمال الجامعة مع الأنظمة واللوائح المعمول بها.'],
        'admin'    => ['الإداريون',            'fa-briefcase',         'فريق العمل الذي يدير شؤون الجامعة اليومية ويخدم الطلاب.'],
        'honorary' => ['الأعضاء الفخريون',    'fa-award',             'شخصيات علمية ومجتمعية كريمة تُشرّف الجامعة بعضويتها.'],
    ];
}

/* المقاعد الافتراضية قبل إدخال الأسماء الحقيقية */
function uwciv_members_seed()
{
    $m = [];
    $slot = function ($g, $title, $spec = '', $lead = false) use (&$m) {
        $m[] = ['id' => substr(md5($g . $title . count($m)), 0, 10), 'g' => $g, 'name' => '', 'title' => $title, 'spec' => $spec, 'bio' => '', 'photo' => '', 'email' => '', 'links' => [], 'lead' => $lead];
    };
    $named = function ($g, $name, $title, $spec = '', $lead = false) use (&$m) {
        $m[] = ['id' => substr(md5($g . $name . count($m)), 0, 10), 'g' => $g, 'name' => $name, 'title' => $title, 'spec' => $spec, 'bio' => '', 'photo' => '', 'email' => '', 'links' => [], 'lead' => $lead];
    };
    $named('board', 'د. عبدالله بن محمد عبدالله آل يحيى الغامدي', 'رئيس مجلس الجامعة', 'أعلى هيئة إدارية في الجامعة', true);
    $named('board', 'د. سيف الله', 'رئيس الجامعة', 'عضو بحكم المنصب');
    foreach (['نائب الرئيس للشؤون الأكاديمية', 'نائب الرئيس للشؤون الإدارية والمالية', 'عميد الدراسات العليا', 'عميد شؤون الطلاب', 'عميد القبول والتسجيل'] as $x) $slot('board', 'عضو مجلس الجامعة', $x);
    /* أعضاء هيئة التدريس كما في الموقع الرسمي للجامعة */
    foreach (['أ. يوسف مولانا', 'د. محمود الشيخ', 'أ. غالب العدني', 'د. علاء أحمد محمد لبده', 'د. زين', 'د. سعيد رمضان', 'أ. عبد الغفور', 'ش. حمادة أبو الفتوح', 'أ. محمد جابر', 'د. عبد الحميد', 'د. أكرم عبد الباري صويلح', 'أ. سيد أنصاري', 'أ. سعيد الجابري', 'د. أحمد الجيلي', 'د. مصعب نبيل', 'أ. محمد شبراوي', 'أ. سوهيري', 'أ. سبكي الفقيه'] as $x) $named('faculty', $x, 'عضو هيئة التدريس');
    foreach (['د. مايسة السنهوري', 'أ. ندى فيصل', 'أ. حنان أحمد', 'د. هدى عبد الرحمن', 'أ. سمر الحناوي', 'أ. نور', 'د. إيمان الشميري'] as $x) $named('faculty', $x, 'عضو هيئة التدريس');
    foreach ($GLOBALS['UWCIV_COLLEGES'] as $slug => $c) $slot('deans', 'عميد ' . $c['n'], $c['tag']);
    foreach (['الاستشارات الأكاديمية', 'الجودة والاعتماد', 'التحول الرقمي', 'الشؤون الشرعية'] as $x) $slot('advisors', 'مستشار ' . $x);
    foreach (['المستشار القانوني العام', 'مستشار الأنظمة واللوائح', 'مستشار العقود والشراكات'] as $x) $slot('legal', $x);
    foreach (['مدير عام الجامعة', 'مدير القبول والتسجيل', 'مدير الشؤون المالية', 'مدير الموارد البشرية', 'مدير التعلم الإلكتروني', 'مدير العلاقات العامة والإعلام'] as $x) $slot('admin', $x);
    foreach (['عضو فخري — الشؤون العلمية', 'عضو فخري — العمل الخيري والوقفي', 'عضو فخري — الشراكات الدولية'] as $x) $slot('honorary', $x);
    return $m;
}

function uwciv_members_path() { return dirname(__DIR__) . '/_private/members.json'; }
function uwciv_members()
{
    $f = uwciv_members_path();
    if (is_file($f)) { $j = json_decode((string) @file_get_contents($f), true); if (is_array($j)) return $j; }
    return uwciv_members_seed();
}
function uwciv_members_save(array $m)
{
    $f = uwciv_members_path();
    if (!is_dir(dirname($f))) @mkdir(dirname($f), 0755, true);
    return @file_put_contents($f, json_encode(array_values($m), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) !== false;
}
function uwciv_member_link_defs()
{
    return ['website' => ['الموقع', 'fas fa-globe'], 'scholar' => ['Google Scholar', 'fas fa-graduation-cap'], 'linkedin' => ['LinkedIn', 'fab fa-linkedin-in'], 'x' => ['X', 'fab fa-twitter'], 'facebook' => ['Facebook', 'fab fa-facebook-f'], 'youtube' => ['YouTube', 'fab fa-youtube']];
}
