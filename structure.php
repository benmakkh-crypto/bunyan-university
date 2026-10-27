<?php
require_once __DIR__ . '/inc/shell.php';
require_once __DIR__ . '/inc/colleges.php';

/* ✏️ محتوى الهيكل الأكاديمي — عدّل النصوص هنا */
$PILLARS = [
  ['fa-book-open',    'الأصالة العلمية',     'علوم شرعية وإنسانية راسخة ومناهج موثوقة.'],
  ['fa-microchip',    'المستقبل الرقمي',     'ذكاء اصطناعي، بيانات، وتعليم رقمي.'],
  ['fa-flask',        'البحث والابتكار',     'مراكز بحثية، مشاريع تطبيقية، ونشر علمي.'],
  ['fa-globe-asia',   'العالمية والانفتاح',  'شراكات دولية وبرامج متعددة اللغات.'],
];
$BRANCHES = [
  ['fa-graduation-cap', 'نائب الرئيس للشؤون الأكاديمية', 'الكليات والبرامج والجودة'],
  ['fa-lightbulb',      'نائب الرئيس للبحث والتطوير',   'البحث والابتكار والشراكات'],
  ['fa-laptop-code',    'عمادة التعلم الرقمي',          'المنصة والتعليم الإلكتروني'],
  ['fa-building',       'الإدارة العامة',               'الموارد والمالية والتشغيل'],
];
/* ✏️ تفاصيل تظهر عند المرور على أي بطاقة (تُعدَّل هنا مؤقتًا إلى أن تُنقل للوحة المشرف) */
$INFO = [
  // الركائز
  'الأصالة العلمية' => ['مناهج مبنية على المصادر الأصيلة والكتب المعتمدة', 'أساتذة متخصصون بالسند والإجازة', 'ربط العلوم الشرعية بالعلوم الإنسانية', 'منهج وسطي معتدل'],
  'المستقبل الرقمي' => ['منصة تعليمية ومحاضرات مسجلة ومباشرة', 'امتحانات وشهادات إلكترونية موثقة', 'مقررات في الذكاء الاصطناعي وعلوم البيانات', 'مكتبة رقمية متاحة على مدار الساعة'],
  'البحث والابتكار' => ['معهد البحوث والاستشارات', 'المجلة العلمية المحكّمة', 'مشاريع تخرج تطبيقية تخدم المجتمع', 'تحقيق المخطوطات ونشرها'],
  'العالمية والانفتاح' => ['الدراسة بالعربية والإنجليزية والبنغالية والأردية', 'طلاب من دول متعددة', 'اتفاقيات تعاون مع جامعات ومؤسسات دولية', 'تعليم عن بُعد يصل لكل مكان'],
  // الشجرة
  'مجلس الأمناء / الجهة الحاكمة المختصة' => ['إقرار السياسات العامة والخطة الاستراتيجية', 'اعتماد الميزانية والهيكل التنظيمي', 'تعيين رئيس الجامعة ومتابعة أدائه', 'حماية رسالة الجامعة وهويتها'],
  'رئيس الجامعة' => ['قيادة الجامعة وتنفيذ قرارات مجلس الأمناء', 'رئاسة مجلس الجامعة', 'تمثيل الجامعة محليًا ودوليًا', 'الإشراف على النواب والعمادات والإدارات'],
  'المجالس والقيادات الأكاديمية والإدارية' => ['نواب الرئيس والعمداء ورؤساء الأقسام', 'المجالس المتخصصة السبعة', 'التنسيق بين الكليات والإدارات', 'رفع التوصيات لرئيس الجامعة'],
  'نائب الرئيس للشؤون الأكاديمية' => ['الإشراف على الكليات والأقسام', 'القبول والتسجيل والشؤون الأكاديمية', 'تطوير المناهج والخطط الدراسية', 'الجودة والاعتماد الأكاديمي'],
  'نائب الرئيس للبحث والتطوير' => ['معهد البحوث والاستشارات', 'المجلة العلمية والنشر', 'الدراسات العليا والبحث التطبيقي', 'الشراكات والاتفاقيات الدولية'],
  'عمادة التعلم الرقمي' => ['المنصة التعليمية والمحاضرات', 'نظام الامتحانات الإلكترونية', 'الشهادات الإلكترونية والتحقق منها', 'الدعم الفني للطلاب والأساتذة'],
  'الإدارة العامة' => ['الشؤون المالية والرسوم', 'الموارد البشرية وشؤون الموظفين', 'الشؤون الإدارية والتشغيل', 'العلاقات العامة والإعلام'],
  // المجالس
  'مجلس الجامعة' => ['يرأسه رئيس الجامعة ويضم النواب والعمداء', 'إقرار الخطط والبرامج واللوائح الداخلية', 'اعتماد منح الدرجات العلمية', 'يجتمع دوريًا مرة كل شهر على الأقل'],
  'المجلس العلمي أو مجلس الدراسات العليا' => ['ترقيات أعضاء هيئة التدريس', 'اعتماد خطط البحوث والرسائل', 'تشكيل لجان المناقشة', 'متابعة الإنتاج العلمي'],
  'مجلس الشؤون الأكاديمية والمناهج' => ['اعتماد الخطط الدراسية وتوصيف المقررات', 'مراجعة المناهج وتحديثها', 'تنظيم الجداول والاختبارات', 'معادلة المقررات والتحويل'],
  'مجلس الجودة والاعتماد الأكاديمي' => ['التقييم الذاتي للبرامج', 'متابعة مؤشرات الأداء', 'التحضير للاعتماد المؤسسي والبرامجي', 'قياس رضا الطلاب والخريجين'],
  'مجلس البحث العلمي والابتكار' => ['أولويات البحث العلمي في الجامعة', 'دعم المشاريع البحثية وتمويلها', 'أخلاقيات البحث وحماية الملكية الفكرية', 'تشجيع الابتكار وريادة الأعمال'],
  'مجلس الدراسات العليا والبحث التطبيقي' => ['برامج الدبلوم العالي والماجستير والدكتوراه', 'قبول طلاب الدراسات العليا', 'الإشراف على الرسائل العلمية', 'ربط البحوث باحتياجات المجتمع'],
  'مجالس الكليات والأقسام العلمية' => ['مجلس لكل كلية برئاسة العميد', 'مجلس لكل قسم برئاسة رئيس القسم', 'اقتراح الخطط وتوزيع التدريس', 'متابعة الطلاب ونتائجهم'],
  // الوحدات
  'القبول والتسجيل' => ['استقبال طلبات الالتحاق إلكترونيًا', 'التحقق من الوثائق والمؤهلات', 'التسجيل في المقررات كل فصل', 'التحويل والانسحاب وإعادة القيد'],
  'الشؤون الأكاديمية' => ['الخطط الدراسية لكل برنامج', 'الجداول والمحاضرات', 'الاختبارات ورصد الدرجات', 'إصدار النتائج والسجلات الأكاديمية'],
  'تطوير المناهج' => ['توصيف المقررات ونواتج التعلم', 'مراجعة المحتوى سنويًا', 'تطوير مواد تعليمية رقمية', 'الاستفادة من تجارب الجامعات المتميزة'],
  'الجودة والاعتماد' => ['التقييم الذاتي الدوري', 'مؤشرات الأداء الرئيسية', 'استبانات الطلاب والخريجين', 'خطط التحسين المستمر'],
  'الدراسات العليا' => ['قبول طلاب الماجستير والدكتوراه', 'تسجيل الخطط البحثية', 'تعيين المشرفين', 'تنظيم المناقشات العلمية'],
  'البحث العلمي' => ['دعم البحوث والمشاريع', 'النشر في المجلة العلمية', 'المؤتمرات والندوات', 'أخلاقيات البحث والتوثيق'],
  'التعلم الإلكتروني' => ['إدارة المنصة التعليمية', 'تسجيل المحاضرات ونشرها', 'الاختبارات الإلكترونية', 'تدريب الأساتذة على التعليم الرقمي'],
  'التدريب والتعليم المستمر' => ['دورات قصيرة ومهنية', 'برامج اللغات', 'شهادات حضور وإتمام', 'شراكات مع جهات التدريب'],
  'الإرشاد الأكاديمي' => ['مرشد أكاديمي لكل طالب', 'متابعة التقدم الدراسي', 'خطط علاجية للطلاب المتعثرين', 'التوجيه في اختيار التخصص'],
  'الشراكات الدولية' => ['اتفاقيات التعاون الأكاديمي', 'تبادل الأساتذة والطلاب', 'البحوث المشتركة', 'الاعتراف المتبادل بالشهادات'],
  // متطلبات عصر الذكاء الاصطناعي
  'ai:الثقافة الرقمية والذكاء الاصطناعي' => ['مقرر إلزامي لجميع الطلاب', 'أدوات الذكاء الاصطناعي في التعلم والبحث', 'التحقق من المعلومات والمصادر', 'سياسة الاستخدام المسؤول'],
  'ai:البحث العلمي' => ['مقرر مناهج البحث لجميع الطلاب', 'التوثيق والاقتباس العلمي', 'أخلاقيات النشر ومكافحة الانتحال', 'بحث تخرج في كل برنامج'],
  'ai:اللغات والتواصل' => ['مستويات في العربية والإنجليزية', 'البنغالية لخدمة البيئة المحلية', 'مهارات العرض والكتابة', 'التواصل بين الثقافات'],
  'ai:التدريب العملي' => ['مشاريع تطبيقية في كل برنامج', 'تدريب ميداني قبل التخرج', 'شراكات مع مؤسسات المجتمع', 'ملف إنجاز لكل طالب'],
  'ai:التعلم مدى الحياة' => ['دورات مفتوحة للخريجين', 'شهادات مهنية قصيرة', 'منصة الخريجين', 'تحديث المهارات باستمرار'],
  'ai:الجودة' => ['قياس نواتج التعلم', 'استبانات رضا الطلاب', 'متابعة أداء الخريجين', 'تقارير دورية للتحسين'],
  'ai:الشمول وإمكانية الوصول' => ['مواد رقمية متاحة دائمًا', 'دعم ذوي الاحتياجات الخاصة', 'مرونة في مواعيد الدراسة', 'رسوم ميسّرة ومنح'],
  // المسارات
  'التعليم الجامعي' => ['بكالوريوس في الشريعة والدعوة والتربية', 'نظام الساعات المعتمدة', 'حضوري وعن بُعد ومدمج'],
  'الدراسات العليا' => ['دبلوم عالٍ وماجستير ودكتوراه', 'مقررات منهجية ورسالة علمية', 'إشراف أكاديمي متخصص'],
  'التعليم المهني والمستمر' => ['دورات قصيرة وشهادات مهنية', 'برامج اللغات والمهارات', 'لا تمنح درجة علمية'],
  'التعليم الرقمي والعابر للحدود' => ['مقررات إلكترونية مفتوحة', 'برامج مدمجة مع شركاء دوليين', 'دراسة من أي دولة'],
  // المراحل
  'التأسيس والتحقق' => ['مراجعة الترخيص والمتطلبات النظامية', 'دراسة الاحتياج وسوق العمل', 'اعتماد الهيكل الحاكم والمجالس', 'تحديد الكليات الأولى'],
  'البناء والتطوير' => ['إعداد اللوائح الأكاديمية والإدارية', 'توصيف البرامج والمقررات', 'بناء الأنظمة الإلكترونية', 'استقطاب أعضاء هيئة التدريس'],
  'التهيئة والتشغيل' => ['اختبار الأنظمة تجريبيًا', 'تأهيل أعضاء هيئة التدريس', 'فتح القبول للدفعة الأولى', 'التحقق من الجاهزية الكاملة'],
  'التوسع اللاحق' => ['فتح برامج وكليات إضافية', 'تطوير الدراسات العليا', 'توسيع البحث العلمي', 'شراكات دولية جديدة'],
];
function s_info($k) {
  global $INFO; if (empty($INFO[$k])) return '';
  $h = '<div class="s-x"><ul>'; foreach ($INFO[$k] as $x) $h .= '<li>' . $x . '</li>'; return $h . '</ul></div>';
}

