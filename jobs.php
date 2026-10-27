<?php
/* الوظائف — الانضمام إلى أعضاء هيئة التدريس وفريق الجامعة */
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/inc/shell.php';
require_once __DIR__ . '/inc/jobs.php';
require_once __DIR__ . '/inc/settings.php';
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
if (empty($_SESSION['jobs_csrf'])) $_SESSION['jobs_csrf'] = bin2hex(random_bytes(16));

$TR = uwciv_job_tracks(); $ST = uwciv_job_status(); $POS = array_values(array_filter(uwciv_jobs_positions(), function ($p) { return !empty($p['open']); }));
$posById = []; foreach ($POS as $p) $posById[$p['id']] = $p;
$C = uwciv_contact();
$LANGS = ['العربية', 'الإنجليزية', 'البنغالية', 'الأردية', 'لغة أخرى'];
$v = array_fill_keys(['name', 'email', 'phone', 'country', 'track', 'pos', 'college', 'degree', 'spec', 'rank', 'exp', 'cover', 'link'], '');
$v['langs'] = [];
$err = []; $done = null; $found = null; $trackErr = '';
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };

/* ===== تقديم طلب ===== */
if (($_POST['act'] ?? '') === 'apply') {
    foreach ($v as $k => $_) if ($k !== 'langs') $v[$k] = trim(mb_substr((string) ($_POST[$k] ?? ''), 0, $k === 'cover' ? 3000 : 200));
    $v['langs'] = array_values(array_intersect((array) ($_POST['langs'] ?? []), $LANGS));
    if (!hash_equals($_SESSION['jobs_csrf'], (string) ($_POST['csrf'] ?? '')) || !empty($_POST['website'])) $err['form'] = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    $now = time(); $_SESSION['jobs_try'] = array_values(array_filter($_SESSION['jobs_try'] ?? [], function ($t) use ($now) { return $t > $now - 3600; }));
    if (count($_SESSION['jobs_try']) >= 5) $err['form'] = 'تجاوزت عدد الطلبات المسموح خلال ساعة.';
    if (mb_strlen($v['name']) < 5) $err['name'] = 'اكتب الاسم الثلاثي';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $err['email'] = 'بريد إلكتروني غير صحيح';
    if (!preg_match('/^\+?[0-9 ()-]{7,20}$/', $v['phone'])) $err['phone'] = 'رقم غير صحيح';
    if ($v['country'] === '') $err['country'] = 'اكتب الدولة';
    if (!isset($TR[$v['track']])) $err['track'] = 'اختر المسار';
    if ($v['pos'] !== '' && !isset($posById[$v['pos']])) $v['pos'] = '';
    if ($v['college'] !== '' && !isset($UWCIV_COLLEGES[$v['college']])) $v['college'] = '';
    if (!in_array($v['degree'], uwciv_job_degrees(), true)) $err['degree'] = 'اختر المؤهل';
    if (mb_strlen($v['spec']) < 3) $err['spec'] = 'اكتب التخصص';
    if ($v['link'] !== '' && !filter_var($v['link'], FILTER_VALIDATE_URL)) $err['link'] = 'رابط غير صحيح';
    if (empty($_POST['agree'])) $err['agree'] = 'يجب الإقرار بصحة البيانات';
    $cv = '';
    if (!$err) {
        $f = $_FILES['cv'] ?? null;
        if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) $err['cv'] = 'أرفق السيرة الذاتية';
        elseif ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 8 * 1024 * 1024) $err['cv'] = 'الملف أكبر من 8 ميجابايت أو تعذّر رفعه';
        elseif ((string) @file_get_contents($f['tmp_name'], false, null, 0, 5) !== '%PDF-') $err['cv'] = 'السيرة الذاتية يجب أن تكون PDF';
        else {
            $dir = uwciv_jobs_dir() . '/files'; if (!is_dir($dir)) @mkdir($dir, 0750, true);
            $cv = 'cv-' . date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.pdf';
            if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $cv)) { $err['cv'] = 'تعذّر حفظ الملف'; $cv = ''; }
        }
    }
    if (!$err) {
        $apps = uwciv_jobs_apps();
        $ref = uwciv_jobs_new_ref($apps);
        $app = $v + ['ref' => $ref, 'cv' => $cv, 'status' => 'new', 'created' => date('c'), 'history' => [['new', date('c'), '']], 'note' => ''];
        $apps[] = $app;
        if (uwciv_jobs_save_apps($apps)) {
            $_SESSION['jobs_try'][] = $now;
            $_SESSION['jobs_csrf'] = bin2hex(random_bytes(16));
            $posT = $v['pos'] ? $posById[$v['pos']]['title'] : 'تقديم عام';
            $done = ['ref' => $ref, 'name' => $v['name'], 'email' => $v['email'], 'pos' => $posT];
            require_once __DIR__ . '/inc/mailer.php';
            $rows = ['رقم الطلب' => $ref, 'الاسم' => $v['name'], 'البريد' => $v['email'], 'الجوال' => $v['phone'], 'الدولة' => $v['country'], 'المسار' => $TR[$v['track']][0], 'الشاغر' => $posT, 'المؤهل' => $v['degree'] . ' — ' . $v['spec'], 'الخبرة' => $v['exp'] . ' سنوات'];
            $tbl = '<table cellpadding="6">'; foreach ($rows as $k => $x) $tbl .= '<tr><td style="color:#5b6f62">' . $k . '</td><td><b>' . $h($x) . '</b></td></tr>'; $tbl .= '</table>';
            uwciv_mail(UWCIV_MAIL_TO, 'طلب انضمام جديد ' . $ref . ' — ' . $v['name'], '<h3 style="color:#12935a">طلب انضمام جديد</h3>' . $tbl . '<p><a href="https://your-domain.com/admin/jobs.php">مراجعة الطلب في لوحة التوظيف</a></p>', $v['email'], $v['name']);
            uwciv_mail($v['email'], 'استلمنا طلب انضمامك ' . $ref . ' — جامعة بنيان العالمية', '<h3 style="color:#12935a">مرحبًا ' . $h($v['name']) . '،</h3><p>شكرًا لرغبتك في الانضمام إلى جامعة بنيان العالمية. استلمنا طلبك بنجاح.</p>' . $tbl . '<p>تابع حالة طلبك في أي وقت من: <a href="https://your-domain.com/jobs#track">your-domain.com/jobs</a> برقم الطلب وبريدك الإلكتروني.</p>');
            foreach ($v as $k => $_) $v[$k] = $k === 'langs' ? [] : '';
        } else $err['form'] = 'تعذّر حفظ الطلب، حاول مرة أخرى.';
    }
}

