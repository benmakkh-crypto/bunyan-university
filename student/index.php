<?php
/* سجّل معنا — طلب الالتحاق بجامعة بنيان العالمية (معالج من 5 خطوات) */
require_once $_SERVER['DOCUMENT_ROOT'] . '/db_config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/shell.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/colleges.php';
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
if (empty($_SESSION['apply_csrf'])) $_SESSION['apply_csrf'] = bin2hex(random_bytes(16));

$FEES = ['dip' => 300, 'bac' => 500, 'mas' => 700, 'phd' => 1000];
$STAGE_LABEL = ['dip' => 'مرحلة الدبلوم', 'bac' => 'مرحلة البكالوريوس', 'mas' => 'مرحلة الماجستير', 'phd' => 'مرحلة الدكتوراه'];
$COUNTRIES = ['بنغلاديش', 'الهند', 'باكستان', 'نيبال', 'سريلانكا', 'المملكة العربية السعودية', 'الإمارات', 'الكويت', 'قطر', 'البحرين', 'سلطنة عمان', 'اليمن', 'مصر', 'السودان', 'العراق', 'الأردن', 'سوريا', 'لبنان', 'فلسطين', 'ليبيا', 'تونس', 'الجزائر', 'المغرب', 'موريتانيا', 'الصومال', 'جيبوتي', 'نيجيريا', 'السنغال', 'مالي', 'تشاد', 'النيجر', 'إثيوبيا', 'كينيا', 'تنزانيا', 'جنوب أفريقيا', 'إندونيسيا', 'ماليزيا', 'تايلاند', 'الفلبين', 'تركيا', 'أفغانستان', 'إيران', 'أوزبكستان', 'كازاخستان', 'المملكة المتحدة', 'فرنسا', 'ألمانيا', 'الولايات المتحدة', 'كندا', 'أستراليا', 'دولة أخرى'];
$PRIV = $_SERVER['DOCUMENT_ROOT'] . '/_private/applications';

$v = array_fill_keys(['fname', 'lname', 'email', 'phone', 'birth', 'gender', 'id_iden', 'id_pass', 'address', 'country', 'stage', 'college', 'dept'], '');
$err = []; $done = null;

/* ترقية بسيطة لمرة واحدة: توسيع حقول جدول الطلبات */
function apply_migrate($cn)
{
    $flag = $_SERVER['DOCUMENT_ROOT'] . '/_private/.info_v2';
    if (is_file($flag)) return;
    @mysqli_query($cn, "ALTER TABLE info MODIFY fname varchar(60) NOT NULL, MODIFY lname varchar(60) NOT NULL, MODIFY email varchar(150) NOT NULL, MODIFY address varchar(255) NOT NULL, MODIFY depart varchar(200) NOT NULL, MODIFY imgpay varchar(250) NOT NULL DEFAULT ''");
    @file_put_contents($flag, date('c'));
}

