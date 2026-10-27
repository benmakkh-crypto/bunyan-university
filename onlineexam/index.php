<?php
/* بوابة الامتحانات الإلكترونية — الدخول والتسجيل */
require_once dirname(__DIR__) . '/inc/exam.php';
require_once dirname(__DIR__) . '/inc/shell.php';
require_once dirname(__DIR__) . '/inc/colleges.php';
if (oe_me()) { header('Location: account.php'); exit; }

$tab = ($_GET['tab'] ?? '') === 'new' ? 'new' : 'in'; $err = ''; $old = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $now = time(); $_SESSION['oe_try'] = array_values(array_filter($_SESSION['oe_try'] ?? [], function ($t) use ($now) { return $t > $now - 900; }));
    $old = $_POST; unset($old['password'], $old['cpassword']);
    if (!oe_csrf_ok()) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    elseif (count($_SESSION['oe_try']) >= 10) $err = 'محاولات كثيرة، انتظر 15 دقيقة.';
    elseif (trim($_POST['website'] ?? '') !== '') $err = 'تعذّر الإرسال.';
    else try {
        $db = oe_db();
        $email = strtolower(trim((string) ($_POST['email'] ?? ''))); $pw = (string) ($_POST['password'] ?? '');
        if (($_POST['do'] ?? '') === 'login') {
            $tab = 'in';
            $st = $db->prepare('SELECT name, email, password FROM user WHERE LOWER(email) = ? LIMIT 1'); $st->execute([$email]); $u = $st->fetch();
            if ($u && hash_equals((string) $u['password'], md5($pw))) {
                session_regenerate_id(true); $_SESSION['oe_email'] = $u['email']; $_SESSION['oe_name'] = $u['name']; unset($_SESSION['oe_try']);
                header('Location: account.php'); exit;
            }
            $_SESSION['oe_try'][] = $now; $err = 'البريد أو كلمة المرور غير صحيحة.';
        } else {
            $tab = 'new';
            $name = trim(mb_substr((string) ($_POST['name'] ?? ''), 0, 50));
            $gender = in_array($_POST['gender'] ?? '', ['M', 'F'], true) ? $_POST['gender'] : '';
            $dep = trim(mb_substr((string) ($_POST['college'] ?? ''), 0, 100));
            $mob = preg_replace('/\D/', '', (string) ($_POST['mob'] ?? ''));
            if (mb_strlen($name) < 3) $err = 'اكتب اسمك الكامل.';
            elseif (!$gender) $err = 'اختر الجنس.';
            elseif ($dep === '') $err = 'اختر الكلية.';
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 50) $err = 'اكتب بريدًا إلكترونيًا صحيحًا.';
            elseif (strlen($mob) < 7 || strlen($mob) > 15) $err = 'اكتب رقم جوال صحيحًا بالأرقام.';
            elseif (mb_strlen($pw) < 6) $err = 'كلمة المرور 6 أحرف على الأقل.';
            elseif ($pw !== (string) ($_POST['cpassword'] ?? '')) $err = 'كلمتا المرور غير متطابقتين.';
            else {
                $st = $db->prepare('SELECT COUNT(*) FROM user WHERE LOWER(email) = ?'); $st->execute([$email]);
                if ($st->fetchColumn()) $err = 'هذا البريد مسجّل مسبقًا — سجّل الدخول بدلًا من ذلك.';
                else {
                    $db->prepare('INSERT INTO user (name, gender, department, email, mob, password) VALUES (?, ?, ?, ?, ?, ?)')->execute([$name, $gender, $dep, $email, $mob, md5($pw)]);
                    session_regenerate_id(true); $_SESSION['oe_email'] = $email; $_SESSION['oe_name'] = $name;
                    header('Location: account.php?welcome=1'); exit;
                }
            }
        }
    } catch (Throwable $e) { $err = 'الخدمة غير متاحة الآن، حاول بعد قليل.'; }
}
$stats = ['q' => 0, 'u' => 0, 'h' => 0];
try { $db = oe_db(); $stats['q'] = (int) $db->query('SELECT COUNT(*) FROM quiz')->fetchColumn(); $stats['u'] = (int) $db->query('SELECT COUNT(*) FROM user')->fetchColumn(); $stats['h'] = (int) $db->query('SELECT COUNT(*) FROM history')->fetchColumn(); } catch (Throwable $e) {}
$h = 'uwciv_h'; $v = function ($k) use ($old, $h) { return $h($old[$k] ?? ''); };
uwciv_open('الامتحانات الإلكترونية', 'الخدمات الإلكترونية', 'اختبر معارفك في أي وقت ومن أي مكان: امتحانات مؤقّتة، تصحيح فوري، ونتيجة مفصّلة.');
?>
<link rel="stylesheet" href="/assets/css/uwciv-exam.css?v=1">
<section class="ox-hero">
  <div class="ox-intro">
    <small class="ox-kick"><i class="fas fa-laptop-code"></i> منصة التقييم الإلكتروني</small>
    <h2>امتحانك… <span>بضغطة زر</span></h2>
    <p>منصة جامعة بنيان العالمية للاختبارات الإلكترونية: ادخل بحسابك، اختر الامتحان، وأجب قبل انتهاء الوقت — تظهر نتيجتك وتصحيح كل سؤال فور التسليم.</p>
    <div class="ox-mock" aria-hidden="true">
      <div class="ox-mock-top"><span><i class="far fa-clock"></i> 09:42</span><b>سؤال 3 من 10</b><em><i style="width:30%"></i></em></div>
      <div class="ox-mock-q">ما الموضوع الرئيس لعلم أصول الفقه؟</div>
      <div class="ox-mock-o on"><i class="fas fa-check"></i> الأدلة الإجمالية وكيفية الاستفادة منها</div>
      <div class="ox-mock-o"><i></i> الأحكام التفصيلية</div>
      <div class="ox-mock-o"><i></i> تراجم الفقهاء</div>
    </div>
    <div class="ox-stats"><div><b><?= $stats['q'] ?></b><span>امتحان متاح</span></div><div><b><?= $stats['u'] ?></b><span>طالب مسجّل</span></div><div><b><?= $stats['h'] ?></b><span>محاولة مكتملة</span></div></div>
  </div>

  <div class="ox-auth" id="auth">
    <div class="ox-tabs" role="tablist"><button type="button" class="<?= $tab === 'in' ? 'on' : '' ?>" data-t="in"><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</button><button type="button" class="<?= $tab === 'new' ? 'on' : '' ?>" data-t="new"><i class="fas fa-user-plus"></i> حساب جديد</button></div>
    <?php if ($err): ?><div class="ox-err"><i class="fas fa-exclamation-circle"></i> <?= $h($err) ?></div><?php endif; ?>

    <form method="post" class="ox-form<?= $tab === 'in' ? ' on' : '' ?>" data-f="in" novalidate>
      <input type="hidden" name="csrf" value="<?= oe_csrf() ?>"><input type="hidden" name="do" value="login"><input class="ox-hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
      <h3>أهلًا بعودتك 👋</h3><p>ادخل ببريدك وكلمة المرور لعرض امتحاناتك.</p>
      <label>البريد الإلكتروني<div class="ox-in"><i class="far fa-envelope"></i><input type="email" name="email" required dir="ltr" autocomplete="email" value="<?= $tab === 'in' ? $v('email') : '' ?>" placeholder="name@example.com"></div></label>
      <label>كلمة المرور<div class="ox-in"><i class="fas fa-lock"></i><input type="password" name="password" required dir="ltr" autocomplete="current-password" placeholder="••••••••"><button type="button" class="ox-eye" aria-label="إظهار"><i class="far fa-eye"></i></button></div></label>
      <button class="ox-btn">دخول إلى البوابة <i class="fas fa-arrow-left"></i></button>
      <p class="ox-alt">ليس لديك حساب؟ <a href="?tab=new#auth" data-go="new">أنشئ حسابًا الآن</a> · <a href="/form1.php">نسيت كلمة المرور؟</a></p>
    </form>

    <form method="post" class="ox-form<?= $tab === 'new' ? ' on' : '' ?>" data-f="new" novalidate>
      <input type="hidden" name="csrf" value="<?= oe_csrf() ?>"><input type="hidden" name="do" value="register"><input class="ox-hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
      <h3>انضم إلى المنصة</h3><p>دقيقة واحدة وتبدأ أول امتحان.</p>
      <label>الاسم الكامل<div class="ox-in"><i class="far fa-user"></i><input name="name" required maxlength="50" value="<?= $v('name') ?>" placeholder="الاسم الثلاثي"></div></label>
      <div class="ox-two">
        <label>الجنس<div class="ox-seg"><label><input type="radio" name="gender" value="M" <?= ($old['gender'] ?? '') === 'M' ? 'checked' : '' ?>><span><i class="fas fa-male"></i> ذكر</span></label><label><input type="radio" name="gender" value="F" <?= ($old['gender'] ?? '') === 'F' ? 'checked' : '' ?>><span><i class="fas fa-female"></i> أنثى</span></label></div></label>
        <label>رقم الجوال<div class="ox-in"><i class="fas fa-mobile-alt"></i><input name="mob" inputmode="tel" dir="ltr" required value="<?= $v('mob') ?>" placeholder="+880…"></div></label>
      </div>
      <label>الكلية<div class="ox-in"><i class="fas fa-university"></i><select name="college" required><option value="">اختر كليتك</option><?php foreach ($UWCIV_COLLEGES as $c): ?><option <?= ($old['college'] ?? '') === $c['n'] ? 'selected' : '' ?>><?= $h($c['n']) ?></option><?php endforeach; ?></select></div></label>
      <label>البريد الإلكتروني<div class="ox-in"><i class="far fa-envelope"></i><input type="email" name="email" required dir="ltr" maxlength="50" value="<?= $tab === 'new' ? $v('email') : '' ?>" placeholder="name@example.com"></div></label>
      <div class="ox-two">
        <label>كلمة المرور<div class="ox-in"><i class="fas fa-key"></i><input type="password" name="password" required minlength="6" dir="ltr" autocomplete="new-password"></div></label>
        <label>تأكيدها<div class="ox-in"><i class="fas fa-redo"></i><input type="password" name="cpassword" required minlength="6" dir="ltr" autocomplete="new-password"></div></label>
      </div>
      <button class="ox-btn">إنشاء الحساب والبدء <i class="fas fa-arrow-left"></i></button>
      <p class="ox-alt">لديك حساب؟ <a href="?tab=in#auth" data-go="in">سجّل الدخول</a></p>
    </form>
  </div>