/* ===== متابعة طلب ===== */
if (($_POST['act'] ?? '') === 'track') {
    $ref = strtoupper(trim((string) ($_POST['ref'] ?? ''))); $em = mb_strtolower(trim((string) ($_POST['temail'] ?? '')));
    $now = time(); $_SESSION['track_try'] = array_values(array_filter($_SESSION['track_try'] ?? [], function ($t) use ($now) { return $t > $now - 600; }));
    if (count($_SESSION['track_try']) >= 10) $trackErr = 'محاولات كثيرة، حاول بعد 10 دقائق.';
    else {
        $_SESSION['track_try'][] = $now;
        foreach (uwciv_jobs_apps() as $a) if ($a['ref'] === $ref && mb_strtolower($a['email']) === $em) { $found = $a; break; }
        if (!$found) $trackErr = 'لم نجد طلبًا بهذا الرقم وهذا البريد. تأكد من البيانات كما في رسالة التأكيد.';
    }
}
$open = $err && !isset($err['form']) || ($err['form'] ?? '') ? true : (isset($_GET['pos']) && isset($posById[$_GET['pos']]));
if (isset($_GET['pos']) && isset($posById[$_GET['pos']]) && !$v['pos']) { $v['pos'] = $_GET['pos']; $v['track'] = $posById[$_GET['pos']]['track']; }
$e = function ($k) use ($err) { return isset($err[$k]) ? '<small class="jb-err">' . htmlspecialchars($err[$k]) . '</small>' : ''; };
uwciv_open('الوظائف', t('about'), 'انضم إلى أعضاء هيئة التدريس وفريق جامعة بنيان العالمية.');
?>
<link rel="stylesheet" href="/assets/css/uwciv-jobs.css?v=2">