function apply_upload($field, $prefix, $dir, &$err, $required)
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) { if ($required) $err[$field] = 'هذا المستند مطلوب'; return ''; }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) { $err[$field] = 'تعذّر رفع الملف، حاول مرة أخرى'; return ''; }
    if ($f['size'] > 5 * 1024 * 1024) { $err[$field] = 'حجم الملف أكبر من 5 ميجابايت'; return ''; }
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $okExt = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    $mime = function_exists('mime_content_type') ? (string) @mime_content_type($f['tmp_name']) : '';
    if (!in_array($ext, $okExt, true) || ($mime && !preg_match('#^(image/(jpeg|png|webp)|application/pdf)$#', $mime))) { $err[$field] = 'الصيغ المسموحة: JPG أو PNG أو PDF'; return ''; }
    if (!is_dir($dir)) @mkdir($dir, 0750, true);
    $name = $prefix . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) { $err[$field] = 'تعذّر حفظ الملف'; return ''; }
    return 'app:' . $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($v as $k => $_) $v[$k] = trim((string) ($_POST[$k] ?? ''));
    if (!hash_equals($_SESSION['apply_csrf'], (string) ($_POST['csrf'] ?? ''))) $err['form'] = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    if (!empty($_POST['website'])) $err['form'] = 'تعذّر إرسال الطلب.'; // مصيدة الروبوتات
    if (mb_strlen($v['fname']) < 2) $err['fname'] = 'اكتب الاسم الأول';
    if (mb_strlen($v['lname']) < 2) $err['lname'] = 'اكتب اسم العائلة';
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $err['email'] = 'بريد إلكتروني غير صحيح';
    if (!preg_match('/^\+?[0-9 ()-]{7,20}$/', $v['phone'])) $err['phone'] = 'رقم جوال غير صحيح';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $v['birth'])) $err['birth'] = 'حدّد تاريخ الميلاد';
    if (!in_array($v['gender'], ['ذكر', 'أنثى'], true)) $err['gender'] = 'اختر الجنس';
    if ($v['id_iden'] === '' && $v['id_pass'] === '') $err['id_iden'] = 'اكتب رقم الهوية أو رقم الجواز';
    if (mb_strlen($v['address']) < 4) $err['address'] = 'اكتب العنوان';
    if (!in_array($v['country'], $COUNTRIES, true)) $err['country'] = 'اختر الدولة';
    if (!isset($STAGE_LABEL[$v['stage']])) $err['stage'] = 'اختر المرحلة الدراسية';
    $col = $UWCIV_COLLEGES[$v['college']] ?? null;
    if (!$col) $err['college'] = 'اختر الكلية';
    elseif (!in_array($v['stage'], $col['st'], true)) $err['college'] = 'هذه الكلية لا تقدم المرحلة المختارة';
    if ($col && $v['dept'] !== '' && !in_array($v['dept'], array_column($col['deps'], 0), true)) $v['dept'] = '';
    if (empty($_POST['agree'])) $err['agree'] = 'يجب الموافقة على صحة البيانات';

    $img1 = $imgpass = ''; $cn = null;
    if (!$err) {
        $cn = db_mysqli('logweb');
        if (!$cn) $err['form'] = 'الخدمة غير متاحة الآن، حاول بعد قليل.';
    }
    if (!$err) {
        $img1 = apply_upload('img1', 'id-', $PRIV, $err, true);
        $imgpass = apply_upload('imgpass', 'pass-', $PRIV, $err, false);
    }
    if (!$err) {
        {
            apply_migrate($cn);
            $depart = $col['n'] . ($v['dept'] !== '' ? ' — قسم ' . $v['dept'] : '');
            $stage = $STAGE_LABEL[$v['stage']];
            $st = mysqli_prepare($cn, "INSERT INTO info (fname, lname, email, phone, birth, id_iden, id_pass, gender, address, country, stage, depart, img1, imgpass, imgpay) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?, '')");
            $s = function ($x, $n) { return mb_substr($x, 0, $n); };
            $p = [$s($v['fname'], 60), $s($v['lname'], 60), $s($v['email'], 150), $s($v['phone'], 30), $v['birth'], $s($v['id_iden'], 80), $s($v['id_pass'], 100), $v['gender'], $s($v['address'], 255), $v['country'], $stage, $s($depart, 200), $img1, $imgpass];
            if ($st && mysqli_stmt_bind_param($st, str_repeat('s', 14), ...$p) && mysqli_stmt_execute($st)) {
                $id = mysqli_insert_id($cn);
                $ref = 'BIU-' . date('y') . '-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
                $done = ['ref' => $ref, 'name' => $v['fname'], 'email' => $v['email'], 'stage' => $stage, 'col' => $col['n'], 'fee' => $FEES[$v['stage']]];
                require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/mailer.php';
                $h = function ($x) { return htmlspecialchars($x, ENT_QUOTES, 'UTF-8'); };
                $rows = ['رقم الطلب' => $ref, 'الاسم' => $v['fname'] . ' ' . $v['lname'], 'البريد' => $v['email'], 'الجوال' => $v['phone'], 'الدولة' => $v['country'], 'المرحلة' => $stage, 'الكلية' => $depart];
                $tbl = '<table cellpadding="6" style="border-collapse:collapse">'; foreach ($rows as $k => $x) $tbl .= '<tr><td style="color:#5b6f62">' . $k . '</td><td><b>' . $h($x) . '</b></td></tr>'; $tbl .= '</table>';
                uwciv_mail(UWCIV_MAIL_TO, 'طلب التحاق جديد ' . $ref . ' — ' . $v['fname'] . ' ' . $v['lname'], '<h3 style="color:#12935a">طلب التحاق جديد</h3>' . $tbl . '<p><a href="https://your-domain.com/student/admin/list.php">عرض الطلبات في لوحة المشرف</a></p>', $v['email'], $v['fname'] . ' ' . $v['lname']);
                uwciv_mail($v['email'], 'استلمنا طلبك ' . $ref . ' — جامعة بنيان العالمية', '<h3 style="color:#12935a">مرحبًا ' . $h($v['fname']) . '،</h3><p>شكرًا لتقديمك إلى جامعة بنيان العالمية. استلمنا طلبك بنجاح وهذه بياناته:</p>' . $tbl . '<p>سيراجع قسم القبول والتسجيل طلبك ويتواصل معك عبر البريد أو الجوال لإكمال الإجراءات وطريقة سداد الرسوم.</p><p>للاستفسار: info@biuedu.com</p>');
                $_SESSION['apply_csrf'] = bin2hex(random_bytes(16));
                foreach ($v as $k => $_) $v[$k] = '';
            } else {
                $err['form'] = 'تعذّر حفظ الطلب، حاول مرة أخرى أو راسلنا على info@biuedu.com';
                @error_log('apply insert: ' . mysqli_error($cn));
            }
        }
    }
} elseif (!empty($_GET['c']) && isset($UWCIV_COLLEGES[$_GET['c']])) {
    $v['college'] = $_GET['c'];
}
if (!empty($_GET['s']) && isset($STAGE_LABEL[$_GET['s']]) && $v['stage'] === '') $v['stage'] = $_GET['s'];

