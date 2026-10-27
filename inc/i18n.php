<?php
/*
 * اللغات: العربية / English / বাংলা / اردو
 * نصوص الواجهة (القائمة، التذييل، الصفحة الرئيسية) مترجمة يدوياً هنا.
 * لإضافة ترجمة أو تعديلها: عدّل الكلمة في الجدول أدناه فقط.
 */
if (defined('UWCIV_I18N')) return;
define('UWCIV_I18N', 1);

$UWCIV_LANGS = [
    'ar' => ['name' => 'العربية', 'short' => 'ع',  'dir' => 'rtl'],
    'en' => ['name' => 'English', 'short' => 'EN', 'dir' => 'ltr'],
    'bn' => ['name' => 'বাংলা',   'short' => 'বা', 'dir' => 'ltr'],
    'ur' => ['name' => 'اردو',    'short' => 'ار', 'dir' => 'rtl'],
];

$UWCIV_LANG = 'ar';
if (isset($_GET['lang']) && isset($UWCIV_LANGS[$_GET['lang']])) {
    $UWCIV_LANG = $_GET['lang'];
    if (!headers_sent()) {
        setcookie('uwciv_lang', $UWCIV_LANG, time() + 31536000, '/');
        // يترجم محتوى الصفحات الطويلة تلقائياً للغات غير العربية
        setcookie('googtrans', $UWCIV_LANG === 'ar' ? '' : '/ar/' . $UWCIV_LANG, $UWCIV_LANG === 'ar' ? time() - 3600 : time() + 31536000, '/');
    }
} elseif (isset($_COOKIE['uwciv_lang']) && isset($UWCIV_LANGS[$_COOKIE['uwciv_lang']])) {
    $UWCIV_LANG = $_COOKIE['uwciv_lang'];
}