<section class="jb-hero">
  <div class="jb-hero-txt">
    <span class="jb-kick">مسارك المهني في الجامعة</span>
    <h2>الانضمام إلى أعضاء<br><em>هيئة التدريس</em></h2>
    <p>قدّم طلب الانضمام، وتابع حالته لحظة بلحظة، أو تواصل مع لجنة التوظيف — من مكان واحد وبخطوات واضحة.</p>
    <div class="jb-stats"><div><b><?= count($POS) ?></b><span>شاغرًا مفتوحًا</span></div><div><b><?= count($UWCIV_COLLEGES) ?></b><span>كليات ومعهد</span></div><div><b>4</b><span>لغات تدريس</span></div></div>
  </div>
  <div class="jb-actions">
    <a href="#apply" class="jb-main" data-open-apply>
      <span class="jb-tag">التقديم متاح</span><span class="jb-no">01</span>
      <i class="fas fa-user-tie"></i><b>قدّم طلب الانضمام</b><small>نموذج واحد مرتب، يستغرق 5 دقائق</small>
      <span class="jb-go">ابدأ التقديم <i class="fas fa-arrow-left"></i></span>
    </a>
    <a href="#track" class="jb-side"><i class="fas fa-search-location"></i><span><b>متابعة الطلب</b><small>تحقق من حالة طلبك برقم الطلب وبريدك.</small><em>تحقق من الحالة <i class="fas fa-chevron-left"></i></em></span></a>
    <a href="https://wa.me/<?= $C['digits'] ?>?text=<?= rawurlencode('السلام عليكم، أستفسر عن الانضمام لهيئة التدريس في جامعة بنيان العالمية') ?>" target="_blank" rel="noopener" class="jb-side"><i class="fab fa-whatsapp"></i><span><b>تواصل معنا</b><small>تحدث مع لجنة التوظيف قبل التقديم أو بعده.</small><em>تواصل الآن <i class="fas fa-chevron-left"></i></em></span></a>
  </div>
</section>

<div class="jb-steps"><b><span>03</span> رحلتك في ثلاث خطوات</b><div><i>1</i>جهّز سيرتك الذاتية PDF</div><div><i>2</i>أرسل طلبك واحتفظ برقمه</div><div><i>3</i>تابع الحالة حتى المقابلة والقبول</div></div>

<?php if ($done): ?>
<section class="jb-done" id="apply">
  <div class="jb-burst"><i class="fas fa-check"></i></div>
  <h3>تم إرسال طلبك بنجاح، <?= $h($done['name']) ?></h3>
  <p>احتفظ برقم الطلب لمتابعة حالته. أرسلنا نسخة إلى <b dir="ltr"><?= $h($done['email']) ?></b></p>
  <div class="jb-ref"><small>رقم الطلب</small><b dir="ltr"><?= $done['ref'] ?></b><button type="button" onclick="navigator.clipboard&&navigator.clipboard.writeText('<?= $done['ref'] ?>');this.textContent='تم النسخ ✓'">نسخ</button></div>
  <p class="jb-small">الشاغر: <?= $h($done['pos']) ?> · تراجع اللجنة الطلبات عادة خلال 7–14 يوم عمل.</p>
</section>
<?php endif; ?>

<!-- الشواغر -->
<section class="jb-sec" id="positions">
  <div class="jb-h"><div><span class="jb-kick">الفرص المتاحة</span><h3>الشواغر المفتوحة الآن</h3></div>
    <div class="jb-filters"><button class="on" data-f="">الكل</button><?php foreach ($TR as $k => $t): if (!array_filter($POS, function ($p) use ($k) { return $p['track'] === $k; })) continue; ?><button data-f="<?= $k ?>" style="--c:<?= $t[2] ?>"><i class="fas <?= $t[1] ?>"></i> <?= $t[0] ?></button><?php endforeach; ?></div></div>
  <div class="jb-grid">
    <?php foreach ($POS as $p): $t = $TR[$p['track']] ?? $TR['faculty']; $col = $p['college'] ? ($UWCIV_COLLEGES[$p['college']] ?? null) : null; ?>
    <article class="jb-pos" style="--c:<?= $t[2] ?>" data-track="<?= $p['track'] ?>">
      <div class="jb-pos-top"><i class="fas <?= $col ? $col['i'] : $t[1] ?>"></i><span><?= $t[0] ?></span></div>
      <h4><?= $h($p['title']) ?></h4>
      <p><?= $h($p['desc']) ?></p>
      <div class="jb-meta"><?php if ($col): ?><span><i class="fas fa-university"></i><?= $h(str_replace('كلية ', '', $col['n'])) ?></span><?php endif; ?><span><i class="fas fa-map-marker-alt"></i><?= $h($p['type']) ?></span></div>
      <a href="?pos=<?= $h($p['id']) ?>#apply" class="jb-apply" data-pos="<?= $h($p['id']) ?>" data-track="<?= $p['track'] ?>">قدّم على هذا الشاغر <i class="fas fa-arrow-left"></i></a>
    </article>
    <?php endforeach; ?>
    <article class="jb-pos jb-general" style="--c:#0b3119"><div class="jb-pos-top"><i class="fas fa-paper-plane"></i><span>تقديم عام</span></div><h4>لم تجد الشاغر المناسب؟</h4><p>أرسل طلبًا عامًا بتخصصك، ونتواصل معك فور فتح شاغر يناسب خبرتك.</p><a href="#apply" class="jb-apply" data-pos="" data-track="">قدّم طلبًا عامًا <i class="fas fa-arrow-left"></i></a></article>
  </div>
