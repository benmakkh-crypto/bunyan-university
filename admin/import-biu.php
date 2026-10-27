<?php
/*
 * استيراد محتوى جامعة بنيان من موقعها الرسمي biu.ac.id
 * يجلب على الخادم: الأخبار والمقالات وصورها، ومعرض الصور، وصور الصفحات (الهيكل التنظيمي، الاعتمادات، الشركاء، الدبلومات، السلايدر).
 * آمن للتكرار: لا يُضاف الخبر مرتين (يُقارن بالعنوان)، ولا تُنزَّل الصورة الموجودة مرة أخرى.
 */
require_once dirname(__DIR__) . '/inc/admin_ui.php';
uwciv_admin_gate(['title' => 'استيراد محتوى بنيان']);
@set_time_limit(600);

const BIU_SITE = 'https://biu.ac.id';
const BIU_API  = 'https://bkport.kafaatalia.com';
$ROOT = dirname(__DIR__);
$IMG  = $ROOT . '/assets/images/biu';

function biu_get($url, $timeout = 40)
{
    if (function_exists('curl_init')) {
        $c = curl_init($url);
        curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_MAXREDIRS => 4, CURLOPT_TIMEOUT => $timeout, CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (BIU site importer)', CURLOPT_HTTPHEADER => ['Accept: */*', 'Referer: ' . BIU_SITE . '/']]);
        $b = curl_exec($c); $code = (int) curl_getinfo($c, CURLINFO_HTTP_CODE); curl_close($c);
        return ($b !== false && $code >= 200 && $code < 300) ? $b : null;
    }
    $ctx = stream_context_create(['http' => ['timeout' => $timeout, 'header' => "User-Agent: Mozilla/5.0 (BIU site importer)\r\nReferer: " . BIU_SITE . "/\r\n"]]);
    $b = @file_get_contents($url, false, $ctx);
    return $b === false ? null : $b;
}
function biu_file_url($name) { return BIU_API . '/files/' . rawurlencode($name); }
/* تنزيل صورة والتحقق أنها صورة فعلًا؛ يعيد true إن وُجدت أو نُزّلت */
function biu_save_image($url, $dest, &$log)
{
    if (is_file($dest) && filesize($dest) > 0) return true;
    $b = biu_get($url);
    if ($b === null || @getimagesizefromstring($b) === false) { $log[] = ['bad', 'تعذّر تنزيل: ' . $url]; return false; }
    if (!is_dir(dirname($dest))) @mkdir(dirname($dest), 0755, true);
    if (@file_put_contents($dest, $b) === false) { $log[] = ['bad', 'تعذّر الحفظ في: ' . $dest]; return false; }
    return true;
}
function biu_ext($name) { $e = strtolower(pathinfo($name, PATHINFO_EXTENSION)); return in_array($e, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? ($e === 'jpeg' ? 'jpg' : $e) : 'jpg'; }
function biu_html($t)
{
    $t = trim(str_replace("\r", '', (string) $t));
    $p = preg_split('/\n{2,}/u', $t);
    return implode('', array_map(function ($x) { return '<p>' . nl2br(htmlspecialchars(trim($x), ENT_QUOTES, 'UTF-8')) . '</p>'; }, array_filter($p, 'strlen')));
}

/* صور الصفحات الثابتة: المسار المحلي => المسار في موقع بنيان */
function biu_static_images()
{
    $L = [
        'org_chart.jpg' => '/assets/images/pages/org_chart.jpg',
        'UniCalander.jpg' => '/assets/images/publicImages/UniCalander.jpeg',
        'accreditations/accreditation_1.jpg' => '/assets/images/accreditations/accreditation_1.jpg',
        'accreditations/accreditation_2.jpg' => '/assets/images/accreditations/accreditation_2.jpg',
    ];
    foreach (['icesco.jpg', 'umm_al_qura.jpg', 'king_abdulaziz.png', 'king_khalid.webp', 'princess_nourah.png', 'uin_sunan.jpg', 'iua.jpg', 'nobles.jpg'] as $f) $L['partners/' . $f] = '/assets/images/pages/' . $f;
    foreach (['mechanism', 'level1', 'level2', 'level3', 'level4', 'curriculum', 'methods'] as $f) $L['diploma_preachers/' . $f . '.png'] = '/assets/images/diploma_preachers/' . $f . '.png';
    foreach (['table1', 'table2', 'level1', 'level2'] as $f) $L['diploma_qualifying/' . $f . '.png'] = '/assets/images/diploma_qualifying/' . $f . '.png';
    return $L;
}
/* صور معرض الجامعة كما في صفحة المعرض بموقع بنيان */
function biu_gallery_files()
{
    $n = ['1 (36) -tws- 20240307853004507.jpeg', '1 (64) -tws- 20240307842106522.jpeg', '1 (2) -tws- 20240307848494036.jpeg', '1 (70) -tws- 20240307853153693.jpeg', '1 (5) -tws- 20240311754299501.jpg', '1 (6) -tws- 20240311754327519.jpg', '1 (3) -tws- 20240311758334973.jpg', '1 (94) -tws- 20240307840508957.jpeg', '1 (68) -tws- 20240307849424708.jpeg', '1 (16) -tws- 20240311749215879.jpg', '1 (9) -tws- 20240311754447300.jpg', '1 (24) -tws- 20240307843198769.jpeg', '1 (114) -tws- 20240307845551198.jpeg', '1 (110) -tws- 20240307847297881.jpeg', '1 (5) -tws- 20240307848046405.jpeg', '1 (75) -tws- 20240307851041069.jpeg', '1 (1) -tws- 20240307852361194.jpeg', '1 (14) -tws- 20240307852494111.jpeg', '1 (14) -tws- 20240311749451444.jpg', '1 (2) -tws- 20240311752243089.jpg', '1 (4) -tws- 20240311752388083.jpg', '1 (67) -tws- 20240307841364632.jpeg', '1 (3) -tws- 20240311751479681.jpg', '1 (39) -tws- 20240307852173813.jpeg', '1 (1) -tws- 20240311752138681.jpg', '1 (9) -tws- 20240307846269502.jpeg', '1 (107) -tws- 20240307853268957.jpeg', '1 (89) -tws- 20240307851329156.jpeg', '1 (97) -tws- 20240307840343711.jpeg', '1 (28) -tws- 20240307842378337.jpeg', '1 (4) -tws- 20240307847527920.jpeg', '1 (3) -tws- 20240307849035966.jpeg', '1 (1) -tws- 20240307850045719.jpg', '1 (73) -tws- 20240307850515258.jpeg', '1 (76) -tws- 20240307851152414.jpeg', '1 (103) -tws- 20240307853338328.jpeg', '1 (8) -tws- 20240311754415515.jpg', '1 (98) -tws- 20240307840098285.jpeg', '1 (93) -tws- 20240307841117283.jpeg', '1 (26) -tws- 20240307843082118.jpeg', '1 (88) -tws- 20240307851421056.jpeg', '1 (13) -tws- 20240311749579616.jpg', '1 (12) -tws- 20240311750071993.jpg', '1 (20) -tws- 20240311752554123.jpg', '1 (21) -tws- 20240311753249518.jpg', '1 (11) -tws- 20240311754587546.jpg', '1 (74) -tws- 20240307853416931.jpeg', '1 (71) -tws- 20240307846597185.jpeg', '1 (72) -tws- 20240307850358749.jpeg', '1 (17) -tws- 20240311749034413.jpg', '1 (15) -tws- 20240311749316276.jpg', '1 (19) -tws- 20240311753276974.jpg', '1 (18) -tws- 20240311753332888.jpg', '1 (7) -tws- 20240311754363914.jpg', '1 (8) -tws- 20240311755318709.jpg'];
    return $n;
}

$log = []; $stats = ['img' => 0, 'news' => 0, 'skip' => 0];
$done = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!uwciv_csrf_ok()) $log[] = ['bad', 'انتهت صلاحية الصفحة، أعد المحاولة.'];
    else {
        $what = (array) ($_POST['what'] ?? []);
        if (in_array('pages', $what, true)) {
            foreach (biu_static_images() as $local => $remote) if (biu_save_image(BIU_SITE . $remote, $IMG . '/' . $local, $log)) $stats['img']++;
            $log[] = ['ok', 'صور الصفحات: الهيكل التنظيمي والاعتمادات والشركاء والدبلومات والتقويم.'];
        }
        if (in_array('home', $what, true)) {
            $sl = json_decode((string) biu_get(BIU_API . '/slider/list'), true);
            if (is_array($sl)) { $i = 0; foreach (array_reverse($sl) as $x) { if (empty($x['image']['url'])) continue; if (biu_save_image(biu_file_url($x['image']['url']), $IMG . '/slider-' . (++$i) . '.jpg', $log)) $stats['img']++; } }
            else $log[] = ['bad', 'تعذّر قراءة صور السلايدر من موقع بنيان.'];
            $fi = json_decode((string) biu_get(BIU_API . '/frontinfo/list'), true);
            $map = ['الصورة 1' => 'front-about', 'الصورة 2' => 'front-library', 'الصورة 3' => 'front-vision', 'الصورة 4' => 'front-mission', 'الصورة 5' => 'front-system'];
            if (is_array($fi)) { foreach ($fi as $x) { $k = $map[trim((string) ($x['title'] ?? ''))] ?? null; if ($k && !empty($x['image']['url']) && biu_save_image(biu_file_url($x['image']['url']), $IMG . '/' . $k . '.jpg', $log)) $stats['img']++; } }
            else $log[] = ['bad', 'تعذّر قراءة صور الصفحة الرئيسية من موقع بنيان.'];
            $log[] = ['ok', 'صور الصفحة الرئيسية والسلايدر.'];
        }
        if (in_array('gallery', $what, true)) {
            foreach (biu_gallery_files() as $i => $f) if (biu_save_image(biu_file_url($f), sprintf('%s/gallery/%03d.%s', $IMG, $i + 1, biu_ext($f)), $log)) $stats['img']++;
            $log[] = ['ok', 'معرض الصور.'];
        }
        if (in_array('news', $what, true) || in_array('blog', $what, true)) {
            try { $db = db_pdo('logweb'); $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); } catch (Throwable $e) { $db = null; $log[] = ['bad', 'تعذّر الاتصال بقاعدة البيانات — أكمل إعداد db_config.php أولًا.']; }
            if ($db) {
                $exists = $db->prepare('SELECT COUNT(*) FROM posts WHERE title = ?');
                $ins = $db->prepare('INSERT INTO posts (`date`, title, summary, content, classification, image) VALUES (?, ?, ?, ?, ?, ?)');
                foreach (['news' => 'أخبار الجامعة', 'blog' => 'مقالات'] as $src => $cls) {
                    if (!in_array($src, $what, true)) continue;
                    $rows = json_decode((string) biu_get(BIU_API . '/' . $src . '/list'), true);
                    if (!is_array($rows)) { $log[] = ['bad', 'تعذّر قراءة ' . ($src === 'news' ? 'الأخبار' : 'المقالات') . ' من موقع بنيان.']; continue; }
                    usort($rows, function ($a, $b) { return strcmp((string) ($a['created_date'] ?? ''), (string) ($b['created_date'] ?? '')); });
                    foreach ($rows as $r) {
                        $title = trim(preg_replace('/\s+/u', ' ', (string) ($r['title'] ?? ''))); if ($title === '') continue;
                        $exists->execute([$title]); if ((int) $exists->fetchColumn() > 0) { $stats['skip']++; continue; }
                        $img = '';
                        if (!empty($r['image']['url'])) {
                            $name = 'biu-' . preg_replace('/[^a-f0-9]/', '', (string) ($r['_id'] ?? md5($title))) . '.' . biu_ext($r['image']['url']);
                            if (biu_save_image(biu_file_url($r['image']['url']), $ROOT . '/blog/img/' . $name, $log)) { $img = $name; $stats['img']++; }
                        }
                        $content = (string) ($r['content'] ?? '');
                        $summary = mb_strimwidth(preg_replace('/\s+/u', ' ', $content), 0, 220, '…');
                        $date = !empty($r['created_date']) ? date('Y/m/d', strtotime($r['created_date'])) : date('Y/m/d');
                        $ins->execute([$date, mb_substr($title, 0, 250), $summary, biu_html($content), $cls, $img]);
                        $stats['news']++;
                    }
                }
                $log[] = ['ok', 'الأخبار والمقالات.'];
            }
        }
        if (function_exists('uwciv_staff_log')) uwciv_staff_log('استورد محتوى من موقع بنيان: ' . $stats['news'] . ' خبر/مقال و' . $stats['img'] . ' صورة');
        $done = true;
    }
}