$UWCIV_T = [
 // الشريط العلوي
 'uni'        => ['جامعة بنيان العالمية', 'Bunyan International University', 'বুনিয়ান আন্তর্জাতিক বিশ্ববিদ্যালয়', 'بنیان انٹرنیشنل یونیورسٹی'],
 'register'   => ['سجّل معنا', 'Apply Now', 'ভর্তি হোন', 'داخلہ لیں'],
 'portal'     => ['البوابة الإلكترونية', 'E-Portal', 'ই-পোর্টাল', 'ای پورٹل'],
 'support'    => ['الدعم الفني', 'Technical Support', 'প্রযুক্তিগত সহায়তা', 'تکنیکی معاونت'],
 'calendar'   => ['التقويم الجامعي', 'Academic Calendar', 'একাডেমিক ক্যালেন্ডার', 'تعلیمی کیلنڈر'],
 'search_ph'  => ['ابحث في موقع الجامعة', 'Search the university site', 'বিশ্ববিদ্যালয়ের সাইটে খুঁজুন', 'یونیورسٹی کی ویب سائٹ میں تلاش کریں'],
 'menu'       => ['القائمة', 'Menu', 'মেনু', 'مینو'],
 'language'   => ['اللغة', 'Language', 'ভাষা', 'زبان'],
 // القائمة الرئيسية
 'home'       => ['الرئيسية', 'Home', 'হোম', 'ہوم'],
 'about'      => ['عن الجامعة', 'About', 'আমাদের সম্পর্কে', 'یونیورسٹی کے بارے میں'],
 'about_biu'  => ['التعريف بالجامعة', 'About the University', 'বিশ্ববিদ্যালয় পরিচিতি', 'یونیورسٹی کا تعارف'],
 'study_system' => ['نظام الدراسة في الجامعة', 'Study System', 'অধ্যয়ন পদ্ধতি', 'نظامِ تعلیم'],
 'hq'         => ['المقر الرئيسي للجامعة', 'Main Campus', 'প্রধান ক্যাম্পাস', 'مرکزی کیمپس'],
 'agreements' => ['الاتفاقيات والمعاهدات الدولية', 'International Agreements', 'আন্তর্জাতিক চুক্তি', 'بین الاقوامی معاہدے'],
 'org_chart'  => ['الهيكل التنظيمي', 'Organizational Chart', 'সাংগঠনিক কাঠামো', 'تنظیمی ڈھانچہ'],
 'scholarships' => ['المنح الدراسية', 'Scholarships', 'বৃত্তি', 'وظائف'],
 'sharia_prog' => ['برنامج الشريعة — صناعة العلماء', 'Sharia Program', 'শরিয়াহ প্রোগ্রাম', 'شریعہ پروگرام'],
 'personal_status' => ['قسم الأحوال الشخصية', 'Personal Status Department', 'ব্যক্তিগত মর্যাদা বিভাগ', 'شعبۂ احوالِ شخصیہ'],
 'guidance'   => ['قسم التوجيه والإرشاد', 'Guidance & Counseling Department', 'নির্দেশনা ও পরামর্শ বিভাগ', 'شعبۂ رہنمائی و ارشاد'],
 'preachers_dip' => ['دبلوم تأهيل الدعاة', 'Preachers Qualification Diploma', 'দাঈ প্রশিক্ষণ ডিপ্লোমা', 'ڈپلومہ تربیتِ داعیان'],
 'introducers_dip' => ['دبلوم تأهيل المعرفين بالإسلام', 'Diploma for Introducers of Islam', 'ইসলাম পরিচিতি ডিপ্লোমা', 'ڈپلومہ تعارفِ اسلام'],
 'arabic_dept' => ['قسم اللغة العربية', 'Arabic Language Department', 'আরবি ভাষা বিভাগ', 'شعبۂ عربی زبان'],
 'admin'      => ['إدارة الجامعة', 'Administration', 'প্রশাসন', 'انتظامیہ'],
 'study'      => ['الدراسة في الجامعة', 'Study at UWC', 'অধ্যয়ন', 'تعلیم'],
 'centers'    => ['المراكز', 'Centers', 'কেন্দ্রসমূহ', 'مراکز'],
 'branches'   => ['الفروع', 'Branches', 'শাখাসমূহ', 'شاخیں'],
 'regs'       => ['اللوائح والأنظمة', 'Regulations', 'নিয়মাবলী', 'قواعد و ضوابط'],
 'alumni'     => ['منصة الخريجين', 'Alumni', 'প্রাক্তন শিক্ষার্থী', 'فضلاء پلیٹ فارم'],
 'jobs'       => ['الوظائف', 'Careers', 'চাকরি', 'ملازمتیں'],
 'contact'    => ['تواصل معنا', 'Contact Us', 'যোগাযোগ', 'رابطہ کریں'],
 // عن الجامعة
 'edusys'     => ['نظام الدراسة في الجامعة', 'Study System', 'অধ্যয়ন পদ্ধতি', 'نظامِ تعلیم'],
 'place'      => ['المقر الرئيسي', 'Main Campus', 'প্রধান ক্যাম্পাস', 'مرکزی کیمپس'],
 'countries'  => ['الاتفاقيات الدولية', 'International Agreements', 'আন্তর্জাতিক চুক্তি', 'بین الاقوامی معاہدے'],
 'structure'  => ['الهيكل الأكاديمي', 'Academic Structure', 'একাডেমিক কাঠামো', 'تعلیمی ڈھانچہ'],
 'strategy'   => ['استراتيجيات الجامعة', 'University Strategy', 'বিশ্ববিদ্যালয়ের কৌশল', 'یونیورسٹی کی حکمتِ عملی'],
 // إدارة الجامعة
 'council'    => ['مجلس الجامعة', 'University Council', 'বিশ্ববিদ্যালয় পরিষদ', 'یونیورسٹی کونسل'],
 'president'  => ['رئيس الجامعة', 'President', 'উপাচার্য', 'صدرِ جامعہ'],
 'vice'       => ['وكيل الجامعة', 'Vice President', 'উপ-উপাচার্য', 'نائب صدر'],
 'vicedep'    => ['وكالة الجامعة', 'Vice Presidency', 'উপ-উপাচার্যের দপ্তর', 'نیابتِ جامعہ'],
 'scicouncil' => ['المجلس العلمي', 'Scientific Council', 'বৈজ্ঞানিক পরিষদ', 'علمی کونسل'],
 'promo'      => ['اللجنة الدائمة للترقيات', 'Standing Promotions Committee', 'স্থায়ী পদোন্নতি কমিটি', 'مستقل کمیٹی برائے ترقیات'],
 'finance'    => ['الإدارة العامة للشؤون المالية والإدارية', 'Finance & Administration', 'অর্থ ও প্রশাসন বিভাগ', 'مالی و انتظامی امور'],
 'adminaff'   => ['الشؤون الإدارية', 'Administrative Affairs', 'প্রশাসনিক বিষয়াবলী', 'انتظامی امور'],
 // الدراسة
 'colleges'   => ['الكليات والمعاهد', 'Colleges & Institutes', 'কলেজ ও ইনস্টিটিউট', 'کالجز اور ادارے'],
 'diploma'    => ['الدبلوم', 'Diploma', 'ডিপ্লোমা', 'ڈپلومہ'],
 'bachelor'   => ['مرحلة البكالوريوس', 'Bachelor', 'স্নাতক', 'بیچلر'],
 'master'     => ['مرحلة الماجستير', 'Master', 'স্নাতকোত্তর', 'ماسٹرز'],
 'phd'        => ['مرحلة الدكتوراه', 'PhD', 'পিএইচডি', 'پی ایچ ڈی'],
 'deanships'  => ['العمادات', 'Deanships', 'ডিনশিপ', 'عمادات'],
 'support_u'  => ['الوحدات الإدارية المساندة', 'Support Units', 'সহায়ক ইউনিট', 'معاون انتظامی اکائیاں'],
 'faculty'    => ['أعضاء هيئة التدريس', 'Faculty Members', 'শিক্ষকমণ্ডলী', 'اساتذہ کرام'],
 'plans'      => ['الخطط الدراسية', 'Study Plans', 'পাঠ্যক্রম পরিকল্পনা', 'تعلیمی منصوبے'],
 'platform'   => ['المنصة التعليمية', 'Learning Platform', 'শিক্ষা প্ল্যাটফর্ম', 'تعلیمی پلیٹ فارم'],
 'exam'       => ['الامتحان الإلكتروني', 'Online Exam', 'অনলাইন পরীক্ষা', 'آن لائن امتحان'],
 'cert'       => ['الشهادة الإلكترونية', 'E-Certificate', 'ই-সার্টিফিকেট', 'ای سرٹیفکیٹ'],
 'courses'    => ['الدورات التدريبية', 'Training Courses', 'প্রশিক্ষণ কোর্স', 'تربیتی کورسز'],
 // المراكز
 'c_train'    => ['مركز التدريب والتطوير والبرامج المجتمعية', 'Training & Community Programs Center', 'প্রশিক্ষণ ও কমিউনিটি প্রোগ্রাম কেন্দ্র', 'مرکز برائے تربیت و سماجی پروگرام'],
 'c_lang'     => ['مركز اللغات والترجمة', 'Languages & Translation Center', 'ভাষা ও অনুবাদ কেন্দ্র', 'مرکزِ زبان و ترجمہ'],
 'c_media'    => ['المركز الإعلامي', 'Media Center', 'মিডিয়া সেন্টার', 'میڈیا سینٹر'],
 'c_research' => ['معهد البحوث والاستشارات', 'Research & Consulting Institute', 'গবেষণা ও পরামর্শ ইনস্টিটিউট', 'ادارہ تحقیق و مشاورت'],
 'c_manus'    => ['مركز المخطوطات وتحقيق التراث', 'Manuscripts & Heritage Center', 'পাণ্ডুলিপি ও ঐতিহ্য কেন্দ্র', 'مرکزِ مخطوطات و تحقیقِ تراث'],
 'c_dawah'    => ['مركز الدعوة', 'Dawah Center', 'দাওয়াহ কেন্দ্র', 'مرکزِ دعوت'],
 'journal'    => ['مجلة الجامعة', 'University Journal', 'বিশ্ববিদ্যালয় জার্নাল', 'یونیورسٹی جریدہ'],
 'library'    => ['المكتبة الرقمية', 'Digital Library', 'ডিজিটাল লাইব্রেরি', 'ڈیجیٹل لائبریری'],
 'news'       => ['أخبار الجامعة', 'University News', 'বিশ্ববিদ্যালয়ের খবর', 'یونیورسٹی کی خبریں'],
 'events'     => ['الفعاليات والأنشطة', 'Events & Activities', 'অনুষ্ঠান ও কার্যক্রম', 'تقریبات و سرگرمیاں'],
 'gallery'    => ['معرض الصور', 'Photo Gallery', 'ফটো গ্যালারি', 'تصویری گیلری'],
 'party'      => ['حفلات التخرج', 'Graduation Ceremonies', 'সমাবর্তন অনুষ্ঠান', 'تقاریبِ تقسیمِ اسناد'],
 'faq'        => ['الأسئلة الشائعة', 'FAQ', 'সাধারণ প্রশ্ন', 'عام سوالات'],
 'login'      => ['تسجيل الدخول', 'Sign In', 'লগইন', 'لاگ ان'],
 'col_guide'  => ['دليل الكليات', 'Colleges Guide', 'কলেজ নির্দেশিকা', 'کالجز گائیڈ'],
 'colleges_m' => ['الكليات', 'Colleges', 'কলেজসমূহ', 'کالجز'],
 'library_m'  => ['المكتبة الإلكترونية', 'E-Library', 'ই-লাইব্রেরি', 'ای لائبریری'],
 'journal_m'  => ['المجلة العلمية', 'Scientific Journal', 'বৈজ্ঞানিক জার্নাল', 'علمی جریدہ'],
 'calendar_m' => ['التقويم الدراسي', 'Academic Calendar', 'একাডেমিক ক্যালেন্ডার', 'تعلیمی کیلنڈر'],
 'accred'     => ['الاعتمادات', 'Accreditations', 'স্বীকৃতি', 'منظوریاں'],
 'lectures'   => ['المحاضرات', 'Lectures', 'লেকচার', 'لیکچرز'],
 'lec_bac'    => ['محاضرات البكالوريوس', 'Bachelor Lectures', 'স্নাতক লেকচার', 'بیچلر لیکچرز'],
 'lec_mas'    => ['محاضرات الماجستير', 'Master Lectures', 'স্নাতকোত্তর লেকচার', 'ماسٹرز لیکچرز'],
 'lec_phd'    => ['محاضرات الدكتوراه', 'PhD Lectures', 'পিএইচডি লেকচার', 'پی ایچ ڈی لیکچرز'],
 'stage'      => ['المرحلة الدراسية', 'Study Stage', 'অধ্যয়নের স্তর', 'تعلیمی مرحلہ'],
 'links'      => ['الروابط المختصرة', 'Short Links', 'সংক্ষিপ্ত লিংক', 'مختصر روابط'],
 'grad_res'   => ['رسائل الخريجين', 'Graduate Theses', 'স্নাতকদের থিসিস', 'فضلاء کے مقالات'],
 'logout'     => ['تسجيل الخروج', 'Sign out', 'লগআউট', 'لاگ آؤٹ'],
 'back'       => ['رجوع', 'Back', 'ফিরে যান', 'واپس'],
 // التذييل
 'f_links'    => ['الروابط الهامة', 'Important Links', 'গুরুত্বপূর্ণ লিংক', 'اہم روابط'],
 'f_quick'    => ['الوصول السريع', 'Quick Access', 'দ্রুত প্রবেশ', 'فوری رسائی'],
 'f_follow'   => ['تابع الجامعة', 'Follow the University', 'আমাদের অনুসরণ করুন', 'ہمیں فالو کریں'],
 'f_reach'    => ['تواصل مع الجامعة', 'Get in Touch', 'যোগাযোগ করুন', 'رابطے میں رہیں'],
 'f_about'    => ['مؤسسة تعليمية تقدّم برامج أكاديمية عن بُعد في العلوم الشرعية والإنسانية، بمرونة تناسب طلاب العلم في كل مكان.', 'An educational institution offering remote academic programs in Islamic and human sciences, flexible for learners everywhere.', 'একটি শিক্ষা প্রতিষ্ঠান যা ইসলামী ও মানবিক বিজ্ঞানে দূরশিক্ষণ একাডেমিক প্রোগ্রাম প্রদান করে, সর্বত্র শিক্ষার্থীদের জন্য নমনীয়।', 'ایک تعلیمی ادارہ جو اسلامی و انسانی علوم میں فاصلاتی تعلیمی پروگرام پیش کرتا ہے، ہر جگہ کے طلبہ کے لیے سہولت کے ساتھ۔'],
 'privacy'    => ['سياسة الخصوصية', 'Privacy Policy', 'গোপনীয়তা নীতি', 'رازداری کی پالیسی'],
 'credits'    => ['مصادر الصور', 'Image Credits', 'ছবির উৎস', 'تصاویر کے ماخذ'],
 'terms'      => ['شروط وأحكام الخدمة', 'Terms of Service', 'সেবার শর্তাবলী', 'شرائطِ خدمت'],
 'rights'     => ['جميع الحقوق محفوظة لجامعة بنيان العالمية', 'All rights reserved — Bunyan International University', 'সর্বস্বত্ব সংরক্ষিত — বুনিয়ান আন্তর্জাতিক বিশ্ববিদ্যালয়', 'جملہ حقوق محفوظ ہیں — بنیان انٹرنیشنل یونیورسٹی'],
 // الرئيسية
 'h_kicker'   => ['خدمات الجامعة الرقمية', 'Digital University Services', 'ডিজিটাল বিশ্ববিদ্যালয় সেবা', 'یونیورسٹی کی ڈیجیٹل خدمات'],
 'h_title'    => ['تعلّم بلا حدود', 'Learning Without Borders', 'সীমাহীন শিক্ষা', 'بے حد و حساب علم'],
 'h_text'     => ['جامعة بنيان العالمية تفتح أبواب العلم الشرعي والمعرفة الإنسانية لكل طالب، أينما كان. سجّل في برامج الدبلوم والبكالوريوس والماجستير والدكتوراه، وتابع محاضراتك وامتحاناتك وشهاداتك من بوابة واحدة.', 'Bunyan International University opens the doors of Islamic and human knowledge to every learner, wherever they are. Enroll in diploma, bachelor, master and PhD programs, and follow your lectures, exams and certificates from one portal.', 'বুনিয়ান আন্তর্জাতিক বিশ্ববিদ্যালয় প্রতিটি শিক্ষার্থীর জন্য ইসলামী ও মানবিক জ্ঞানের দরজা খুলে দেয়, যেখানেই থাকুন না কেন। ডিপ্লোমা, স্নাতক, স্নাতকোত্তর ও পিএইচডি প্রোগ্রামে ভর্তি হন এবং একটি পোর্টাল থেকে আপনার লেকচার, পরীক্ষা ও সনদ দেখুন।', 'بنیان انٹرنیشنل یونیورسٹی ہر طالب علم کے لیے، چاہے وہ کہیں بھی ہو، شرعی اور انسانی علوم کے دروازے کھولتی ہے۔ ڈپلومہ، بیچلر، ماسٹرز اور پی ایچ ڈی پروگراموں میں داخلہ لیں اور ایک ہی پورٹل سے اپنے لیکچرز، امتحانات اور اسناد دیکھیں۔'],
 'h_cta'      => ['الدخول إلى البوابة', 'Enter the Portal', 'পোর্টালে প্রবেশ করুন', 'پورٹل میں داخل ہوں'],
 'h_cta2'     => ['استعرض الكليات', 'Explore Colleges', 'কলেজসমূহ দেখুন', 'کالجز دیکھیں'],
 'h_note'     => ['تظهر لك الخدمات المتاحة حسب نوع حسابك في الجامعة.', 'Services shown depend on your university account type.', 'আপনার অ্যাকাউন্টের ধরন অনুযায়ী সেবাগুলো দেখানো হয়।', 'آپ کے اکاؤنٹ کی قسم کے مطابق خدمات دکھائی جاتی ہیں۔'],
 's_students' => ['خدمات الطلاب', 'Student Services', 'শিক্ষার্থী সেবা', 'طلبہ کی خدمات'],
 's_faculty'  => ['خدمات أعضاء هيئة التدريس', 'Faculty Services', 'শিক্ষক সেবা', 'اساتذہ کی خدمات'],
 's_staff'    => ['خدمات الموظفين', 'Staff Services', 'কর্মচারী সেবা', 'عملے کی خدمات'],
 's_students_d' => ['التسجيل، المحاضرات، الامتحانات', 'Registration, lectures, exams', 'নিবন্ধন, লেকচার, পরীক্ষা', 'رجسٹریشن، لیکچرز، امتحانات'],
 's_faculty_d'  => ['المقررات، الدرجات، المكتبة', 'Courses, grades, library', 'কোর্স, গ্রেড, লাইব্রেরি', 'کورسز، نمبرات، لائبریری'],
 's_staff_d'    => ['الأخبار، الفعاليات، الإدارة', 'News, events, administration', 'খবর, অনুষ্ঠান, প্রশাসন', 'خبریں، تقریبات، انتظامیہ'],
 'p_unified'  => ['دخول موحّد', 'Single sign-in', 'একক লগইন', 'یکساں لاگ ان'],
 'p_paths'    => ['٣ مسارات مخصّصة', '3 dedicated paths', '৩টি নির্দিষ্ট পথ', '۳ مخصوص راستے'],
 'p_roles'    => ['خدمات حسب الصلاحية', 'Role-based services', 'ভূমিকা-ভিত্তিক সেবা', 'اختیار کے مطابق خدمات'],
 // صفحة المحتوى
 'soon_t'     => ['يُضاف المحتوى قريباً', 'Content coming soon', 'বিষয়বস্তু শীঘ্রই আসছে', 'مواد جلد شامل کیا جائے گا'],
 'soon_d'     => ['نعمل على تجهيز هذه الصفحة. للاستفسار تواصل مع الجامعة عبر صفحة الدعم الفني.', 'We are preparing this page. For inquiries, please contact us through Technical Support.', 'আমরা এই পৃষ্ঠাটি প্রস্তুত করছি। প্রশ্নের জন্য প্রযুক্তিগত সহায়তার মাধ্যমে যোগাযোগ করুন।', 'ہم یہ صفحہ تیار کر رہے ہیں۔ استفسار کے لیے تکنیکی معاونت کے ذریعے رابطہ کریں۔'],
 'in_section' => ['في هذا القسم', 'In this section', 'এই বিভাগে', 'اس حصے میں'],
];