</section>

<!-- نموذج التقديم -->
<section class="jb-sec jb-form-wrap<?= $open ? ' open' : '' ?>" id="apply"<?= $done ? ' hidden' : '' ?>>
  <div class="jb-h"><div><span class="jb-kick">نموذج الانضمام</span><h3>قدّم طلبك</h3></div></div>
  <form class="jb-form" method="post" enctype="multipart/form-data" novalidate id="jbForm">
    <input type="hidden" name="act" value="apply"><input type="hidden" name="csrf" value="<?= $_SESSION['jobs_csrf'] ?>">
    <input type="text" name="website" class="jb-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
    <?php if ($err): ?><div class="jb-alert"><i class="fas fa-exclamation-circle"></i> <?= $h($err['form'] ?? 'يرجى تصحيح الحقول المظللة.') ?></div><?php endif; ?>

    <fieldset><legend><i>1</i> البيانات الشخصية</legend>
      <div class="jb-g">
        <label class="w">الاسم الثلاثي *<input name="name" value="<?= $h($v['name']) ?>" required placeholder="مثال: د. عبد الرحمن محمد علي"><?= $e('name') ?></label>
        <label>البريد الإلكتروني *<input type="email" name="email" value="<?= $h($v['email']) ?>" dir="ltr" required><?= $e('email') ?></label>
        <label>الجوال (واتساب) *<input name="phone" value="<?= $h($v['phone']) ?>" dir="ltr" required placeholder="+880 …"><?= $e('phone') ?></label>
        <label>الدولة *<input name="country" value="<?= $h($v['country']) ?>" required><?= $e('country') ?></label>
      </div></fieldset>

    <fieldset><legend><i>2</i> المسار والشاغر</legend>
      <div class="jb-tracks"><?php foreach ($TR as $k => $t): ?><label style="--c:<?= $t[2] ?>"><input type="radio" name="track" value="<?= $k ?>" <?= $v['track'] === $k ? 'checked' : '' ?> required><span><i class="fas <?= $t[1] ?>"></i><?= $t[0] ?></span></label><?php endforeach; ?></div><?= $e('track') ?>
      <div class="jb-g">
        <label>الشاغر<select name="pos" id="jbPos"><option value="">تقديم عام</option><?php foreach ($POS as $p): ?><option value="<?= $h($p['id']) ?>" data-track="<?= $p['track'] ?>" <?= $v['pos'] === $p['id'] ? 'selected' : '' ?>><?= $h($p['title']) ?></option><?php endforeach; ?></select></label>
        <label>الكلية المفضلة<select name="college"><option value="">— بدون تحديد —</option><?php foreach ($UWCIV_COLLEGES as $s => $c): ?><option value="<?= $s ?>" <?= $v['college'] === $s ? 'selected' : '' ?>><?= $h($c['n']) ?></option><?php endforeach; ?></select></label>
      </div></fieldset>

    <fieldset><legend><i>3</i> المؤهلات والخبرة</legend>
      <div class="jb-g">
        <label>أعلى مؤهل *<select name="degree" required><option value="">اختر</option><?php foreach (uwciv_job_degrees() as $d): ?><option <?= $v['degree'] === $d ? 'selected' : '' ?>><?= $d ?></option><?php endforeach; ?></select><?= $e('degree') ?></label>
        <label>التخصص الدقيق *<input name="spec" value="<?= $h($v['spec']) ?>" required placeholder="مثال: الحديث وعلومه"><?= $e('spec') ?></label>
        <label>الرتبة العلمية<select name="rank"><option value="">—</option><?php foreach (uwciv_job_ranks() as $r): ?><option <?= $v['rank'] === $r ? 'selected' : '' ?>><?= $r ?></option><?php endforeach; ?></select></label>
        <label>سنوات الخبرة<input type="number" min="0" max="60" name="exp" value="<?= $h($v['exp']) ?>"></label>
        <div class="w jb-langs"><span>لغات التدريس</span><?php foreach ($LANGS as $l): ?><label><input type="checkbox" name="langs[]" value="<?= $l ?>" <?= in_array($l, $v['langs'], true) ? 'checked' : '' ?>><span><?= $l ?></span></label><?php endforeach; ?></div>
        <label class="w">نبذة عنك ولماذا تريد الانضمام<textarea name="cover" rows="4" maxlength="3000" placeholder="خبراتك التدريسية، أبحاثك، الإجازات العلمية…"><?= $h($v['cover']) ?></textarea></label>
        <label class="w">رابط أعمالك (اختياري)<input name="link" value="<?= $h($v['link']) ?>" dir="ltr" placeholder="Google Scholar / LinkedIn / موقع شخصي"><?= $e('link') ?></label>
      </div></fieldset>

    <fieldset><legend><i>4</i> السيرة الذاتية والإرسال</legend>
      <label class="jb-drop" id="jbDrop"><input type="file" name="cv" accept="application/pdf" required><span class="in"><i class="fas fa-file-upload"></i><b>اسحب السيرة الذاتية هنا أو اضغط للاختيار</b><small>PDF فقط · حتى 8 ميجابايت</small></span><span class="pv"></span></label><?= $e('cv') ?>
      <label class="jb-agree"><input type="checkbox" name="agree" value="1" required> أقر بأن البيانات صحيحة، وأوافق على معالجتها لأغراض التوظيف وفق <a href="/info.php?p=privacy" target="_blank">سياسة الخصوصية</a>.</label><?= $e('agree') ?>
      <button class="jb-send" id="jbSend"><i class="fas fa-paper-plane"></i> إرسال طلب الانضمام</button>
    </fieldset>
  </form>
