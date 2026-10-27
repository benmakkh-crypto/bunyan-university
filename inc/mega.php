<?php
/*
 * ✏️ القائمة العملاقة — ثلاثة مستويات لكل عنوان رئيسي
 * لكل عنوان رئيسي: [وصف قصير, أقسام[], بطاقة دعوة]
 * القسم: [العنوان, الأيقونة, الوصف, عناصر[]]
 * العنصر: [العنوان, الرابط, الأيقونة, الوصف]
 * المفتاح هو مفتاح العنوان في uwciv_menu()
 */
function uwciv_mega()
{
    static $M = null; if ($M !== null) return $M;
    $i = function ($k) { return '/info.php?p=' . $k; };
    $r = function ($id) { return '/regulations-view.php?d=' . $id; };

    /* الكليات حسب المجموعات — تُولَّد من دليل الكليات */
    global $UWCIV_COLLEGES, $UWCIV_GROUPS;
    if (empty($UWCIV_COLLEGES) && !function_exists('uwciv_college_url')) require __DIR__ . '/colleges.php';
    $UWCIV_GROUPS = $UWCIV_GROUPS ?: []; $UWCIV_COLLEGES = $UWCIV_COLLEGES ?: [];
    $colSecs = [];
    foreach ($UWCIV_GROUPS as $gk => $g) {
        $items = [];
        foreach ($UWCIV_COLLEGES as $slug => $c) if (($c['g'] ?? '') === $gk) $items[] = [$c['n'], '/college/' . $slug, $c['i'], $c['tag'] ?? ''];
        if ($items) $colSecs[] = [$g[0], $g[1], count($items) . ' كليات بأقسامها وبرامجها', $items];
    }

    $M = [
    'about' => ['هوية الجامعة ورسالتها وأنظمتها، ومكانها في العالم.', [
        ['تعرّف على الجامعة', 'fas fa-university', 'من نحن ورؤيتنا ونظامنا التعليمي', [
            ['من نحن', $i('about_biu'), 'fas fa-info-circle', 'النشأة والرسالة والقيم'],
            ['نظام الدراسة', $i('study_system'), 'fas fa-graduation-cap', 'نظام هجين بالساعات المعتمدة'],
            ['المنح الدراسية', $i('scholarships'), 'fas fa-hand-holding-heart', 'منح شاملة للمتميزين'],
            ['الخطة الاستراتيجية', '/stratj.php', 'fas fa-chess', 'الأهداف والمؤشرات حتى 2030'],
            ['الهيكل التنظيمي', $i('org_chart'), 'fas fa-sitemap', 'الكليات والأقسام والمجالس'],
        ]],
        ['الانتشار والفروع', 'fas fa-globe-asia', 'مقر الجامعة وحضورها الدولي', [
            ['المقر الرئيسي', $i('hq'), 'fas fa-map-marker-alt', 'ساجيرا، ليباك — بانتن، إندونيسيا'],
            ['الاتفاقيات الدولية', $i('agreements'), 'fas fa-handshake', 'أكثر من 50 اتفاقية وشراكة'],
            ['الفروع والمراكز', $i('branches'), 'fas fa-code-branch', 'فروع الجامعة ومكاتبها'],
            ['تواصل معنا', '/contact.php', 'fas fa-phone-alt', 'الهاتف والبريد والخريطة'],
        ]],
        ['الأنظمة واللوائح', 'fas fa-gavel', 'كل ما يحكم العمل في الجامعة', [
            ['دليل اللوائح والأنظمة', '/regulations.php', 'fas fa-folder-open', 'الوثائق والأدلة والصلاحيات'],
            ['النظام الأساسي', $r('statute'), 'fas fa-landmark', 'هوية الجامعة وهيكلها الحاكم'],
            ['لائحة الدراسة والاختبارات', $r('study'), 'fas fa-clipboard-check', 'الساعات والتقديرات والاختبارات'],
            ['حقوق الطالب وواجباته', $r('rights'), 'fas fa-user-shield', 'ما لك وما عليك'],
            ['مصفوفة الصلاحيات', '/regulations.php#powers', 'fas fa-project-diagram', 'من أعلى الهرم إلى آخره'],
        ]],
        ['مجتمع الجامعة', 'fas fa-users', 'الخريجون والعاملون والأعضاء', [
            ['وحدة الخريجين', '/grad.php', 'fas fa-user-graduate', 'أخبار ووثائق وخدمات الخريجين'],
            ['الوظائف', '/jobs.php', 'fas fa-briefcase', 'انضم إلى فريق الجامعة'],
            ['أعضاء الجامعة', '/teacher/index.php', 'fas fa-id-badge', 'المجالس والقيادات والهيئة'],
            ['الأسئلة الشائعة', '/ask.php', 'far fa-question-circle', 'إجابات سريعة لأكثر الأسئلة'],
        ]],
    ], ['ابدأ رحلتك معنا', 'التسجيل مفتوح لكل المراحل — حضوريًا وعن بُعد.', '/apply', 'سجّل الآن', 'fas fa-user-plus']],

    'admin' => ['القيادات والمجالس والوحدات الإدارية من أعلى الهرم إلى آخره.', [
        ['المجالس العليا', 'fas fa-landmark', 'جهات القرار والحوكمة', [
            ['مجلس الجامعة', $i('council'), 'fas fa-crown', 'أعلى هيئة إدارية في الجامعة'],
            ['المجلس العلمي', $i('scicouncil'), 'fas fa-microscope', 'الشؤون الأكاديمية والبحثية'],
            ['مصفوفة الصلاحيات', '/regulations.php#powers', 'fas fa-project-diagram', 'صلاحيات كل جهة'],
        ]],
        ['القيادة التنفيذية', 'fas fa-user-tie', 'رئاسة الجامعة ونوابها', [
            ['رئيس الجامعة', $i('president'), 'fas fa-user-tie', 'كلمة الرئيس ومهامه'],
            ['نائب الرئيس', $i('vice'), 'fas fa-user-friends', 'الشؤون الأكاديمية'],
            ['وكيل الجامعة', $i('vicedep'), 'fas fa-user-cog', 'الشؤون التنفيذية'],
        ]],
        ['الإدارات المساندة', 'fas fa-cogs', 'الخدمات الإدارية والمالية', [
            ['الشؤون الإدارية', $i('adminaff'), 'fas fa-briefcase', 'الموارد البشرية والخدمات'],
            ['الشؤون المالية', $i('finance'), 'fas fa-coins', 'التخطيط والرقابة المالية'],
            ['لجنة التعيينات والترقيات', $i('promo'), 'fas fa-award', 'ترقيات أعضاء هيئة التدريس'],
            ['الوحدات المساندة', $i('support_u'), 'fas fa-hands-helping', 'الجودة والتقنية والقبول'],
        ]],
        ['التواصل مع الإدارة', 'fas fa-envelope-open-text', 'أوصل صوتك للمسؤول', [
            ['طلب أو شكوى لرئيس الجامعة', '/form2.php', 'fas fa-envelope', 'تصل مباشرة لمكتب الرئيس'],
            ['الدعم الفني', '/form1.php', 'fas fa-life-ring', 'مشكلة في الموقع أو حسابك'],
            ['لائحة التظلمات', $r('grievances'), 'fas fa-balance-scale', 'خطوات التظلم ومدده'],
        ]],
    ], ['أعضاء الجامعة', 'تعرّف على القيادات والمجالس وهيئة التدريس.', '/teacher/index.php', 'عرض الأعضاء', 'fas fa-users']],

    'colleges_m' => ['كلية الشريعة، وكلية الدعوة، وكلية التربية للبنات، ومعهد تعليم اللغة العربية.', array_merge([
        ['المراحل الدراسية', 'fas fa-layer-group', 'من الدبلوم إلى الدكتوراه', [
            ['دليل الكليات', '/colleges.php', 'fas fa-th', 'كل الكليات والأقسام'],
            ['الدبلوم', '/depl.php', 'fas fa-seedling', 'برامج تأهيلية قصيرة'],
            ['البكالوريوس', '/depm.php', 'fas fa-book-open', 'الدرجة الجامعية الأولى'],
            ['الماجستير', '/depmst.php', 'fas fa-feather-alt', 'مقررات ورسالة علمية'],
            ['الدكتوراه', '/depdec.php', 'fas fa-crown', 'أعلى الدرجات العلمية'],
        ]]], $colSecs, [
        ['الأقسام والدبلومات', 'fas fa-sitemap', 'التفاصيل الكاملة لكل برنامج', [
            ['برنامج الشريعة', $i('sharia_prog'), 'fas fa-book-open', 'صناعة العلماء — البكالوريوس'],
            ['قسم الأحوال الشخصية', $i('personal_status'), 'fas fa-gavel', 'القضاء والسياسة الشرعية'],
            ['قسم التوجيه والإرشاد', $i('guidance'), 'fas fa-compass', 'الدعوة والثقافة الإسلامية'],
            ['دبلوم تأهيل الدعاة', $i('preachers_dip'), 'fas fa-microphone-alt', 'عن بُعد في أربعة مستويات'],
            ['دبلوم تأهيل المعرفين بالإسلام', $i('introducers_dip'), 'fas fa-globe-asia', 'فصلان و60 ساعة'],
            ['قسم اللغة العربية', $i('arabic_dept'), 'fas fa-pen-nib', 'كلية التربية للبنات'],
        ]],
        ['المحاضرات والخطط', 'fas fa-chalkboard-teacher', 'المحتوى الدراسي لكل مرحلة', [
            ['محاضرات البكالوريوس', '/depmlact.php', 'fas fa-play-circle', 'مسجّلة ومباشرة'],
            ['محاضرات الماجستير', '/depmstlact.php', 'fas fa-play-circle', 'حلقات بحث ومقررات'],
            ['محاضرات الدكتوراه', '/declact.php', 'fas fa-play-circle', 'مقررات منهجية'],
            ['الخطط الدراسية', $i('plans'), 'fas fa-list-alt', 'المقررات والساعات'],
            ['أعضاء هيئة التدريس', '/teacher/index.php', 'fas fa-chalkboard-teacher', 'أساتذة الكليات'],
            ['العمادات', $i('deanships'), 'fas fa-university', 'عمادات الجامعة'],
        ]],
        ['المنصة التعليمية', 'fas fa-laptop', 'خدمات الطالب الإلكترونية', [
            ['المنصة التعليمية', '/education.php', 'fas fa-desktop', 'البوابة الإلكترونية'],
            ['الامتحانات الإلكترونية', '/onlineexam/index.php', 'fas fa-laptop-code', 'امتحانات مؤقّتة ونتائج فورية'],
            ['الشهادة الإلكترونية', '/de/des.php', 'fas fa-certificate', 'استخراج الشهادات'],
            ['الدورات', '/courses/courses.php', 'fas fa-chalkboard', 'دورات قصيرة معتمدة'],
            ['التقويم الدراسي', '/calendar.php', 'far fa-calendar-alt', 'مواعيد العام الجامعي'],
        ]],
    ]), ['سجّل في كليتك', 'اختر الكلية والمرحلة وقدّم طلبك في 5 خطوات.', '/apply', 'قدّم الآن', 'fas fa-user-plus']],

    'centers' => ['مراكز علمية وخدمية تخدم الطلاب والمجتمع.', [
        ['المراكز العلمية', 'fas fa-flask', 'البحث والتدريب واللغات', [
            ['معهد البحوث والاستشارات', '/research.php', 'fas fa-microscope', 'خدمات البحث والنشر'],
            ['مركز التدريب', $i('c_train'), 'fas fa-chalkboard-teacher', 'دورات تأهيلية ومهنية'],
            ['مركز اللغات', $i('c_lang'), 'fas fa-language', 'العربية والإنجليزية ولغات أخرى'],
            ['مركز المخطوطات', $i('c_manus'), 'fas fa-scroll', 'حفظ التراث وتحقيقه'],
        ]],
        ['المركز الإعلامي', 'fas fa-broadcast-tower', 'أخبار الجامعة وصورها', [
            ['أخبار الجامعة', '/news.php', 'far fa-newspaper', 'آخر المستجدات'],
            ['الفعاليات', '/events.php', 'far fa-calendar-check', 'الندوات والمؤتمرات'],
            ['معرض الصور', '/images.php', 'far fa-images', 'لقطات من حياة الجامعة'],
            ['حفلات التخرج', '/party.php', 'fas fa-graduation-cap', 'احتفالات الخريجين'],
        ]],
        ['خدمة المجتمع', 'fas fa-hands-helping', 'رسالة الجامعة خارج أسوارها', [
            ['مركز الدعوة', $i('c_dawah'), 'fas fa-mosque', 'البرامج الدعوية والتوعوية'],
            ['وحدة الخريجين', '/grad.php', 'fas fa-user-graduate', 'شبكة الخريجين'],
            ['الوظائف', '/jobs.php', 'fas fa-briefcase', 'فرص العمل بالجامعة'],
        ]],
    ], ['معهد البحوث', 'احجز خدمة بحثية أو استشارة علمية.', '/research.php', 'تعرّف على الخدمات', 'fas fa-microscope']],

    'library_m' => ['مصادر المعرفة الرقمية متاحة لك في أي وقت.', [
        ['المكتبة الرقمية', 'fas fa-book-reader', 'كتب ومراجع بصيغة PDF', [
            ['تصفّح المكتبة', '/librarysystem/index.php', 'fas fa-book', 'بحث فوري حسب القسم'],
            ['الكتب الشرعية', '/librarysystem/index.php?q=' . rawurlencode('الشريعة'), 'fas fa-quran', 'مراجع العلوم الشرعية'],
            ['الكتب العربية والإنسانية', '/librarysystem/index.php?q=' . rawurlencode('العربية'), 'fas fa-feather-alt', 'اللغة والأدب والتاريخ'],
        ]],
        ['الإنتاج العلمي', 'fas fa-file-alt', 'بحوث الجامعة ورسائلها', [
            ['رسائل الخريجين ووثائقهم', '/pdf_grad/index.php', 'fas fa-certificate', 'التحقق والتحميل'],
            ['مجلة الجامعة', '/journal.php', 'fas fa-newspaper', 'بحوث محكّمة'],
            ['معهد البحوث', '/research.php', 'fas fa-microscope', 'المشروعات البحثية'],
        ]],
        ['أدلة مفيدة', 'fas fa-compass', 'لتستفيد من المكتبة', [
            ['لائحة البحث العلمي', $r('research'), 'fas fa-balance-scale', 'أخلاقيات البحث والنشر'],
            ['التعليم الإلكتروني', $r('elearning'), 'fas fa-laptop', 'كيف تدرس عن بُعد'],
            ['الأسئلة الشائعة', '/ask.php', 'far fa-question-circle', 'إجابات سريعة'],
        ]],
    ], ['أضف إلى المعرفة', 'هل لديك كتاب أو بحث للمكتبة؟ راسلنا.', '/form2.php', 'تواصل معنا', 'fas fa-book-medical']],

    'journal_m' => ['دورية علمية محكّمة تنشر البحوث الأصيلة.', [
        ['عن المجلة', 'fas fa-newspaper', 'الهوية والمجالات', [
            ['صفحة المجلة', '/journal.php', 'fas fa-book-open', 'التعريف والأعداد'],
            ['مجالات النشر', '/journal.php#fields', 'fas fa-th-large', 'ثمانية مجالات علمية'],
            ['الأعداد المنشورة', '/journal.php#issues', 'fas fa-layer-group', 'أرشيف الأعداد'],
        ]],
        ['للباحثين', 'fas fa-pen-nib', 'من الإرسال إلى النشر', [
            ['رحلة النشر', '/journal.php#steps', 'fas fa-stream', 'خمس مراحل واضحة'],
            ['شروط النشر', '/journal.php#cond', 'fas fa-clipboard-list', 'المواصفات والضوابط'],
            ['أرسل بحثك', 'mailto:info@biuedu.com', 'fas fa-paper-plane', 'عبر البريد مباشرة'],
            ['أخلاقيات البحث', $r('research'), 'fas fa-balance-scale', 'لائحة البحث العلمي'],
        ]],
    ], ['انشر بحثك', 'تحكيم سري مزدوج وقرار خلال أسابيع.', '/journal.php', 'ابدأ الآن', 'fas fa-paper-plane']],

    'calendar_m' => ['مواعيد العام الجامعي في مكان واحد.', [
        ['التقويم الدراسي', 'far fa-calendar-alt', 'الفصول والمواعيد', [
            ['التقويم الكامل', '/calendar.php', 'far fa-calendar', 'كل مواعيد العام'],
            ['مواعيد التسجيل', '/calendar.php#reg', 'fas fa-user-plus', 'فتح القبول وإغلاقه'],
            ['مواعيد الاختبارات', '/calendar.php#exam', 'fas fa-clipboard-check', 'النهائية والفصلية'],
            ['الإجازات', '/calendar.php#holiday', 'fas fa-umbrella-beach', 'العطل الرسمية'],
        ]],
        ['خدمات مرتبطة', 'fas fa-concierge-bell', 'استعد لكل موعد', [
            ['الامتحانات الإلكترونية', '/onlineexam/index.php', 'fas fa-laptop-code', 'أدِّ امتحانك عن بُعد'],
            ['سجّل معنا', '/apply', 'fas fa-edit', 'طلب الالتحاق'],
            ['لائحة الدراسة والاختبارات', $r('study'), 'fas fa-gavel', 'الأنظمة المنظِّمة'],
        ]],
    ], ['لا يفوتك موعد', 'أضف المواعيد إلى تقويم جوالك بضغطة.', '/calendar.php', 'افتح التقويم', 'far fa-calendar-plus']],

    'accred' => ['الجودة والاعتماد الأكاديمي ضمان لقيمة شهادتك.', [
        ['الاعتماد', 'fas fa-award', 'الاعترافات والشراكات', [
            ['الاعتماد والاعتراف', $i('accred'), 'fas fa-award', 'جهات الاعتماد'],
            ['التحقق من الشهادات', '/pdf_grad/index.php', 'fas fa-shield-alt', 'تحقق من وثيقة تخرج'],
            ['الشهادة الإلكترونية', '/de/des.php', 'fas fa-certificate', 'الوثائق الرقمية'],
        ]],
        ['الجودة', 'fas fa-check-double', 'معايير وأدلة', [
            ['دليل الجودة والاعتماد', $r('quality'), 'fas fa-book', 'معايير الجودة'],
            ['الخطة الاستراتيجية', '/stratj.php', 'fas fa-chess', 'مؤشرات الأداء'],
            ['سياسة الخصوصية', $r('privacy'), 'fas fa-user-lock', 'حماية بياناتك'],
        ]],
    ], ['تحقّق من شهادة', 'برقم المرجع ورقم الهوية خلال ثوانٍ.', '/pdf_grad/index.php', 'تحقق الآن', 'fas fa-shield-alt']],

    'gallery' => ['لحظات من حياة الجامعة بالصور.', [
        ['المعارض', 'far fa-images', 'الصور والفعاليات', [
            ['معرض الصور', '/images.php', 'far fa-image', 'كل الألبومات'],
            ['الفعاليات', '/events.php', 'far fa-calendar-check', 'ندوات ولقاءات'],
            ['حفلات التخرج', '/party.php', 'fas fa-graduation-cap', 'فرحة الخريجين'],
        ]],
        ['الأخبار', 'far fa-newspaper', 'آخر المستجدات', [
            ['أخبار الجامعة', '/news.php', 'far fa-newspaper', 'المستجدات أولًا بأول'],
            ['أخبار الخريجين', '/grad.php', 'fas fa-user-graduate', 'قصص نجاح'],
        ]],
    ], ['تابعنا', 'أحدث الصور والأخبار على صفحات الجامعة.', '/news.php', 'آخر الأخبار', 'far fa-newspaper']],

    'contact' => ['كل طرق الوصول إلينا — نرد عليك في أسرع وقت.', [
        ['تواصل مباشر', 'fas fa-headset', 'اتصال وواتساب وبريد', [
            ['صفحة التواصل', '/contact.php', 'fas fa-address-book', 'الهاتف والبريد والخريطة'],
            ['واتساب', 'https://wa.me/' . (uwciv_contact()['digits'] ?? ''), 'fab fa-whatsapp', 'محادثة فورية'],
            ['البريد الإلكتروني', 'mailto:info@biuedu.com', 'far fa-envelope', 'info@biuedu.com'],
        ]],
        ['النماذج', 'fas fa-file-signature', 'أرسل طلبك برقم متابعة', [
            ['الدعم الفني', '/form1.php', 'fas fa-life-ring', 'مشكلة تقنية أو حساب'],
            ['طلب أو شكوى لرئيس الجامعة', '/form2.php', 'fas fa-envelope-open-text', 'رسالة مباشرة للرئيس'],
            ['الأسئلة الشائعة', '/ask.php', 'far fa-question-circle', 'قد تجد جوابك هنا'],
        ]],
        ['روابط سريعة', 'fas fa-bolt', 'الأكثر استخدامًا', [
            ['سجّل معنا', '/apply', 'fas fa-user-plus', 'طلب الالتحاق'],
            ['تسجيل الدخول', '/syslog/index.php', 'fas fa-sign-in-alt', 'بوابة الطالب'],
            ['تطبيق الجوال', '/app.php', 'fas fa-mobile-alt', 'ثبّت التطبيق'],
            ['الروابط المختصرة', '/links.php', 'fas fa-link', 'كل روابط الموقع'],
        ]],
    ], ['نحن هنا لخدمتك', 'تواصل معنا عبر واتساب في أي وقت.', 'https://wa.me/' . (uwciv_contact()['digits'] ?? ''), 'محادثة واتساب', 'fab fa-whatsapp']],
    ];
    return $M;
}