/* الخطوة التي فيها أول خطأ */
$stepOf = ['fname' => 1, 'lname' => 1, 'email' => 1, 'phone' => 1, 'birth' => 1, 'gender' => 1, 'id_iden' => 2, 'address' => 2, 'country' => 2, 'stage' => 3, 'college' => 3, 'img1' => 4, 'imgpass' => 4, 'agree' => 5, 'form' => 5];
$startStep = 1; foreach ($err as $k => $_) { $startStep = $stepOf[$k] ?? 5; break; }
$e = function ($k) use ($err) { return isset($err[$k]) ? '<small class="a-err">' . htmlspecialchars($err[$k]) . '</small>' : ''; };
$val = function ($k) use ($v) { return htmlspecialchars($v[$k], ENT_QUOTES, 'UTF-8'); };

/* بيانات الكليات للواجهة */
$jsCols = [];
foreach ($UWCIV_COLLEGES as $slug => $c) $jsCols[$slug] = ['n' => $c['n'], 'c' => $c['c'], 'i' => $c['i'], 'g' => $c['g'], 'st' => $c['st'], 'd' => array_column($c['deps'], 0)];

uwciv_open('سجّل معنا', 'طلب الالتحاق', 'خمس خطوات بسيطة تفصلك عن بداية رحلتك العلمية — قدّم طلبك الآن ويتواصل معك قسم القبول والتسجيل.');
?>
<link rel="stylesheet" href="/assets/css/uwciv-apply.css?v=1">

<?php if ($done): ?>
<div class="a-done">
  <div class="a-burst"><i class="fas fa-check"></i></div>
  <h2>تم استلام طلبك بنجاح، <?= htmlspecialchars($done['name']) ?> 🎉</h2>
  <p>أرسلنا تأكيدًا إلى بريدك <b dir="ltr"><?= htmlspecialchars($done['email']) ?></b></p>
  <div class="a-ref"><small>رقم الطلب</small><b dir="ltr"><?= $done['ref'] ?></b><button type="button" onclick="navigator.clipboard&&navigator.clipboard.writeText('<?= $done['ref'] ?>');this.textContent='تم النسخ ✓'">نسخ</button></div>
  <div class="a-sum"><span><i class="fas fa-layer-group"></i><?= $done['stage'] ?></span><span><i class="fas fa-university"></i><?= $done['col'] ?></span><span><i class="fas fa-tag"></i>الرسوم <?= $done['fee'] ?> $ للفصل</span></div>
  <ol class="a-next">
    <li><b>مراجعة الطلب</b><small>يراجع قسم القبول بياناتك ومستنداتك خلال 3–5 أيام عمل.</small></li>
    <li><b>التواصل معك</b><small>نرسل لك نتيجة المراجعة وطريقة سداد الرسوم على البريد أو الجوال.</small></li>
    <li><b>تفعيل الحساب</b><small>بعد السداد تحصل على حساب البوابة الإلكترونية وتبدأ الدراسة.</small></li>
  </ol>
  <div class="a-actions"><a class="a-btn" href="/elctro.php"><i class="fas fa-layer-group"></i> البوابة الإلكترونية</a><a class="a-btn ghost" href="/index.php">الصفحة الرئيسية</a></div>
