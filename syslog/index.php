<?php
/* تسجيل الدخول الموحّد — جامعة بنيان العالمية */
require_once $_SERVER['DOCUMENT_ROOT'] . '/db_config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/settings.php';
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();

if (isset($_SESSION['SESSION_EMAIL']) || !empty($_SESSION['uwciv_super'])) { header('Location: ../elctro.php'); exit; }
if (empty($_SESSION['login_csrf'])) $_SESSION['login_csrf'] = bin2hex(random_bytes(16));

$msg = ''; $type = 'err'; $email = '';
$conn = db_mysqli('logweb');

// تفعيل الحساب من رابط البريد
if (isset($_GET['verification']) && $conn) {
    $code = (string) $_GET['verification'];
    $st = $conn->prepare('UPDATE user SET code = "" WHERE code = ? AND code <> ""');
    $st->bind_param('s', $code); $st->execute();
    if ($st->affected_rows > 0) { $msg = 'تم تفعيل حسابك بنجاح، يمكنك تسجيل الدخول الآن.'; $type = 'ok'; }
}

if (isset($_POST['submit'])) {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['pass'] ?? '');
    $now = time();
    $_SESSION['login_try'] = array_values(array_filter($_SESSION['login_try'] ?? [], function ($t) use ($now) { return $t > $now - 900; }));
    if (!hash_equals($_SESSION['login_csrf'], (string) ($_POST['csrf'] ?? ''))) $msg = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    elseif (count($_SESSION['login_try']) >= 8) $msg = 'محاولات كثيرة خاطئة. انتظر 15 دقيقة ثم حاول مجددًا.';
    elseif ($email === '' || $password === '') $msg = 'اكتب اسم المستخدم أو البريد وكلمة المرور.';
    elseif (uwciv_admin_login($email, $password)) {
        $_SESSION['SESSION_EMAIL'] = 'المشرف العام';
        unset($_SESSION['login_try']);
        header('Location: ../elctro.php'); exit;
    } elseif (!$conn) $msg = 'تعذّر الاتصال بالخادم حاليًا، حاول بعد قليل.';
    else {
        $st = $conn->prepare('SELECT email, pass FROM user WHERE email = ? LIMIT 1');
        $st->bind_param('s', $email); $st->execute();
        $row = $st->get_result()->fetch_assoc();
        $ok = $row && (hash_equals((string) $row['pass'], md5($password)) || (strlen($row['pass']) > 40 && password_verify($password, $row['pass'])));
        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['SESSION_EMAIL'] = $row['email'];
            unset($_SESSION['login_try']);
            header('Location: ../elctro.php'); exit;
        }
        $_SESSION['login_try'][] = $now;
        $msg = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
    }
}
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
$C = uwciv_contact();
?><!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>تسجيل الدخول | جامعة بنيان العالمية</title>
<meta name="robots" content="noindex">
<meta name="theme-color" content="#0b3119">
<link rel="manifest" href="/manifest.webmanifest">
<link rel="icon" type="image/png" sizes="48x48" href="/assets/app/favicon-48.png"><link rel="shortcut icon" href="/favicon.ico"><link rel="stylesheet" href="/assets/css/plugins/all.min.css">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
<style>
:root{--ink:#06210f;--ink2:#0b3119;--ink3:#114326;--teal:#1f7a45;--cyan:#46c07a;--soft:#e4f4ea;--mist:#f3f7f4;--text:#13261b;--muted:#5b6f62}
*{box-sizing:border-box}
html,body{margin:0;min-height:100%;overflow-x:hidden;font-family:'Tajawal','Segoe UI',Tahoma,sans-serif;color:var(--text);background:var(--mist)}
a{color:var(--teal);text-decoration:none}
.lg{min-height:100vh;display:grid;grid-template-columns:1.05fr 1fr}
/* الجانب الأيمن: الهوية */
.lg-side{position:relative;overflow:hidden;color:#fff;padding:48px 56px;display:flex;flex-direction:column;justify-content:space-between;background:linear-gradient(160deg,var(--ink3),var(--ink) 70%)}
.lg-side video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.22;mix-blend-mode:screen}
.lg-side:before{content:"";position:absolute;inset:0;background:radial-gradient(700px 420px at 80% 10%,rgba(70,192,122,.28),transparent 60%),radial-gradient(500px 300px at 0% 100%,rgba(70,192,122,.18),transparent 60%)}
.lg-side>*{position:relative;z-index:1}
.lg-brand{display:flex;flex-direction:column;align-items:flex-start;gap:8px}
.lg-brand img{height:62px;filter:drop-shadow(0 6px 14px rgba(0,0,0,.4))}
.lg-brand small{font-size:11.5px;letter-spacing:.3em;color:#b9dcc6}
.lg-hero{max-width:520px}
.lg-hero .mark{width:120px;height:120px;border-radius:50%;display:grid;place-items:center;margin-bottom:26px;background:radial-gradient(circle,rgba(255,255,255,.12),rgba(255,255,255,.02));border:1px dashed rgba(255,255,255,.25);animation:lgF 6s ease-in-out infinite}
.lg-hero .mark img{width:96px}
@keyframes lgF{50%{transform:translateY(-8px)}}
.lg-hero h1{font-size:clamp(30px,3.6vw,46px);font-weight:900;line-height:1.3;margin:0 0 14px}
.lg-hero h1 em{font-style:normal;color:var(--cyan)}
.lg-hero p{color:#cfe6d7;font-size:17px;line-height:1.9;margin:0}
.lg-roles{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:30px}
.lg-roles div{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:14px;backdrop-filter:blur(4px)}
.lg-roles i{font-size:20px;color:var(--cyan);margin-bottom:8px;display:block}
.lg-roles b{display:block;font-size:14.5px}.lg-roles small{color:#b9dcc6;font-size:12.5px;line-height:1.6}
.lg-foot{display:flex;gap:18px;flex-wrap:wrap;color:#b9dcc6;font-size:13.5px}
.lg-foot a{color:#e6f4ec}.lg-foot i{color:var(--cyan);margin-left:6px}
/* النموذج */
.lg-main{display:flex;align-items:center;justify-content:center;padding:40px 24px;position:relative}
.lg-back{position:absolute;top:24px;left:24px;display:inline-flex;gap:8px;align-items:center;background:#fff;border-radius:30px;padding:9px 16px;font-weight:800;font-size:14px;color:var(--ink3);box-shadow:0 6px 16px -10px rgba(6,33,15,.4)}
.lg-card{width:100%;max-width:440px}
.lg-card h2{font-size:32px;font-weight:900;margin:0 0 6px;color:var(--ink)}
.lg-card>p{color:var(--muted);margin:0 0 26px;font-size:16px}
.lg-alert{display:flex;gap:10px;align-items:flex-start;border-radius:14px;padding:13px 16px;margin:0 0 18px;font-weight:700;font-size:14.5px;line-height:1.7;animation:lgS .4s}
.lg-alert.err{background:#fdecef;color:#9b1f45}.lg-alert.ok{background:var(--soft);color:var(--teal)}
@keyframes lgS{0%,100%{transform:none}25%{transform:translateX(-6px)}75%{transform:translateX(6px)}}
.lg-f{display:block;margin-bottom:16px}
.lg-f>span{display:flex;justify-content:space-between;font-weight:800;font-size:14.5px;margin-bottom:8px;color:var(--ink3)}
.lg-f>span a{font-weight:700;font-size:13.5px}
.lg-in{display:flex;align-items:center;gap:12px;background:#fff;border:2px solid #dfe9e3;border-radius:16px;padding:0 16px;transition:.25s}
.lg-in>i{color:var(--muted);width:18px;text-align:center;transition:.25s}
.lg-in input{flex:1;border:0;outline:none;background:transparent;height:56px;font-size:16px;font-family:inherit;color:var(--text);min-width:0}
.lg-in button{border:0;background:none;color:var(--muted);cursor:pointer;padding:6px;font-size:15px}
.lg-in:focus-within{border-color:var(--teal);box-shadow:0 0 0 4px rgba(31,122,69,.12)}
.lg-in:focus-within>i{color:var(--teal)}
.lg-caps{display:none;color:#b7791f;font-size:13px;font-weight:700;margin-top:6px}
.lg-btn{width:100%;height:58px;border:0;border-radius:16px;font-size:17.5px;font-weight:900;color:#fff;cursor:pointer;font-family:inherit;background:linear-gradient(135deg,#2fa060,var(--ink3));box-shadow:0 16px 30px -16px rgba(6,33,15,.8);display:flex;align-items:center;justify-content:center;gap:10px;transition:.25s;margin-top:6px}
.lg-btn:hover{transform:translateY(-2px)}
.lg-btn:disabled{opacity:.75;cursor:wait;transform:none}
.lg-or{display:flex;align-items:center;gap:12px;color:var(--muted);font-size:13.5px;margin:24px 0 16px}
.lg-or:before,.lg-or:after{content:"";flex:1;height:1px;background:#dfe9e3}
.lg-alt{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.lg-alt a{min-width:0;display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #dfe9e3;border-radius:14px;padding:12px 14px;font-weight:800;font-size:14px;color:var(--ink3);transition:.2s}
.lg-alt a:hover{border-color:var(--teal);transform:translateY(-2px)}
.lg-alt i{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;background:var(--soft);color:var(--teal)}
.lg-new{margin-top:26px;text-align:center;color:var(--muted);font-size:15px}
.lg-new a{font-weight:900}
.lg-sec{margin-top:18px;text-align:center;color:#8a9a90;font-size:12.5px}
@media(max-width:980px){.lg{grid-template-columns:1fr}.lg-side{padding:28px 22px;min-height:auto}.lg-hero .mark,.lg-roles,.lg-foot{display:none}.lg-hero h1{font-size:26px;margin:14px 0 6px}.lg-hero p{font-size:15px}.lg-back{position:static;margin-bottom:18px}.lg-main{flex-direction:column;padding:24px 18px 40px}}
@media(prefers-reduced-motion:reduce){.lg-hero .mark{animation:none}}
</style>
</head>
<body>
<div class="lg">
  <aside class="lg-side">
    <video autoplay muted loop playsinline preload="none" poster="/assets/video/pattern.jpg" aria-hidden="true"><source src="/assets/video/pattern.mp4" type="video/mp4"></video>
    <a class="lg-brand" href="/index.php" aria-label="جامعة بنيان العالمية"><img src="/assets/images/na-light.png" alt="جامعة بنيان العالمية"><small>BUNYAN INTERNATIONAL UNIVERSITY</small></a>
    <div class="lg-hero">
      <div class="mark"><img src="/assets/images/logo-light.png" alt=""></div>
      <h1>بوابتك الموحّدة إلى<br><em>جامعة بنيان العالمية</em></h1>
      <p>حساب واحد للوصول إلى المحاضرات والامتحانات والشهادات والمكتبة الرقمية ولوحات التحكم.</p>
      <div class="lg-roles">
        <div><i class="fas fa-user-graduate"></i><b>الطلاب</b><small>المحاضرات، الامتحانات، النتائج</small></div>
        <div><i class="fas fa-chalkboard-teacher"></i><b>هيئة التدريس</b><small>المقررات، الدرجات، المكتبة</small></div>
        <div><i class="fas fa-user-shield"></i><b>الإدارة</b><small>الطلبات، الأخبار، الإعدادات</small></div>
      </div>
    </div>
    <div class="lg-foot"><a href="https://wa.me/<?= $C['digits'] ?>" dir="ltr"><i class="fab fa-whatsapp"></i><?= $h($C['pretty']) ?></a><a href="mailto:info@biuedu.com"><i class="far fa-envelope"></i>info@biuedu.com</a><a href="/form2.php"><i class="fas fa-headset"></i>الدعم الفني</a></div>
  </aside>

  <main class="lg-main">
    <a class="lg-back" href="/index.php"><i class="fas fa-arrow-right"></i> الصفحة الرئيسية</a>
    <div class="lg-card">
      <h2>مرحبًا بعودتك 👋</h2>
      <p>سجّل الدخول لمتابعة دراستك أو إدارة حسابك.</p>
      <?php if ($msg): ?><div class="lg-alert <?= $type ?>"><i class="fas <?= $type === 'ok' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>" style="margin-top:4px"></i><span><?= $h($msg) ?></span></div><?php endif; ?>
      <form method="post" id="lgf" autocomplete="on">
        <input type="hidden" name="csrf" value="<?= $_SESSION['login_csrf'] ?>">
        <label class="lg-f"><span>البريد الإلكتروني أو اسم المستخدم</span>
          <div class="lg-in"><i class="far fa-user"></i><input name="email" value="<?= $h($email) ?>" required autocomplete="username" placeholder="name@example.com"></div></label>
        <label class="lg-f"><span>كلمة المرور <a href="forgot-password.php">نسيت كلمة المرور؟</a></span>
          <div class="lg-in"><i class="fas fa-lock"></i><input type="password" name="pass" id="lgp" required autocomplete="current-password" placeholder="••••••••" dir="ltr"><button type="button" id="lgt" aria-label="إظهار كلمة المرور"><i class="far fa-eye"></i></button></div>
          <div class="lg-caps" id="lgc"><i class="fas fa-exclamation-triangle"></i> زر الأحرف الكبيرة (Caps Lock) مفعّل</div></label>
        <button class="lg-btn" name="submit" value="1" id="lgb"><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</button>
      </form>
      <div class="lg-or">خدمات بدون تسجيل دخول</div>
      <div class="lg-alt">
        <a href="/pdf_grad/index.php"><i class="fas fa-certificate"></i>وثيقة التخرج</a>
        <a href="/student/index.php"><i class="fas fa-user-plus"></i>طلب التحاق</a>
        <a href="/colleges.php"><i class="fas fa-university"></i>دليل الكليات</a>
        <a href="/app"><i class="fas fa-mobile-alt"></i>تطبيق الجوال</a>
      </div>
      <p class="lg-new">ليس لديك حساب؟ <a href="register.php">أنشئ حسابًا جديدًا</a></p>
      <p class="lg-sec"><i class="fas fa-shield-alt"></i> اتصال آمن ومشفّر · لا تشارك كلمة المرور مع أحد</p>
    </div>
  </main>
</div>
<script>
(function(){var p=document.getElementById('lgp'),t=document.getElementById('lgt'),c=document.getElementById('lgc'),f=document.getElementById('lgf'),b=document.getElementById('lgb');
t.onclick=function(){var s=p.type==='password';p.type=s?'text':'password';t.innerHTML=s?'<i class="far fa-eye-slash"></i>':'<i class="far fa-eye"></i>';p.focus();};
p.addEventListener('keyup',function(e){c.style.display=e.getModifierState&&e.getModifierState('CapsLock')?'block':'none';});
f.addEventListener('submit',function(){setTimeout(function(){b.disabled=true;b.innerHTML='<i class="fas fa-spinner fa-spin"></i> جارٍ الدخول…';},10);});
if(!document.querySelector('[name=email]').value)document.querySelector('[name=email]').focus();})();
</script>
</body>
</html>