$h = 'uwciv_h';
$have = function ($rel) use ($IMG) { return is_file($IMG . '/' . $rel); };
$gal = count(glob($IMG . '/gallery/*') ?: []);
uwciv_admin_open('import', 'استيراد محتوى بنيان', 'اجلب الأخبار والمقالات والصور من الموقع الرسمي biu.ac.id إلى موقعك. يمكن تشغيله أكثر من مرة دون تكرار.', '<a class="ad-b g" href="https://biu.ac.id/" target="_blank" rel="noopener"><i class="fas fa-external-link-alt"></i> موقع بنيان</a>');
?>
<div class="cp-kpis">
  <div class="cp-kpi" style="--c:#1f7a45"><i class="far fa-images"></i><span><b><?= $gal ?></b><span>صورة في المعرض</span></span></div>
  <div class="cp-kpi" style="--c:#2c5fd0"><i class="fas fa-sitemap"></i><span><b><?= $have('org_chart.jpg') ? 'نعم' : 'لا' ?></b><span>صور الصفحات</span></span></div>
  <div class="cp-kpi" style="--c:#c9962a"><i class="fas fa-home"></i><span><b><?= $have('front-about.jpg') ? 'نعم' : 'لا' ?></b><span>صور الرئيسية</span></span></div>
  <?php if ($done): ?><div class="cp-kpi" style="--c:#9b1f45"><i class="far fa-newspaper"></i><span><b><?= $stats['news'] ?></b><span>خبر ومقال أُضيف الآن</span></span></div><?php endif; ?>