</div>
<?php else: ?>

<div class="a-shell" id="apply" data-start="<?= $startStep ?>">
  <!-- الشريط الجانبي -->
  <aside class="a-side">
    <div class="a-side-head"><i class="fas fa-user-graduate"></i><div><b>طلب الالتحاق</b><small>العام الجامعي <?= date('Y') ?>/<?= date('Y') + 1 ?></small></div></div>
    <ol class="a-steps">
      <li data-s="1"><span>1</span><div><b>البيانات الشخصية</b><small>الاسم والتواصل</small></div></li>
      <li data-s="2"><span>2</span><div><b>الهوية والعنوان</b><small>الوثائق الرسمية</small></div></li>
      <li data-s="3"><span>3</span><div><b>البرنامج الدراسي</b><small>المرحلة والكلية</small></div></li>
      <li data-s="4"><span>4</span><div><b>المستندات</b><small>صورة الهوية والجواز</small></div></li>
      <li data-s="5"><span>5</span><div><b>المراجعة والإرسال</b><small>تأكيد الطلب</small></div></li>
    </ol>
    <div class="a-pick" id="pick">
      <small>اختيارك</small>
      <b id="pickStage">لم تختر المرحلة بعد</b>
      <span id="pickCol">—</span>
      <em id="pickFee"></em>
    </div>
    <div class="a-help"><i class="fas fa-headset"></i><div><b>تحتاج مساعدة؟</b><a href="/form2.php">الدعم الفني</a> · <a href="/ask.php">الأسئلة الشائعة</a></div></div>
  </aside>

  <!-- النموذج -->
  <form class="a-card" method="post" enctype="multipart/form-data" novalidate id="aform">
    <input type="hidden" name="csrf" value="<?= $_SESSION['apply_csrf'] ?>">
    <input type="text" name="website" tabindex="-1" autocomplete="off" class="a-hp" aria-hidden="true">
    <div class="a-bar"><i id="abar"></i></div>
    <?php if (isset($err['form'])): ?><div class="a-alert"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($err['form']) ?></div><?php endif; ?>
    <?php if ($err && !isset($err['form'])): ?><div class="a-alert"><i class="fas fa-exclamation-circle"></i> يرجى تصحيح الحقول المظللة بالأحمر.</div><?php endif; ?>

    <!-- 1 -->
    <section class="a-step" data-step="1">
      <header><span class="a-kick">الخطوة 1 من 5</span><h2>البيانات الشخصية</h2><p>اكتب اسمك كما هو في الهوية أو جواز السفر.</p></header>
      <div class="a-grid">
        <label class="a-f"><span>الاسم الأول <i>*</i></span><div class="a-in"><i class="fas fa-user"></i><input name="fname" value="<?= $val('fname') ?>" maxlength="60" required autocomplete="given-name" placeholder="مثال: محمد"></div><?= $e('fname') ?></label>
        <label class="a-f"><span>اسم العائلة <i>*</i></span><div class="a-in"><i class="fas fa-user-tag"></i><input name="lname" value="<?= $val('lname') ?>" maxlength="60" required autocomplete="family-name" placeholder="مثال: عبد الرحمن"></div><?= $e('lname') ?></label>
        <label class="a-f"><span>البريد الإلكتروني <i>*</i></span><div class="a-in"><i class="fas fa-envelope"></i><input type="email" name="email" value="<?= $val('email') ?>" maxlength="150" required dir="ltr" autocomplete="email" placeholder="name@example.com"></div><?= $e('email') ?></label>
        <label class="a-f"><span>رقم الجوال (واتساب) <i>*</i></span><div class="a-in"><i class="fab fa-whatsapp"></i><input type="tel" name="phone" value="<?= $val('phone') ?>" maxlength="20" required dir="ltr" autocomplete="tel" placeholder="+880 1XXX XXXXXX" pattern="\+?[0-9 ()-]{7,20}"></div><?= $e('phone') ?></label>
        <label class="a-f"><span>تاريخ الميلاد <i>*</i></span><div class="a-in"><i class="fas fa-birthday-cake"></i><input type="date" name="birth" value="<?= $val('birth') ?>" required max="<?= date('Y-m-d', strtotime('-12 years')) ?>"></div><?= $e('birth') ?></label>
        <div class="a-f"><span>الجنس <i>*</i></span>
          <div class="a-seg">
            <label><input type="radio" name="gender" value="ذكر" <?= $v['gender'] === 'ذكر' ? 'checked' : '' ?> required><span><i class="fas fa-male"></i> ذكر</span></label>
            <label><input type="radio" name="gender" value="أنثى" <?= $v['gender'] === 'أنثى' ? 'checked' : '' ?>><span><i class="fas fa-female"></i> أنثى</span></label>
          </div><?= $e('gender') ?></div>
      </div>
    </section>

    <!-- 2 -->
    <section class="a-step" data-step="2">
      <header><span class="a-kick">الخطوة 2 من 5</span><h2>الهوية والعنوان</h2><p>يكفي رقم الهوية الوطنية أو رقم جواز السفر.</p></header>
      <div class="a-grid">
        <label class="a-f"><span>رقم الهوية الوطنية</span><div class="a-in"><i class="fas fa-id-card"></i><input name="id_iden" value="<?= $val('id_iden') ?>" maxlength="80" dir="ltr"></div><?= $e('id_iden') ?></label>
        <label class="a-f"><span>رقم جواز السفر</span><div class="a-in"><i class="fas fa-passport"></i><input name="id_pass" value="<?= $val('id_pass') ?>" maxlength="100" dir="ltr"></div></label>
        <label class="a-f"><span>الدولة <i>*</i></span><div class="a-in"><i class="fas fa-globe-asia"></i><select name="country" required><option value="">اختر الدولة</option><?php foreach ($COUNTRIES as $c): ?><option <?= $v['country'] === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?></select></div><?= $e('country') ?></label>
        <label class="a-f a-wide"><span>العنوان الكامل <i>*</i></span><div class="a-in top"><i class="fas fa-map-marker-alt"></i><textarea name="address" rows="3" maxlength="255" required placeholder="المدينة، الحي، الشارع"><?= $val('address') ?></textarea></div><?= $e('address') ?></label>
      </div>
    </section>

    <!-- 3 -->
    <section class="a-step" data-step="3">
      <header><span class="a-kick">الخطوة 3 من 5</span><h2>اختر برنامجك</h2><p>اختر المرحلة أولًا، ثم الكلية والقسم.</p></header>
      <div class="a-stages">
        <?php foreach (['dip' => ['#0e9aa7', 'fa-seedling', 'برامج تأسيسية'], 'bac' => ['#12935a', 'fa-book-open', '4 سنوات'], 'mas' => ['#2c5fd0', 'fa-feather-alt', 'مقررات ورسالة'], 'phd' => ['#9b1f45', 'fa-crown', 'بحث متقدم']] as $sk => $s): ?>
        <label class="a-stage" style="--sc:<?= $s[0] ?>"><input type="radio" name="stage" value="<?= $sk ?>" <?= $v['stage'] === $sk ? 'checked' : '' ?> required>
          <span><i class="fas <?= $s[1] ?>"></i><b><?= $UWCIV_STAGES[$sk][0] ?></b><small><?= $s[2] ?></small><em><?= $FEES[$sk] ?> $</em></span></label>
        <?php endforeach; ?>
      </div>
      <?= $e('stage') ?>
      <div class="a-colhead"><b>الكلية <i>*</i></b><div class="a-in sm"><i class="fas fa-search"></i><input id="colq" type="search" placeholder="ابحث عن كلية…" aria-label="بحث عن كلية"></div></div>
      <input type="hidden" name="college" id="college" value="<?= $val('college') ?>">
      <div class="a-cols" id="cols"></div>
      <?= $e('college') ?>
      <div class="a-depts" id="depts" hidden><b>القسم (اختياري)</b><div id="deptList"></div></div>
      <input type="hidden" name="dept" id="dept" value="<?= $val('dept') ?>">
    </section>

    <!-- 4 -->
    <section class="a-step" data-step="4">
      <header><span class="a-kick">الخطوة 4 من 5</span><h2>المستندات</h2><p>صورة واضحة بصيغة JPG أو PNG أو PDF، والحد الأقصى 5 ميجابايت لكل ملف. مستنداتك محفوظة بشكل سري ولا يطّلع عليها إلا قسم القبول.</p></header>
      <div class="a-drops">
        <label class="a-drop" data-for="img1"><input type="file" name="img1" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
          <span class="a-drop-in"><i class="fas fa-id-card"></i><b>صورة الهوية أو الجواز <i>*</i></b><small>اسحب الملف هنا أو اضغط للاختيار</small></span><span class="a-prev"></span></label>
        <label class="a-drop" data-for="imgpass"><input type="file" name="imgpass" accept=".jpg,.jpeg,.png,.webp,.pdf">
          <span class="a-drop-in"><i class="fas fa-file-alt"></i><b>آخر شهادة دراسية</b><small>اختياري — يسرّع مراجعة طلبك</small></span><span class="a-prev"></span></label>
      </div>
      <?= $e('img1') ?><?= $e('imgpass') ?>
      <?php if ($err && isset($_FILES['img1']) && !isset($err['img1'])): ?><p class="a-err">لأمان بياناتك، أعد اختيار المستندات قبل الإرسال.</p><?php endif; ?>
    </section>

    <!-- 5 -->
    <section class="a-step" data-step="5">
      <header><span class="a-kick">الخطوة 5 من 5</span><h2>راجع طلبك</h2><p>تأكد من صحة البيانات قبل الإرسال.</p></header>
      <div class="a-review" id="review"></div>
      <label class="a-agree"><input type="checkbox" name="agree" value="1" required> <span>أقر بأن البيانات المدخلة صحيحة، وأوافق على <a href="/info.php?p=terms" target="_blank">الشروط والأحكام</a> و<a href="/info.php?p=privacy" target="_blank">سياسة الخصوصية</a>.</span></label>
      <?= $e('agree') ?>
    </section>

    <footer class="a-nav">
      <button type="button" class="a-btn ghost" id="prev"><i class="fas fa-arrow-right"></i> السابق</button>
      <span class="a-count" id="count">1 / 5</span>
      <button type="button" class="a-btn" id="next">التالي <i class="fas fa-arrow-left"></i></button>
      <button type="submit" class="a-btn gold" id="send" hidden><i class="fas fa-paper-plane"></i> إرسال الطلب</button>
    </footer>
  </form>