$COUNCILS = ['مجلس الجامعة', 'المجلس العلمي أو مجلس الدراسات العليا', 'مجلس الشؤون الأكاديمية والمناهج', 'مجلس الجودة والاعتماد الأكاديمي', 'مجلس البحث العلمي والابتكار', 'مجلس الدراسات العليا والبحث التطبيقي', 'مجالس الكليات والأقسام العلمية'];
$UNITS = [
  ['fa-user-plus',        'القبول والتسجيل',          'قبول الطلاب، الملفات، التسجيل والتحويل'],
  ['fa-tasks',            'الشؤون الأكاديمية',        'الخطط الدراسية، الجداول، الاختبارات والنتائج'],
  ['fa-pencil-ruler',     'تطوير المناهج',            'توصيف المقررات، نواتج التعلم، تحديث المحتوى'],
  ['fa-award',            'الجودة والاعتماد',         'التقييم الذاتي، مؤشرات الأداء، التحسين المستمر'],
  ['fa-user-graduate',    'الدراسات العليا',          'برامج الماجستير والدكتوراه وفق الاعتماد'],
  ['fa-microscope',       'البحث العلمي',             'المشاريع البحثية، النشر، أخلاقيات البحث'],
  ['fa-laptop',           'التعلم الإلكتروني',        'المحاضرات الرقمية، الاختبارات، إدارة المحتوى'],
  ['fa-certificate',      'التدريب والتعليم المستمر', 'الدورات المهنية والشهادات غير الدرجية'],
  ['fa-compass',          'الإرشاد الأكاديمي',        'متابعة تقدم الطالب ودعم تعثره'],
  ['fa-handshake',        'الشراكات الدولية',         'التبادل العلمي والاتفاقيات والتعاون البحثي'],
];
$TRACKS = [
  ['#12935a', 'fa-university',     'التعليم الجامعي',               'البكالوريوس، وفق متطلبات التخصص والجهة المنظمة.'],
  ['#2c5fd0', 'fa-user-graduate',  'الدراسات العليا',               'الدبلومات العليا والماجستير والدكتوراه، بحسب ما تسمح به التراخيص والاعتمادات.'],
  ['#c9962a', 'fa-tools',          'التعليم المهني والمستمر',       'دورات قصيرة، شهادات مهنية، وبرامج تطوير مهارات بالتعاون مع جهات التدريب.'],
  ['#9b1f45', 'fa-globe',          'التعليم الرقمي والعابر للحدود', 'مقررات إلكترونية، برامج مدمجة، وشراكات تعليمية دولية وفق الأنظمة المعمول بها.'],
];
$AI = [
  ['fa-robot',          'الثقافة الرقمية والذكاء الاصطناعي', 'استخدام مسؤول للتقنيات، والتحقق من المعلومات، وحماية البيانات.'],
  ['fa-search',         'البحث العلمي',                      'مهارات البحث والكتابة العلمية والتوثيق وأخلاقيات النشر.'],
  ['fa-comments',       'اللغات والتواصل',                   'تعزيز العربية والإنجليزية، مع توفير البنغالية لخدمة البيئة المحلية.'],
  ['fa-hard-hat',       'التدريب العملي',                    'ربط المقررات بمشاريع واقعية وتدريب ميداني مناسب.'],
  ['fa-infinity',       'التعلم مدى الحياة',                 'تنمية القدرة على التعلم المستمر وتحديث المهارات.'],
  ['fa-chart-pie',      'الجودة',                            'قياس نواتج التعلم ورضا الطلاب وأداء الخريجين بصورة دورية.'],
  ['fa-universal-access','الشمول وإمكانية الوصول',           'مراعاة اختلاف احتياجات الطلاب وإتاحة المواد التعليمية رقميًا.'],
];
$PHASES = [
  ['التأسيس والتحقق',   'الأشهر 1–5',            'مراجعة الترخيص، دراسة الاحتياج، اعتماد الهيكل الحاكم'],
  ['البناء والتطوير',   'الأشهر 6–10',           'إعداد اللوائح، توصيف البرامج، بناء الأنظمة الأكاديمية'],
  ['التهيئة والتشغيل',  'الأشهر 11–15',          'اختبار النظام، تأهيل أعضاء هيئة التدريس، التحقق من الجاهزية'],
  ['التوسع اللاحق',     'بعد استكمال المتطلبات', 'فتح برامج إضافية وتطوير الدراسات العليا والبحث العلمي'],
];
$FIRST = ['اعتماد الهيكل التنظيمي العام من الجهة المخولة.', 'تحديد الكليات التي ستبدأ بها الجامعة فعليًا وفق التمويل والكوادر والاحتياج المحلي.', 'اختيار البرامج الأولية وإعداد توصيف أكاديمي لكل برنامج.', 'وضع لائحة موحدة للقبول والتسجيل والاختبارات والتخرج.', 'إعداد دليل الجودة والاعتماد ومؤشرات الأداء.', 'ربط الهيكل الأكاديمي بمنظومة الجامعة الإلكترونية وقاعدة بياناتها باللغات العربية والإنجليزية والبنغالية.'];