</section>

<!-- متابعة الطلب -->
<section class="jb-sec jb-track" id="track">
  <div class="jb-track-in">
    <div><span class="jb-kick">متابعة الطلب</span><h3>أين وصل طلبك؟</h3><p>أدخل رقم الطلب والبريد الذي سجّلت به لعرض الحالة وملاحظات اللجنة.</p>
      <form method="post" action="#track" class="jb-tform"><input type="hidden" name="act" value="track">
        <input name="ref" placeholder="رقم الطلب: JOB-26-0001A" dir="ltr" required value="<?= $h($_POST['ref'] ?? '') ?>">
        <input type="email" name="temail" placeholder="البريد الإلكتروني" dir="ltr" required value="<?= $h($_POST['temail'] ?? '') ?>">
        <button><i class="fas fa-search"></i> تحقق</button></form>
      <?php if ($trackErr): ?><div class="jb-alert" style="margin-top:12px"><i class="fas fa-exclamation-circle"></i> <?= $h($trackErr) ?></div><?php endif; ?>
    </div>
    <div class="jb-result">
      <?php if ($found): $cur = $found['status']; $keys = array_keys($ST); $flow = ['new', 'review', 'interview', in_array($cur, ['rejected', 'waitlist'], true) ? $cur : 'accepted']; $ci = array_search($cur, $flow, true); ?>
        <div class="jb-res-h"><span dir="ltr"><?= $h($found['ref']) ?></span><b style="--c:<?= $ST[$cur][2] ?>"><i class="fas <?= $ST[$cur][1] ?>"></i> <?= $ST[$cur][0] ?></b></div>
        <ol class="jb-tl"><?php foreach ($flow as $i => $s): ?><li class="<?= $i < $ci ? 'ok' : ($i === $ci ? 'now' : '') ?>" style="--c:<?= $ST[$s][2] ?>"><i class="fas <?= $ST[$s][1] ?>"></i><span><b><?= $ST[$s][0] ?></b><?php foreach ($found['history'] as $hx) if ($hx[0] === $s) { echo '<small>' . date('Y/m/d', strtotime($hx[1])) . '</small>'; break; } ?></span></li><?php endforeach; ?></ol>
        <p class="jb-res-msg"><?= $h($ST[$cur][3]) ?></p>
        <?php if (!empty($found['note'])): ?><div class="jb-note"><b><i class="fas fa-comment-dots"></i> رسالة من لجنة التوظيف</b><?= nl2br($h($found['note'])) ?></div><?php endif; ?>
      <?php else: ?>
        <div class="jb-res-empty"><i class="fas fa-route"></i><b>ستظهر حالة طلبك هنا</b><span>تم الاستلام ← قيد المراجعة ← مقابلة ← قرار</span></div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- لماذا الجامعة -->
