<?php
/* شريط الأخبار المتحرك — يُعدَّل من لوحة التحكم (/admin/settings.php#ticker) */
require_once __DIR__ . '/settings.php';

function uwciv_ticker_defaults()
{
    return ['on' => true, 'speed' => 45, 'auto' => true, 'label' => 'آخر الأخبار', 'items' => [
        ['t' => 'التسجيل مفتوح الآن للعام الجامعي 2026–2027 في كليات الشريعة والدعوة والتربية ومعهد اللغة العربية — قدّم طلبك في 5 خطوات', 'u' => '/apply', 'i' => 'fas fa-user-plus', 'hot' => true],
        ['t' => 'إطلاق بوابة الامتحانات الإلكترونية: امتحانات مؤقّتة ونتيجة فورية ولوحة شرف للطلاب', 'u' => '/onlineexam/index.php', 'i' => 'fas fa-laptop-code', 'hot' => false],
        ['t' => 'مجلة جامعة بنيان العالمية المحكّمة تستقبل البحوث لعددها الأول', 'u' => '/journal', 'i' => 'far fa-newspaper', 'hot' => false],
        ['t' => 'فرص وظيفية في هيئة التدريس والإدارة — قدّم وتابع طلبك برقم المرجع', 'u' => '/jobs', 'i' => 'fas fa-briefcase', 'hot' => false],
        ['t' => 'المكتبة الرقمية متاحة على مدار الساعة: كتب ومراجع بصيغة PDF', 'u' => '/librarysystem/index.php', 'i' => 'fas fa-book', 'hot' => false],
        ['t' => 'نشر دليل اللوائح والأنظمة وأدلة الكليات ومصفوفة الصلاحيات', 'u' => '/regulations', 'i' => 'fas fa-gavel', 'hot' => false],
        ['t' => 'تحقّق من وثيقة تخرجك برقم المرجع ورقم الهوية خلال ثوانٍ', 'u' => '/pdf_grad/index.php', 'i' => 'fas fa-certificate', 'hot' => false],
        ['t' => 'ثبّت تطبيق الجامعة على جوالك مجانًا وتابع كل جديد', 'u' => '/app', 'i' => 'fas fa-mobile-alt', 'hot' => false],
    ]];
}

function uwciv_ticker_data()
{
    $S = uwciv_settings(); $d = uwciv_ticker_defaults();
    if (!empty($S['ticker']) && is_array($S['ticker'])) $d = array_merge($d, $S['ticker']);
    return $d;
}

function uwciv_ticker_render()
{
    $T = uwciv_ticker_data();
    if (empty($T['on'])) return '';
    $items = array_values(array_filter((array) $T['items'], function ($x) { return trim($x['t'] ?? '') !== ''; }));
    if (!empty($T['auto'])) {
        try {
            $db = db_pdo('logweb');
            foreach ($db->query('SELECT id, title FROM posts ORDER BY id DESC LIMIT 3')->fetchAll(PDO::FETCH_ASSOC) as $p)
                $items[] = ['t' => $p['title'], 'u' => '/blog/view.php?id=' . (int) $p['id'], 'i' => 'far fa-newspaper', 'hot' => false];
        } catch (Throwable $e) {}
    }
    if (!$items) return '';
    $e = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
    $one = '';
    foreach ($items as $x) {
        $u = trim((string) ($x['u'] ?? ''));
        $ok = $u !== '' && preg_match('~^(/|https?://)~i', $u);
        $in = '<i class="' . $e(preg_match('/^(fas|far|fab) fa-[a-z0-9-]+$/', $x['i'] ?? '') ? $x['i'] : 'fas fa-circle') . '"></i>' . (!empty($x['hot']) ? '<em>عاجل</em>' : '') . '<span>' . $e($x['t']) . '</span>';
        $one .= $ok ? '<a class="u-tk-i" href="' . $e($u) . '">' . $in . '</a>' : '<span class="u-tk-i">' . $in . '</span>';
        $one .= '<b class="u-tk-sep" aria-hidden="true">۞</b>';
    }
    $dur = max(15, min(200, (int) $T['speed'])) * max(1, count($items) / 6);
    return '<div class="u-ticker notranslate" role="region" aria-label="' . $e($T['label']) . '"><div class="u-wrap u-tk-row"><span class="u-tk-l"><i class="fas fa-bullhorn"></i>' . $e($T['label']) . '</span><div class="u-tk-v"><div class="u-tk-t" style="--d:' . round($dur) . 's">' . $one . '</div><div class="u-tk-t" aria-hidden="true" style="--d:' . round($dur) . 's">' . $one . '</div></div><button type="button" class="u-tk-p" aria-label="إيقاف/تشغيل"><i class="fas fa-pause"></i></button></div></div>';
}