uwciv_open('الهيكل الأكاديمي', t('about'), 'منظومة تجمع بين أصالة العلوم الشرعية، وجودة التعليم الجامعي، والتحول الرقمي، والبحث العلمي، واحتياجات سوق العمل — حضوريًا وإلكترونيًا ومدمجًا.');
?>
<style>
.s-wrap{--r:20px}
.s-sec{margin:0 0 56px}
.s-h{display:flex;align-items:center;gap:14px;margin:0 0 22px}
.s-h b{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;background:linear-gradient(145deg,var(--teal),var(--ink-3));color:#fff;font-size:18px;font-weight:900;box-shadow:0 10px 20px -8px rgba(6,33,15,.5);transform:rotate(-6deg)}
.s-h h2{margin:0;font-size:clamp(21px,2.6vw,28px);font-weight:900;color:var(--ink)}
.s-h p{margin:2px 0 0;color:var(--muted);font-size:15px}
.s-rule{background:linear-gradient(120deg,var(--ink-2),var(--teal));color:#fff;border-radius:var(--r);padding:24px 28px;display:flex;gap:18px;align-items:center;box-shadow:var(--sh-lift);margin:0 0 28px;position:relative;overflow:hidden}
.s-rule:after{content:"";position:absolute;inset:auto -60px -80px auto;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(217,178,74,.35),transparent 70%)}
.s-rule i{font-size:34px;color:var(--gold)}
.s-rule p{margin:0;font-size:17px;line-height:1.9}.s-rule b{color:var(--gold)}
.s-pillars{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.s-pil{background:#fff;border-radius:var(--r);padding:24px 18px;text-align:center;box-shadow:var(--sh-3d);transition:.35s;border-top:4px solid var(--gold)}
.s-pil:hover{transform:translateY(-8px) rotateX(6deg);box-shadow:var(--sh-lift)}
.s-pil i{font-size:30px;color:var(--teal);margin-bottom:10px}
.s-pil h3{font-size:18px;font-weight:800;margin:0 0 6px;color:var(--ink)}.s-pil p{margin:0;color:var(--muted);font-size:14.5px}
/* شجرة الهيكل */
.s-tree{display:flex;flex-direction:column;align-items:center;gap:0}
.s-node{background:#fff;border-radius:16px;padding:16px 26px;text-align:center;box-shadow:var(--sh-3d);min-width:260px;position:relative;z-index:1}
.s-node.top{background:linear-gradient(145deg,var(--ink-2),var(--ink-3));color:#fff}
.s-node.top small{color:#bfe0c9}
.s-node.pres{background:linear-gradient(145deg,var(--gold),var(--gold-2));color:#2a1f05}
.s-node b{display:block;font-size:17px;font-weight:900}.s-node small{font-size:13.5px;color:var(--muted)}
.s-node.pres small{color:#4a3a10}
.s-vl{width:3px;height:30px;background:linear-gradient(var(--gold),var(--teal))}
.s-branches{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;width:100%;position:relative;padding-top:30px}
.s-branches:before{content:"";position:absolute;top:0;right:12.5%;left:12.5%;height:3px;background:var(--teal);border-radius:3px}
.s-br{background:#fff;border-radius:16px;padding:20px 14px;text-align:center;box-shadow:var(--sh-3d);position:relative;transition:.3s}
.s-br:before{content:"";position:absolute;top:-30px;right:50%;width:3px;height:30px;background:var(--teal)}
.s-br:hover{transform:translateY(-6px);box-shadow:var(--sh-lift)}
.s-br i{width:52px;height:52px;border-radius:50%;display:inline-grid;place-items:center;background:var(--cyan-soft);color:var(--teal);font-size:21px;margin-bottom:8px}
.s-br b{display:block;font-size:15.5px;font-weight:800;color:var(--ink)}.s-br small{color:var(--muted);font-size:13.5px}
.s-councils{display:flex;flex-wrap:wrap;gap:10px;margin-top:22px;justify-content:center}
.s-councils{display:grid!important;grid-template-columns:repeat(4,1fr)}
.s-cn{background:#fff;border:1px solid #dbe9e0;border-radius:16px;padding:12px 14px;font-size:14.5px;color:var(--ink-3);box-shadow:var(--sh-1);display:flex;align-items:center;gap:4px;transition:.25s}
.s-cn:hover{border-color:var(--gold);box-shadow:var(--sh-lift)}
.s-cn b{font-weight:800}
.s-cn em{font-style:normal;display:inline-grid;place-items:center;width:24px;height:24px;border-radius:50%;background:var(--gold);color:#2a1f05;font-size:12px;margin-left:6px}
.s-foot{text-align:center;color:var(--muted);font-size:14px;margin-top:14px}
/* الكليات: بطاقات قلابة ثلاثية الأبعاد */
.s-cols{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
.s-gt{display:flex;align-items:center;gap:10px;font-size:18px;font-weight:900;color:var(--cc);margin:26px 0 14px}.s-gt i{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;background:var(--cc);color:#fff;font-size:15px}
.s-more{margin-top:auto;align-self:flex-start;background:var(--cc);color:#fff!important;border-radius:30px;padding:7px 16px;font-size:13.5px;font-weight:800;text-decoration:none!important}
.s-col{perspective:1100px;height:310px;cursor:pointer;outline:none}
.s-col-in{position:relative;width:100%;height:100%;transition:transform .7s cubic-bezier(.2,.8,.2,1);transform-style:preserve-3d}
.s-col:hover .s-col-in,.s-col:focus .s-col-in,.s-col.on .s-col-in{transform:rotateY(180deg)}
.s-face{position:absolute;inset:0;border-radius:var(--r);backface-visibility:hidden;-webkit-backface-visibility:hidden;padding:22px 18px;display:flex;flex-direction:column;box-shadow:var(--sh-3d)}
.s-front{background:linear-gradient(160deg,var(--cc),color-mix(in srgb,var(--cc) 55%,#000));color:#fff;justify-content:space-between;overflow:hidden}
.s-front:after{content:"";position:absolute;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.08);left:-50px;bottom:-60px}
.s-front .n{font-size:44px;font-weight:900;opacity:.25;line-height:1}
.s-front i{font-size:38px;margin:4px 0 12px}
.s-front h3{color:#fff;font-size:19px;font-weight:900;margin:0 0 6px}
.s-front p{margin:0;font-size:14px;opacity:.9}
.s-front .hint{font-size:12.5px;opacity:.75;margin-top:10px}
.s-back{background:#fff;transform:rotateY(180deg);border-top:5px solid var(--cc)}
.s-back h4{font-size:16px;font-weight:900;color:var(--cc);margin:0 0 10px}
.s-back ul{list-style:none;margin:0;padding:0}
.s-back li{padding:5px 0;border-bottom:1px dashed #e3ece6;font-size:14.5px;color:var(--text);display:flex;gap:8px;align-items:center}
.s-back li:before{content:"";width:8px;height:8px;border-radius:50%;background:var(--cc);flex:none}
/* الوحدات */
.s-units{display:grid;grid-template-columns:repeat(5,1fr);gap:14px}
.s-unit{background:#fff;border-radius:16px;padding:18px 14px;box-shadow:var(--sh-1);transition:.3s;border-bottom:3px solid transparent}
.s-unit:hover{transform:translateY(-5px);border-color:var(--gold);box-shadow:var(--sh-lift)}
.s-unit i{color:var(--teal);font-size:22px;margin-bottom:8px}
.s-unit b{display:block;font-size:15.5px;color:var(--ink);margin-bottom:4px}.s-unit small{color:var(--muted);font-size:13.5px;line-height:1.7}
/* المسارات */
.s-tracks{display:grid;grid-template-columns:repeat(4,1fr);gap:0;border-radius:var(--r);overflow:hidden;box-shadow:var(--sh-lift)}
.s-tr{background:var(--cc);color:#fff;padding:26px 20px;position:relative;clip-path:polygon(0 0,92% 0,100% 50%,92% 100%,0 100%,8% 50%)}
.s-tr:first-child{clip-path:polygon(0 0,92% 0,100% 50%,92% 100%,0 100%)}
.s-tr .k{font-size:13px;opacity:.85}.s-tr i{font-size:26px;margin:8px 0}
.s-tr h3{color:#fff;font-size:18px;font-weight:900;margin:0 0 6px}.s-tr p{margin:0;font-size:14px;opacity:.92;line-height:1.75}
/* متطلبات */
.s-ai{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px}
.s-ai>div{display:flex;gap:14px;background:#fff;border-radius:16px;padding:16px;box-shadow:var(--sh-1)}
.s-ai i{width:46px;height:46px;flex:none;border-radius:12px;display:grid;place-items:center;background:linear-gradient(145deg,var(--gold),var(--gold-2));color:#fff;font-size:19px}
.s-ai b{display:block;color:var(--ink);font-size:15.5px}.s-ai small{color:var(--muted);font-size:13.8px;line-height:1.7}
/* الخطة الزمنية */
.s-time{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;position:relative}
.s-time:before{content:"";position:absolute;top:27px;right:6%;left:6%;height:4px;background:linear-gradient(90deg,var(--gold),var(--teal));border-radius:4px}
.s-ph{text-align:center;position:relative}
.s-ph .d{width:58px;height:58px;margin:0 auto 12px;border-radius:50%;background:#fff;border:4px solid var(--teal);display:grid;place-items:center;font-weight:900;font-size:20px;color:var(--teal);box-shadow:var(--sh-1);position:relative}
.s-ph:last-child .d{border-color:var(--gold);color:var(--gold-2)}
.s-ph .c{background:#fff;border-radius:16px;padding:16px;box-shadow:var(--sh-1)}
.s-ph b{display:block;color:var(--ink);font-size:16px}.s-ph em{display:inline-block;font-style:normal;background:var(--cyan-soft);color:var(--teal);border-radius:20px;padding:2px 12px;font-size:13px;margin:6px 0;font-weight:700}
.s-ph small{display:block;color:var(--muted);font-size:13.8px;line-height:1.7}
.s-first{counter-reset:f;display:grid;grid-template-columns:repeat(2,1fr);gap:12px;list-style:none;padding:0;margin:0}
.s-first li{counter-increment:f;background:#fff;border-radius:14px;padding:14px 16px;box-shadow:var(--sh-1);display:flex;gap:12px;align-items:center;font-size:15px}
.s-first li:before{content:counter(f);flex:none;width:34px;height:34px;border-radius:10px;background:var(--ink-3);color:var(--gold);display:grid;place-items:center;font-weight:900}
.s-rev{opacity:0;transform:translateY(26px);transition:.7s cubic-bezier(.2,.8,.2,1)}.s-rev.in{opacity:1;transform:none}
@media(max-width:992px){.s-pillars,.s-cols,.s-branches{grid-template-columns:repeat(2,1fr)}.s-units{grid-template-columns:repeat(2,1fr)}.s-tracks{grid-template-columns:1fr 1fr}.s-time{grid-template-columns:1fr 1fr}.s-time:before,.s-branches:before,.s-br:before{display:none}.s-tr,.s-tr:first-child{clip-path:none}}
@media(max-width:560px){.s-pillars,.s-cols,.s-branches,.s-units,.s-tracks,.s-time,.s-first{grid-template-columns:1fr}.s-node{min-width:0;width:100%}.s-rule{flex-direction:column;text-align:center}}
@media(prefers-reduced-motion:reduce){.s-rev{opacity:1;transform:none}.s-col-in{transition:none}}

/* تفاصيل تظهر عند المرور — بدون ضغط */
.s-has{position:relative;cursor:default;outline:none}
.s-has:hover,.s-has:focus-within{z-index:30}
.s-x{position:absolute;top:calc(100% + 10px);right:0;left:0;min-width:240px;background:#fff;color:var(--text);border-radius:16px;padding:14px 16px;box-shadow:0 24px 50px -14px rgba(6,33,15,.45);border-top:4px solid var(--gold);text-align:right;opacity:0;visibility:hidden;transform:translateY(10px) scale(.97);transition:.25s ease;pointer-events:none;z-index:40}
.s-x:before{content:"";position:absolute;top:-12px;right:50%;margin-right:-8px;border:8px solid transparent;border-bottom-color:var(--gold)}
.s-x ul{list-style:none;margin:0;padding:0}
.s-x li{font-size:13.8px;line-height:1.7;padding:4px 0;display:flex;gap:8px;align-items:flex-start;color:var(--text);font-weight:500}
.s-x li:before{content:"\f058";font-family:"Font Awesome 5 Free";font-weight:900;color:var(--teal);font-size:12px;margin-top:4px}
.s-has:hover>.s-x,.s-has:focus>.s-x,.s-has:focus-within>.s-x{opacity:1;visibility:visible;transform:none}
.s-x.in{position:static;opacity:1;visibility:visible;transform:none;background:rgba(255,255,255,.14);color:#fff;box-shadow:none;border:0;padding:10px 12px;margin-top:12px;min-width:0;pointer-events:auto}
.s-x.in:before{display:none}.s-x.in li{color:#fff}.s-x.in li:before{color:#fff}
.s-has:after{content:"\f05a";font-family:"Font Awesome 5 Free";font-weight:900;position:absolute;top:8px;left:10px;font-size:12px;color:var(--gold);opacity:.7}
.s-tr .s-x.in+*{display:none}
@media(hover:none),(max-width:768px){.s-x{position:static;opacity:1;visibility:visible;transform:none;box-shadow:none;border-top:2px dashed #e1ebe4;border-radius:0;padding:10px 0 0;margin-top:10px;min-width:0;background:transparent}.s-x:before,.s-has:after{display:none}.s-node.top .s-x li,.s-node.top .s-x li:before{color:#fff}.s-cn{flex-wrap:wrap}.s-cn .s-x{flex-basis:100%}}
@media(max-width:992px){.s-councils{grid-template-columns:repeat(2,1fr)}}
@media(max-width:560px){.s-councils{grid-template-columns:1fr}}
</style>

<div class="s-wrap">
  <div class="s-rule s-rev"><i class="fas fa-seedling"></i><p><b>القاعدة الأساسية:</b> لا نبدأ بكثرة الكليات، بل نبني منظومة أكاديمية واضحة، ثم نتوسع تدريجيًا بحسب الموارد والاحتياجات والاعتماد.</p></div>

  <div class="s-pillars s-sec">
    <?php foreach ($PILLARS as $p): ?><div class="s-pil s-has s-rev" tabindex="0"><i class="fas <?= $p[0] ?>"></i><h3><?= $p[1] ?></h3><p><?= $p[2] ?></p><?= s_info($p[1]) ?></div><?php endforeach; ?>
  </div>

  <section class="s-sec">
    <div class="s-h s-rev"><b>1</b><div><h2>الهيكل التنظيمي الأعلى</h2><p>من الجهة الحاكمة إلى القيادات الأكاديمية والإدارية</p></div></div>
    <div class="s-tree s-rev">
      <div class="s-node top s-has" tabindex="0"><b>مجلس الأمناء / الجهة الحاكمة المختصة</b><small>وفق النظام الأساسي والأنظمة المحلية</small><?= s_info('مجلس الأمناء / الجهة الحاكمة المختصة') ?></div>
      <div class="s-vl"></div>
      <div class="s-node pres s-has" tabindex="0"><b>رئيس الجامعة</b><small>القيادة الاستراتيجية والتنفيذية</small><?= s_info('رئيس الجامعة') ?></div>
      <div class="s-vl"></div>
      <div class="s-node s-has" tabindex="0"><b>المجالس والقيادات الأكاديمية والإدارية</b><?= s_info('المجالس والقيادات الأكاديمية والإدارية') ?></div>
      <div class="s-branches">
        <?php foreach ($BRANCHES as $b): ?><div class="s-br s-has" tabindex="0"><i class="fas <?= $b[0] ?>"></i><b><?= $b[1] ?></b><small><?= $b[2] ?></small><?= s_info($b[1]) ?></div><?php endforeach; ?>
      </div>
    </div>
    <div class="s-councils s-rev">
      <?php foreach ($COUNCILS as $i => $c): ?><div class="s-cn s-has" tabindex="0"><em><?= $i + 1 ?></em><b><?= $c ?></b><?= s_info($c) ?></div><?php endforeach; ?>
    </div>
    <p class="s-foot">تُحدَّد صلاحيات كل مجلس وعلاقته بالجهة الحاكمة وفق النظام الأساسي والأنظمة المعمول بها.</p>
  </section>

  <section class="s-sec">
    <div class="s-h s-rev"><b>2</b><div><h2>الكليات والمجالات الأكاديمية</h2><p>كليات الجامعة ومعهدها في مجموعتين — مرّر أو اضغط على البطاقة لعرض الأقسام</p></div></div>
    <?php $no = 0; foreach ($UWCIV_GROUPS as $gk => $gg): ?>
    <h3 class="s-gt s-rev" style="--cc:<?= $gg[2] ?>"><i class="fas <?= $gg[1] ?>"></i> <?= $gg[0] ?></h3>
    <div class="s-cols">
      <?php foreach ($UWCIV_COLLEGES as $slug => $c): if ($c['g'] !== $gk) continue; $no++; ?>
      <div class="s-col s-rev" tabindex="0" style="--cc:<?= $c['c'] ?>" onclick="this.classList.toggle('on')">
        <div class="s-col-in">
          <div class="s-face s-front"><span class="n"><?= sprintf('%02d', $no) ?></span><div><i class="fas <?= $c['i'] ?>"></i><h3><?= $c['n'] ?></h3><p><?= $c['tag'] ?></p><div class="hint"><i class="fas fa-sync-alt" style="font-size:12px;margin:0"></i> الأقسام</div></div></div>
          <div class="s-face s-back"><h4><?= $c['n'] ?></h4><ul><?php foreach ($c['deps'] as $d): ?><li><?= $d[0] ?></li><?php endforeach; ?></ul><a class="s-more" href="<?= uwciv_college_url($slug) ?>" onclick="event.stopPropagation()">صفحة الكلية <i class="fas fa-arrow-left"></i></a></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    <p class="s-foot">لا تُعدّ البرامج معتمدة لمجرد إدراجها في الهيكل؛ يُفتح كل برنامج بعد استكمال متطلبات الترخيص والاعتماد. <a href="/colleges.php">دليل الكليات الكامل ←</a></p>
  </section>

  <section class="s-sec">
    <div class="s-h s-rev"><b>3</b><div><h2>المنظومة الأكاديمية التي تربط الكليات</h2><p>نظام موحّد يضبط العملية الأكاديمية من القبول حتى التخرج</p></div></div>
    <div class="s-units">
      <?php foreach ($UNITS as $u): ?><div class="s-unit s-has s-rev" tabindex="0"><i class="fas <?= $u[0] ?>"></i><b><?= $u[1] ?></b><small><?= $u[2] ?></small><?= s_info($u[1]) ?></div><?php endforeach; ?>
    </div>
  </section>

  <section class="s-sec">
    <div class="s-h s-rev"><b>4</b><div><h2>نظام الدرجات والمسارات التعليمية</h2><p>فصل واضح بين التعليم الجامعي النظامي والتدريب المهني</p></div></div>
    <div class="s-tracks s-rev">
      <?php foreach ($TRACKS as $i => $t): ?><div class="s-tr" style="--cc:<?= $t[0] ?>;background:<?= $t[0] ?>"><span class="k">المسار <?= ['الأول','الثاني','الثالث','الرابع'][$i] ?></span><div><i class="fas <?= $t[1] ?>"></i></div><h3><?= $t[2] ?></h3><p><?= $t[3] ?></p><?= str_replace('s-x', 's-x in', s_info($t[2])) ?></div><?php endforeach; ?>
    </div>
  </section>

  <section class="s-sec">
    <div class="s-h s-rev"><b>5</b><div><h2>متطلبات الجامعة في عصر الذكاء الاصطناعي</h2><p>متطلبات مشتركة بين جميع التخصصات</p></div></div>
    <div class="s-ai">
      <?php foreach ($AI as $a): ?><div class="s-rev s-has" tabindex="0"><i class="fas <?= $a[0] ?>"></i><span><b><?= $a[1] ?></b><small><?= $a[2] ?></small></span><?= s_info('ai:' . $a[1]) ?></div><?php endforeach; ?>
    </div>
  </section>

  <section class="s-sec">
    <div class="s-h s-rev"><b>6</b><div><h2>خطة تأسيس الهيكل الأكاديمي</h2><p>إطار مرحلي لخمسة عشر شهرًا ثم التوسع</p></div></div>
    <div class="s-time">
      <?php foreach ($PHASES as $i => $p): ?><div class="s-ph s-rev"><div class="d"><?= $i < 3 ? $i + 1 : '<i class="fas fa-rocket" style="font-size:18px"></i>' ?></div><div class="c s-has" tabindex="0"><b><?= $p[0] ?></b><em><?= $p[1] ?></em><small><?= $p[2] ?></small><?= s_info($p[0]) ?></div></div><?php endforeach; ?>
    </div>
  </section>

  <section class="s-sec" style="margin-bottom:0">
    <div class="s-h s-rev"><b>7</b><div><h2>ما الذي يُعتمد أولًا؟</h2><p>القرارات التي تبدأ بها الجامعة</p></div></div>
    <ol class="s-first"><?php foreach ($FIRST as $x): ?><li class="s-rev"><?= $x ?></li><?php endforeach; ?></ol>
  </section>
</div>

<script>
(function(){var els=document.querySelectorAll('.s-rev');if(!('IntersectionObserver'in window)){els.forEach(function(e){e.classList.add('in')});return;}
var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target)}})},{threshold:.12});
els.forEach(function(e,i){e.style.transitionDelay=(i%4)*70+'ms';io.observe(e)});})();
</script>
<?php uwciv_close(); ?>