</div>

<script>
(function(){
  var COLS=<?= json_encode($jsCols, JSON_UNESCAPED_UNICODE) ?>, FEES=<?= json_encode($FEES) ?>,
      SN={dip:'الدبلوم',bac:'البكالوريوس',mas:'الماجستير',phd:'الدكتوراه'};
  var f=document.getElementById('aform'), steps=[].slice.call(f.querySelectorAll('.a-step')), side=document.querySelectorAll('.a-steps li'),
      cur=+document.getElementById('apply').dataset.start||1, maxReached=cur;
  var $=function(s){return f.querySelector(s)}, colIn=$('#college'), deptIn=$('#dept');

  function stage(){var r=$('input[name=stage]:checked');return r?r.value:'';}
  function renderCols(){
    var s=stage(), q=($('#colq').value||'').trim(), box=$('#cols'); box.innerHTML='';
    Object.keys(COLS).forEach(function(k){var c=COLS[k];
      if(q&&(c.n+' '+c.d.join(' ')).indexOf(q)<0)return;
      var ok=!s||c.st.indexOf(s)>-1, b=document.createElement('button');
      b.type='button';b.className='a-col'+(colIn.value===k?' on':'')+(ok?'':' off');b.style.setProperty('--cc',c.c);
      b.innerHTML='<i class="fas '+c.i+'"></i><span>'+c.n.replace('كلية ','')+'</span>'+(ok?'':'<small>غير متاحة لهذه المرحلة</small>');
      b.disabled=!ok; b.onclick=function(){colIn.value=k;deptIn.value='';renderCols();renderDepts();summary();};
      box.appendChild(b);});
    if(colIn.value&&s&&COLS[colIn.value]&&COLS[colIn.value].st.indexOf(s)<0){colIn.value='';deptIn.value='';}
  }
  function renderDepts(){var c=COLS[colIn.value], w=$('#depts'), l=$('#deptList'); l.innerHTML='';
    if(!c){w.hidden=true;return;} w.hidden=false;
    c.d.forEach(function(d){var b=document.createElement('button');b.type='button';b.className='a-chip'+(deptIn.value===d?' on':'');b.style.setProperty('--cc',c.c);b.textContent=d;
      b.onclick=function(){deptIn.value=deptIn.value===d?'':d;renderDepts();summary();};l.appendChild(b);});}
  function summary(){var s=stage(), c=COLS[colIn.value];
    document.getElementById('pickStage').textContent=s?'مرحلة '+SN[s]:'لم تختر المرحلة بعد';
    document.getElementById('pickCol').textContent=c?c.n+(deptIn.value?' — قسم '+deptIn.value:''):'—';
    document.getElementById('pickFee').textContent=s?'الرسوم: '+FEES[s]+' $ للفصل':'';
    document.getElementById('pick').style.setProperty('--cc',c?c.c:'');}

  function fieldErr(el,msg){var w=el.closest('.a-f,.a-step');var old=el.closest('.a-f')&&el.closest('.a-f').querySelector('.a-err.js');if(old)old.remove();
    var host=el.closest('.a-f'); if(host){host.classList.toggle('bad',!!msg); if(msg){var s=document.createElement('small');s.className='a-err js';s.textContent=msg;host.appendChild(s);}}}
  function validate(n){var ok=true, sec=steps[n-1];
    sec.querySelectorAll('.a-err.js').forEach(function(x){x.remove()}); sec.querySelectorAll('.bad').forEach(function(x){x.classList.remove('bad')});
    function need(el,msg){if(!ok)return; fieldErr(el,msg); el.focus&&el.focus(); ok=false;}
    if(n===1){[['fname','اكتب الاسم الأول'],['lname','اكتب اسم العائلة']].forEach(function(p){var el=$('[name='+p[0]+']');if(el.value.trim().length<2)need(el,p[1]);});
      var em=$('[name=email]');if(ok&&!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(em.value.trim()))need(em,'بريد إلكتروني غير صحيح');
      var ph=$('[name=phone]');if(ok&&!/^\+?[0-9 ()-]{7,20}$/.test(ph.value.trim()))need(ph,'رقم جوال غير صحيح');
      var bd=$('[name=birth]');if(ok&&!bd.value)need(bd,'حدّد تاريخ الميلاد');
      if(ok&&!$('input[name=gender]:checked'))need($('input[name=gender]'),'اختر الجنس');}
    if(n===2){var a=$('[name=id_iden]'),b=$('[name=id_pass]');if(!a.value.trim()&&!b.value.trim())need(a,'اكتب رقم الهوية أو رقم الجواز');
      var co=$('[name=country]');if(ok&&!co.value)need(co,'اختر الدولة');
      var ad=$('[name=address]');if(ok&&ad.value.trim().length<4)need(ad,'اكتب العنوان');}
    if(n===3){if(!stage()){ok=false;toast('اختر المرحلة الدراسية');}else if(!colIn.value){ok=false;toast('اختر الكلية');}}
    if(n===4){var fi=$('[name=img1]');if(!fi.files.length){ok=false;toast('أرفق صورة الهوية أو الجواز');}}
    return ok;}
  function toast(m){var t=document.createElement('div');t.className='a-toast';t.textContent=m;document.body.appendChild(t);setTimeout(function(){t.classList.add('in')},10);setTimeout(function(){t.classList.remove('in');setTimeout(function(){t.remove()},300)},2600);}

  function review(){var g=function(n){var el=$('[name='+n+']');return el?el.value.trim():''}, gen=$('input[name=gender]:checked'), c=COLS[colIn.value], fi=$('[name=img1]').files[0], fp=$('[name=imgpass]').files[0];
    var rows=[[1,'البيانات الشخصية',[['الاسم',g('fname')+' '+g('lname')],['البريد',g('email')],['الجوال',g('phone')],['الميلاد',g('birth')],['الجنس',gen?gen.value:'—']]],
      [2,'الهوية والعنوان',[['الهوية',g('id_iden')||'—'],['الجواز',g('id_pass')||'—'],['الدولة',g('country')],['العنوان',g('address')]]],
      [3,'البرنامج',[['المرحلة',stage()?SN[stage()]:'—'],['الكلية',c?c.n:'—'],['القسم',deptIn.value||'—'],['الرسوم',stage()?FEES[stage()]+' $ للفصل':'—']]],
      [4,'المستندات',[['الهوية/الجواز',fi?fi.name:'—'],['الشهادة',fp?fp.name:'—']]]];
    var esc=function(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML};
    document.getElementById('review').innerHTML=rows.map(function(r){return '<div class="a-rv"><div class="a-rv-h"><b>'+r[1]+'</b><button type="button" data-go="'+r[0]+'"><i class="fas fa-pen"></i> تعديل</button></div><dl>'+r[2].map(function(x){return '<dt>'+x[0]+'</dt><dd dir="auto">'+esc(x[1])+'</dd>'}).join('')+'</dl></div>'}).join('');
    document.querySelectorAll('[data-go]').forEach(function(b){b.onclick=function(){go(+b.dataset.go)}});}

  function go(n){cur=n;maxReached=Math.max(maxReached,n);
    steps.forEach(function(s,i){s.classList.toggle('on',i===n-1)});
    side.forEach(function(li,i){li.classList.toggle('on',i===n-1);li.classList.toggle('ok',i<n-1);li.classList.toggle('reach',i<maxReached)});
    document.getElementById('abar').style.width=(n/5*100)+'%';
    document.getElementById('count').textContent=n+' / 5';
    document.getElementById('prev').style.visibility=n>1?'visible':'hidden';
    document.getElementById('next').hidden=n===5; document.getElementById('send').hidden=n!==5;
    if(n===5)review();
    var top=document.getElementById('apply').getBoundingClientRect().top+window.scrollY-90; if(window.scrollY>top)window.scrollTo({top:top,behavior:'smooth'});}
  document.getElementById('next').onclick=function(){if(validate(cur))go(cur+1);};
  document.getElementById('prev').onclick=function(){go(cur-1);};
  side.forEach(function(li){li.onclick=function(){var n=+li.dataset.s;if(n<cur||n<=maxReached){for(var i=1;i<n;i++)if(!validate(i)){go(i);return;}go(n);}}});
  f.addEventListener('submit',function(ev){for(var i=1;i<=4;i++)if(!validate(i)){ev.preventDefault();go(i);return;}
    if(!$('[name=agree]').checked){ev.preventDefault();toast('يجب الموافقة على صحة البيانات');return;}
    var b=document.getElementById('send');b.disabled=true;b.innerHTML='<i class="fas fa-spinner fa-spin"></i> جارٍ الإرسال…';});
  f.addEventListener('keydown',function(e){if(e.key==='Enter'&&e.target.tagName==='INPUT'&&cur<5){e.preventDefault();document.getElementById('next').click();}});
  f.querySelectorAll('input[name=stage]').forEach(function(r){r.onchange=function(){renderCols();renderDepts();summary();}});
  $('#colq').oninput=renderCols;
  f.querySelectorAll('input,select,textarea').forEach(function(el){el.addEventListener('input',function(){var h=el.closest('.a-f');if(h){h.classList.remove('bad');var x=h.querySelector('.a-err');if(x)x.remove();}})});

  /* سحب وإفلات المستندات مع معاينة */
  f.querySelectorAll('.a-drop').forEach(function(d){var inp=d.querySelector('input'),pv=d.querySelector('.a-prev');
    function show(){var fl=inp.files[0];pv.innerHTML='';d.classList.toggle('has',!!fl);if(!fl)return;
      if(fl.size>5*1024*1024){toast('حجم الملف أكبر من 5 ميجابايت');inp.value='';d.classList.remove('has');return;}
      if(/^image\//.test(fl.type)){var im=new Image();im.src=URL.createObjectURL(fl);pv.appendChild(im);}else{pv.innerHTML='<i class="fas fa-file-pdf"></i>';}
      var n=document.createElement('b');n.textContent=fl.name;pv.appendChild(n);
      var x=document.createElement('button');x.type='button';x.innerHTML='<i class="fas fa-times"></i>';x.title='إزالة';x.onclick=function(e){e.preventDefault();inp.value='';show();};pv.appendChild(x);}
    inp.onchange=show;
    ['dragenter','dragover'].forEach(function(t){d.addEventListener(t,function(e){e.preventDefault();d.classList.add('drag')})});
    ['dragleave','drop'].forEach(function(t){d.addEventListener(t,function(e){e.preventDefault();d.classList.remove('drag')})});
    d.addEventListener('drop',function(e){if(e.dataTransfer.files.length){inp.files=e.dataTransfer.files;show();}});});

  renderCols();renderDepts();summary();go(cur);
})();
</script>
<?php endif; ?>
<?php uwciv_close(); ?>