<section class="jb-sec">
  <div class="jb-h"><div><span class="jb-kick">لماذا تنضم إلينا</span><h3>بيئة علمية تحتفي بك</h3></div></div>
  <div class="jb-why">
    <div><i class="fas fa-globe-asia"></i><b>طلاب من دول متعددة</b><span>علّم طلابًا من آسيا وإفريقيا والعالم العربي بلغات متعددة.</span></div>
    <div><i class="fas fa-laptop-house"></i><b>مرونة كاملة</b><span>تدريس عن بُعد أو مدمج، بجداول تراعي ارتباطاتك.</span></div>
    <div><i class="fas fa-book-reader"></i><b>بحث ونشر</b><span>المجلة العلمية ومعهد البحوث يدعمان إنتاجك العلمي.</span></div>
    <div><i class="fas fa-award"></i><b>تطوير وترقية</b><span>تدريب مستمر ولائحة ترقيات واضحة وعادلة.</span></div>
  </div>
</section>

<script>
(function(){
  var wrap=document.querySelector('.jb-form-wrap'), form=document.getElementById('jbForm'), pos=document.getElementById('jbPos');
  function openForm(p,t){if(!wrap||wrap.hidden)return;wrap.classList.add('open');if(p!==undefined){pos.value=p||'';}if(t){var r=form.querySelector('input[name=track][value="'+t+'"]');if(r)r.checked=true;}setTimeout(function(){wrap.scrollIntoView({behavior:'smooth',block:'start'});},30);}
  document.querySelectorAll('[data-open-apply]').forEach(function(a){a.onclick=function(e){if(wrap&&!wrap.hidden){e.preventDefault();openForm();}}});
  document.querySelectorAll('.jb-apply').forEach(function(a){a.onclick=function(e){if(wrap&&!wrap.hidden){e.preventDefault();openForm(a.dataset.pos,a.dataset.track);}}});
  if(pos)pos.onchange=function(){var o=pos.options[pos.selectedIndex];if(o.dataset.track){var r=form.querySelector('input[name=track][value="'+o.dataset.track+'"]');if(r)r.checked=true;}};
  document.querySelectorAll('.jb-filters button').forEach(function(b){b.onclick=function(){document.querySelectorAll('.jb-filters button').forEach(function(x){x.classList.toggle('on',x===b)});document.querySelectorAll('.jb-pos:not(.jb-general)').forEach(function(p){p.style.display=!b.dataset.f||p.dataset.track===b.dataset.f?'':'none';});};});
  var d=document.getElementById('jbDrop');if(d){var inp=d.querySelector('input'),pv=d.querySelector('.pv');
    function show(){var f=inp.files[0];d.classList.toggle('has',!!f);pv.innerHTML=f?'<i class="fas fa-file-pdf"></i><b></b><small>'+(f.size/1048576).toFixed(1)+' MB</small>':'';if(f)pv.querySelector('b').textContent=f.name;}
    inp.onchange=show;['dragenter','dragover'].forEach(function(t){d.addEventListener(t,function(e){e.preventDefault();d.classList.add('drag')})});['dragleave','drop'].forEach(function(t){d.addEventListener(t,function(e){e.preventDefault();d.classList.remove('drag')})});
    d.addEventListener('drop',function(e){if(e.dataTransfer.files.length){inp.files=e.dataTransfer.files;show();}});}
  if(form)form.addEventListener('submit',function(e){var bad=null;form.querySelectorAll('[required]').forEach(function(x){if(bad)return;if((x.type==='checkbox'&&!x.checked)||(x.type==='radio'&&!form.querySelector('input[name='+x.name+']:checked'))||(x.type!=='checkbox'&&x.type!=='radio'&&!x.value.trim()))bad=x;});
    if(bad){e.preventDefault();bad.closest('label,fieldset').scrollIntoView({behavior:'smooth',block:'center'});bad.focus&&bad.focus();return;}var b=document.getElementById('jbSend');setTimeout(function(){b.disabled=true;b.innerHTML='<i class="fas fa-spinner fa-spin"></i> جارٍ الإرسال…'},10);});
  <?php if ($found || $trackErr): ?>document.getElementById('track').scrollIntoView();<?php elseif ($done): ?>document.getElementById('apply').scrollIntoView();<?php elseif ($open): ?>openForm();<?php endif; ?>
})();
</script>
<?php uwciv_close(); ?>