</section>

<section class="ox-how">
  <div><i class="fas fa-user-check"></i><b>سجّل دخولك</b><span>بحسابك في المنصة.</span></div>
  <div><i class="fas fa-list-ol"></i><b>اختر الامتحان</b><span>تظهر مدته وعدد أسئلته ودرجته.</span></div>
  <div><i class="fas fa-hourglass-half"></i><b>أجب قبل انتهاء الوقت</b><span>يُسلَّم تلقائيًا عند انتهاء المؤقّت.</span></div>
  <div><i class="fas fa-chart-pie"></i><b>نتيجة فورية</b><span>درجتك وتصحيح كل سؤال وترتيبك.</span></div>
</section>
<p class="ox-staff"><i class="fas fa-user-shield"></i> أستاذ أو مشرف؟ <a href="/onlineexam/dash.php">لوحة إدارة الامتحانات</a></p>
<script>
(function(){var tabs=document.querySelectorAll('.ox-tabs button'),forms=document.querySelectorAll('.ox-form');
function go(t){tabs.forEach(function(b){b.classList.toggle('on',b.dataset.t===t)});forms.forEach(function(f){f.classList.toggle('on',f.dataset.f===t)});var f=document.querySelector('.ox-form.on input:not([type=hidden]):not(.ox-hp)');if(f)f.focus();}
tabs.forEach(function(b){b.onclick=function(){go(b.dataset.t)}});document.querySelectorAll('[data-go]').forEach(function(a){a.onclick=function(e){e.preventDefault();go(a.dataset.go)}});
document.querySelectorAll('.ox-eye').forEach(function(e){e.onclick=function(){var i=e.parentNode.querySelector('input');i.type=i.type==='password'?'text':'password';e.innerHTML=i.type==='password'?'<i class="far fa-eye"></i>':'<i class="far fa-eye-slash"></i>';}});})();
</script>
<?php uwciv_close(); ?>