/* ===== شجرة القائمة الكاملة (الافتراضي + ما يعدّله المشرف من لوحة «القائمة الرئيسية») ===== */
function uwciv_menu_file() { return dirname(__DIR__) . '/_private/menu.json'; }
function uwciv_menu_default()
{
    global $UWCIV_T;
    $M = uwciv_mega(); $out = [];
    foreach (uwciv_menu() as $m) {
        $k = $m[0]; $it = ['key' => $k, 'label' => $UWCIV_T[$k][0] ?? $k, 'url' => $m[1], 'desc' => '', 'hidden' => false, 'sections' => [], 'cta' => null];
        if (!empty($M[$k])) {
            $it['desc'] = $M[$k][0];
            foreach ($M[$k][1] as $sec) {
                $S = ['t' => $sec[0], 'i' => $sec[1], 'd' => $sec[2], 'items' => []];
                foreach ($sec[3] as $x) $S['items'][] = ['t' => $x[0], 'u' => $x[1], 'i' => $x[2], 'd' => $x[3]];
                $it['sections'][] = $S;
            }
            $c = $M[$k][2]; $it['cta'] = ['t' => $c[0], 'd' => $c[1], 'u' => $c[2], 'b' => $c[3], 'i' => $c[4]];
        }
        $out[] = $it;
    }
    return $out;
}
function uwciv_menu_tree()
{
    static $T = null; if ($T !== null) return $T;
    $f = uwciv_menu_file();
    if (is_file($f)) { $d = json_decode((string) file_get_contents($f), true); if (is_array($d) && $d) return $T = $d; }
    return $T = uwciv_menu_default();
}
function uwciv_icon_fix($c)
{
    $c = trim((string) $c);
    if (!preg_match('/^(fas|far|fab) fa-[a-z0-9-]+$/', $c)) $c = preg_match('/^fa-[a-z0-9-]+$/', $c) ? 'fas ' . $c : 'fas fa-circle';
    return $c;
}
function uwciv_url_ok($u)
{
    $u = trim((string) $u);
    return ($u === '#' || preg_match('~^(/[^\s]*|https?://[^\s]+|mailto:[^\s]+|tel:[+\d]+)$~i', $u)) ? $u : '#';
}