</div>

<?php if ($log): ?>
<div class="cp-card" style="margin-bottom:18px">
  <h2><i class="fas fa-clipboard-list"></i> نتيجة الاستيراد</h2>
  <p>نُزّلت <b><?= $stats['img'] ?></b> صورة، وأُضيف <b><?= $stats['news'] ?></b> خبر ومقال، وتُرك <b><?= $stats['skip'] ?></b> موجود من قبل.</p>
  <ul style="margin:0;padding-inline-start:20px;line-height:2">
  <?php foreach (array_slice($log, 0, 60) as $l): ?><li style="color:<?= $l[0] === 'ok' ? '#1f7a45' : '#9b1f45' ?>"><?= $h($l[1]) ?></li><?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<form method="post" class="cp-card">
  <?= uwciv_csrf_field() ?>
  <h2><i class="fas fa-cloud-download-alt"></i> ماذا تريد أن تستورد؟</h2>
  <p>تُحفظ الصور في <code dir="ltr">assets/images/biu</code>، والأخبار في قاعدة البيانات الرئيسية فتظهر في صفحة الأخبار والصفحة الرئيسية، وتُعدَّل بعد ذلك من «الأخبار».</p>
  <div style="display:grid;gap:10px;margin:14px 0">
    <label style="display:flex;gap:10px;align-items:center"><input type="checkbox" name="what[]" value="pages" checked> صور الصفحات (الهيكل التنظيمي، الاعتمادات، شعارات الشركاء، جداول الدبلومات، التقويم)</label>
    <label style="display:flex;gap:10px;align-items:center"><input type="checkbox" name="what[]" value="home" checked> صور الصفحة الرئيسية والسلايدر</label>
    <label style="display:flex;gap:10px;align-items:center"><input type="checkbox" name="what[]" value="news" checked> الأخبار (15 خبرًا تقريبًا) مع صورها</label>
    <label style="display:flex;gap:10px;align-items:center"><input type="checkbox" name="what[]" value="blog" checked> المقالات (3 مقالات) مع صورها</label>
    <label style="display:flex;gap:10px;align-items:center"><input type="checkbox" name="what[]" value="gallery" checked> معرض الصور (55 صورة تقريبًا — قد يستغرق دقيقة)</label>
  </div>
  <button class="ad-b p" type="submit"><i class="fas fa-download"></i> ابدأ الاستيراد</button>
</form>
<?php uwciv_admin_close(); ?>