function t($key)
{
    global $UWCIV_T, $UWCIV_LANG;
    $i = array_search($UWCIV_LANG, ['ar', 'en', 'bn', 'ur'], true);
    if (!isset($UWCIV_T[$key])) return $key;
    return $UWCIV_T[$key][$i] !== '' ? $UWCIV_T[$key][$i] : $UWCIV_T[$key][0];
}

function lang_url($code)
{
    $uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
    $q = $_GET;
    $q['lang'] = $code;
    return htmlspecialchars($uri . '?' . http_build_query($q), ENT_QUOTES);
}

/*
 * هيكل القائمة: [مفتاح النص, الرابط, [قائمة فرعية]]
 * الصفحات التي لم تُنشأ بعد تذهب إلى info.php?p=المفتاح
 */
function uwciv_menu()
{
    $i = function ($k) { return '/info.php?p=' . $k; };
    return [
        ['home', '/index.php'],
        ['about', $i('about_biu'), [
            ['about_biu', $i('about_biu')], ['study_system', $i('study_system')], ['hq', $i('hq')],
            ['agreements', $i('agreements')], ['org_chart', $i('org_chart')], ['scholarships', $i('scholarships')], ['structure', '/structure.php'], ['strategy', '/stratj.php'],
            ['branches', $i('branches')], ['regs', '/regulations.php'], ['alumni', '/grad.php'], ['jobs', '/jobs.php'],
        ]],
        ['admin', '#', [
            ['council', $i('council')], ['president', $i('president')], ['vice', $i('vice')],
            ['vicedep', $i('vicedep')], ['scicouncil', $i('scicouncil')], ['promo', $i('promo')],
            ['finance', $i('finance')], ['adminaff', $i('adminaff')],
        ]],
        ['colleges_m', '/colleges.php', [
            ['col_guide', '/colleges.php'], ['diploma', '/depl.php'], ['bachelor', '/depm.php'], ['master', '/depmst.php'], ['phd', '/depdec.php'],
            ['lectures', '/depmlact.php', [
                ['lec_bac', '/depmlact.php'], ['lec_mas', '/depmstlact.php'], ['lec_phd', '/declact.php'],
            ]],
            ['faculty', '/teacher/index.php'], ['deanships', $i('deanships')], ['support_u', $i('support_u')], ['plans', $i('plans')],
            ['platform', '/education.php', [
                ['exam', '/onlineexam/index.php'], ['cert', '/de/des.php'], ['courses', '/courses/courses.php'],
            ]],
        ]],
        ['centers', '#', [
            ['c_train', $i('c_train')], ['c_lang', $i('c_lang')], ['c_media', '/news.php', [
                ['news', '/news.php'], ['events', '/events.php'], ['gallery', '/images.php'], ['party', '/party.php'],
            ]],
            ['c_research', '/research.php'], ['c_manus', $i('c_manus')], ['c_dawah', $i('c_dawah')],
        ]],
        ['library_m', '/librarysystem/index.php', [
            ['library', '/librarysystem/index.php'], ['grad_res', '/pdf_grad/index.php'],
        ]],
        ['journal_m', '/journal.php'],
        ['calendar_m', '/calendar.php'],
        ['accred', $i('accred')],
        ['gallery', '/images.php'],
        ['contact', '/contact.php'],
    ];
}

/* المرحلة الدراسية للصفحة الحالية (لتلوين الصفحة حسب المرحلة) */
function uwciv_stage($file)
{
    $f = preg_replace('/\.php$/', '', $file);
    if ($f === 'depl') return 'dip';
    if (in_array($f, ['depm', 'depmlact', 'research'], true) || preg_match('/^(quran|islam|law|faqh|arab|edu|med|eco|pro|lang)1$/', $f) || preg_match('/^(qn|islam|law|faqh|arab|edu|med|eco|prog|lang|rsch)lact$/', $f)) return 'bac';
    if (in_array($f, ['depmst', 'depmstlact', 'research2'], true) || preg_match('/^(quran|islam|law|faqh|arab|edu|med|eco|pro|lang)2$/', $f) || preg_match('/mslact$/', $f)) return 'mas';
    if (in_array($f, ['depdec', 'declact', 'research3'], true) || preg_match('/^(quran|islam|law|faqh|arab|edu|med|eco|pro|lang)3$/', $f) || preg_match('/dclact$/', $f)) return 'phd';
    return '';
}