/* رسم القائمة العملاقة لعنصر من الشجرة */
function uwciv_render_mega_item(array $it, $label)
{
    if (empty($it['sections'])) return '';
    $e = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
    $id = 'mg-' . preg_replace('/[^a-z0-9_]/', '', (string) $it['key']);
    $h = '<div class="u-mega" id="' . $id . '"><div class="u-wrap u-mega-in' . (empty($it['cta']) ? ' no-cta' : '') . '">';
    $h .= '<div class="u-mega-tabs" role="tablist"><div class="u-mega-head"><b>' . $e($label) . '</b><span>' . $e($it['desc']) . '</span></div>';
    foreach ($it['sections'] as $n => $s) $h .= '<button type="button" class="u-mega-tab' . ($n ? '' : ' on') . '" data-i="' . $n . '"><i class="' . $e(uwciv_icon_fix($s['i'])) . '"></i><span><b>' . $e($s['t']) . '</b><small>' . $e($s['d']) . '</small></span><i class="fas fa-chevron-left u-mt-ch"></i></button>';
    $h .= '</div><div class="u-mega-panes">';
    foreach ($it['sections'] as $n => $s) {
        $h .= '<div class="u-mega-pane' . ($n ? '' : ' on') . '" data-i="' . $n . '"><div class="u-mega-ph"><i class="' . $e(uwciv_icon_fix($s['i'])) . '"></i><b>' . $e($s['t']) . '</b><small>' . $e($s['d']) . '</small></div><div class="u-mega-grid">';
        foreach ($s['items'] as $x) {
            $u = uwciv_url_ok($x['u']); $ext = preg_match('~^(https?:|mailto:)~', $u) ? ' target="_blank" rel="noopener"' : '';
            $h .= '<a class="u-mega-link" href="' . $e($u) . '"' . $ext . '><i class="' . $e(uwciv_icon_fix($x['i'])) . '"></i><span><b>' . $e($x['t']) . '</b><small>' . $e($x['d']) . '</small></span></a>';
        }
        $h .= '</div></div>';
    }
    $h .= '</div>';
    if (!empty($it['cta']) && !empty($it['cta']['t'])) { $c = $it['cta']; $h .= '<a class="u-mega-cta" href="' . $e(uwciv_url_ok($c['u'])) . '"><i class="' . $e(uwciv_icon_fix($c['i'])) . '"></i><b>' . $e($c['t']) . '</b><span>' . $e($c['d']) . '</span><em>' . $e($c['b']) . ' <i class="fas fa-arrow-left"></i></em></a>'; }
    return $h . '</div></div>';
}
/* توافق مع الاستدعاء القديم */
function uwciv_render_mega($key, $label)
{
    foreach (uwciv_menu_tree() as $it) if ($it['key'] === $key) return uwciv_render_mega_item($it, $label);
    return '';
}
