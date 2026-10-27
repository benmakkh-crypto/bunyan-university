<?php
/* التحقق من وثيقة التخرج — صفحة عامة */
require_once $_SERVER['DOCUMENT_ROOT'] . '/db_config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/shell.php';
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
if (empty($_SESSION['grad_csrf'])) $_SESSION['grad_csrf'] = bin2hex(random_bytes(16));

$ref = trim((string) ($_POST['id_ref'] ?? ''));
$pass = trim((string) ($_POST['id_pass'] ?? ''));
$state = ''; $msg = ''; $token = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // حد المحاولات: 10 محاولات كل 10 دقائق
    $now = time(); $_SESSION['grad_try'] = array_values(array_filter($_SESSION['grad_try'] ?? [], function ($x) use ($now) { return $x > $now - 600; }));
    if (!hash_equals($_SESSION['grad_csrf'], (string) ($_POST['csrf'] ?? ''))) { $state = 'err'; $msg = 'انتهت صلاحية الصفحة، أعد المحاولة.'; }
    elseif (count($_SESSION['grad_try']) >= 10) { $state = 'err'; $msg = 'تجاوزت عدد المحاولات المسموح. حاول بعد 10 دقائق.'; }
    elseif ($ref === '' || $pass === '') { $state = 'err'; $msg = 'اكتب رقم المرجع ورقم الهوية.'; }
    else {
        $_SESSION['grad_try'][] = $now;
        try {
            $db = db_pdo('logweb');
            $st = $db->prepare('SELECT pdf_file FROM student WHERE id_ref = ? AND id_pass = ? LIMIT 1');
            $st->execute([$ref, $pass]);
            $f = $st->fetchColumn();
            if ($f && is_file(__DIR__ . '/admin/files/' . basename($f))) {
                $token = bin2hex(random_bytes(12));
                $_SESSION['grad_docs'][$token] = basename($f);
                $state = 'ok';
            } else { $state = 'none'; $msg = 'لم نجد وثيقة مطابقة. تأكد من رقم المرجع ورقم الهوية كما هما في سجلك.'; }
        } catch (Throwable $e) { $state = 'err'; $msg = 'الخدمة غير متاحة الآن، حاول بعد قليل.'; }
    }
}
$h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
uwciv_open('التحقق من وثيقة التخرج', 'الخدمات الإلكترونية', 'اعرض وثيقة تخرجك الرسمية أو تحقّق من صحتها خلال ثوانٍ، برقم المرجع ورقم الهوية.');
?>
<style>
.gv{display:grid;grid-template-columns:1.05fr .95fr;gap:28px;align-items:stretch}
.gv-card{background:#fff;border-radius:26px;box-shadow:var(--sh-3d);padding:34px 34px 30px;position:relative;overflow:hidden}
.gv-card:before{content:"";position:absolute;inset:0 0 auto 0;height:5px;background:linear-gradient(90deg,var(--teal),var(--cyan))}
.gv-kick{display:inline-flex;gap:8px;align-items:center;font-size:13px;font-weight:800;color:var(--teal);background:var(--cyan-soft);border-radius:20px;padding:5px 12px}
.gv-card h2{font-size:clamp(24px,3vw,30px);font-weight:900;color:var(--ink);margin:12px 0 6px}
.gv-card>p{color:var(--muted);margin:0 0 24px;font-size:15.5px}
.gv-f{display:block;margin:0 0 16px}
.gv-f>span{display:flex;justify-content:space-between;font-weight:800;font-size:14.5px;color:var(--ink-3);margin-bottom:8px}
.gv-f>span small{font-weight:600;color:var(--muted);font-size:12.5px}
.gv-in{display:flex;align-items:center;gap:12px;background:var(--mist);border:2px solid transparent;border-radius:16px;padding:0 16px;transition:.25s}
.gv-in i{color:var(--muted);width:18px;text-align:center}
.gv-in input{flex:1;border:0!important;background:transparent!important;height:56px;font-size:17px;font-weight:700;letter-spacing:1px;outline:none;box-shadow:none!important;padding:0!important;min-width:0}
.gv-in:focus-within{background:#fff;border-color:var(--teal);box-shadow:0 0 0 4px rgba(31,122,69,.12)}
.gv-in:focus-within i{color:var(--teal)}
.gv-in button{border:0;background:none;color:var(--muted);cursor:pointer;padding:6px}
.gv-btn{width:100%;display:flex;align-items:center;justify-content:center;gap:10px;border:0;border-radius:16px;height:58px;font-size:17px;font-weight:900;color:#fff;background:linear-gradient(135deg,var(--teal),var(--ink-3));box-shadow:0 14px 26px -14px rgba(6,33,15,.7);cursor:pointer;transition:.25s;font-family:inherit;margin-top:6px}
.gv-btn:hover{transform:translateY(-2px)}
.gv-btn:disabled{opacity:.75;cursor:wait;transform:none}
.gv-trust{display:flex;gap:18px;flex-wrap:wrap;margin-top:20px;padding-top:18px;border-top:1px solid #edf2ef;color:var(--muted);font-size:13.5px;font-weight:700}
.gv-trust span{display:inline-flex;gap:7px;align-items:center}.gv-trust i{color:var(--teal)}
.gv-alert{display:flex;gap:12px;align-items:flex-start;border-radius:16px;padding:14px 16px;margin:0 0 18px;font-weight:700;font-size:14.5px;line-height:1.7}
.gv-alert.err{background:#fdecef;color:#9b1f45}.gv-alert i{margin-top:4px}
.gv-ok{text-align:center;padding:10px 0}
.gv-ok .ic{width:84px;height:84px;margin:0 auto 14px;border-radius:50%;display:grid;place-items:center;font-size:36px;color:#fff;background:linear-gradient(145deg,var(--cyan),var(--teal));box-shadow:0 0 0 10px var(--cyan-soft);animation:gvPop .6s cubic-bezier(.2,1.6,.4,1)}
@keyframes gvPop{from{transform:scale(.4);opacity:0}to{transform:none;opacity:1}}
.gv-ok h3{font-size:24px;font-weight:900;color:var(--ink);margin:0 0 6px}
.gv-ok p{color:var(--muted);margin:0 0 20px}
.gv-acts{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.gv-acts a{display:flex;align-items:center;justify-content:center;gap:8px;height:52px;border-radius:14px;font-weight:900;text-decoration:none!important}
.gv-acts a.p{background:linear-gradient(135deg,var(--teal),var(--ink-3));color:#fff!important}
.gv-acts a.g{background:var(--mist);color:var(--ink-3)!important}
.gv-again{display:inline-block;margin-top:16px;font-weight:800}
/* الوثيقة التوضيحية */
.gv-art{position:relative;border-radius:26px;background:radial-gradient(700px 300px at 70% 0%,rgba(70,192,122,.25),transparent 60%),linear-gradient(160deg,var(--ink-2),var(--ink));padding:40px 34px;color:#fff;display:flex;flex-direction:column;justify-content:center;overflow:hidden;min-height:480px}
.gv-art:after{content:"";position:absolute;width:420px;height:420px;border-radius:50%;border:1px dashed rgba(255,255,255,.12);left:-160px;bottom:-200px}
.gv-doc{position:relative;z-index:1;background:#fbfaf5;color:#1d2a22;border-radius:12px;padding:26px 26px 22px;box-shadow:0 30px 60px -20px rgba(0,0,0,.6);transform:perspective(1100px) rotateY(-10deg) rotateX(6deg) rotate(-2deg);transition:transform .6s cubic-bezier(.2,.8,.2,1);border:1px solid #e8e2cf;max-width:420px;margin:0 auto;width:100%}
.gv-art:hover .gv-doc{transform:perspective(1100px) rotateY(0) rotateX(0) rotate(0)}
.gv-doc:before{content:"";position:absolute;inset:8px;border:2px solid #c9d9cf;border-radius:8px;pointer-events:none}
.gv-doc-h{display:flex;align-items:center;gap:12px;justify-content:center;margin-bottom:14px}
.gv-doc-h img{height:54px;filter:brightness(.25) sepia(1) hue-rotate(80deg) saturate(3)}
.gv-doc-h b{display:block;font-size:15px;color:var(--ink-3)}.gv-doc-h small{font-size:10px;letter-spacing:.18em;color:#6d7d73}
.gv-doc h4{text-align:center;font-size:20px;font-weight:900;color:var(--ink);margin:6px 0 12px}
.gv-line{height:9px;border-radius:5px;background:#e3e9e4;margin:9px auto}
.gv-doc-f{display:flex;justify-content:space-between;align-items:flex-end;margin-top:18px}
.gv-seal{width:62px;height:62px;border-radius:50%;background:radial-gradient(circle,#2e8b57,#12553a);display:grid;place-items:center;color:#fff;font-size:22px;box-shadow:0 0 0 4px #dfeee5}
.gv-sign{width:110px}.gv-sign i{display:block;height:2px;background:#9fb1a6;margin-bottom:6px}.gv-sign small{font-size:10px;color:#6d7d73}
.gv-steps{position:relative;z-index:1;list-style:none;margin:30px 0 0;padding:0;display:grid;grid-template-columns:repeat(3,1fr);gap:10px;counter-reset:s}
.gv-steps li{counter-increment:s;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:12px;font-size:13px;color:#d6eadc;line-height:1.6}
.gv-steps li:before{content:counter(s);display:grid;place-items:center;width:26px;height:26px;border-radius:8px;background:var(--cyan);color:var(--ink);font-weight:900;margin-bottom:6px}
.gv-steps b{display:block;color:#fff;font-size:14px}
.gv-faq{margin-top:26px;display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
.gv-faq div{background:#fff;border-radius:18px;padding:18px;box-shadow:var(--sh-1)}
.gv-faq b{display:flex;gap:8px;align-items:center;color:var(--ink);margin-bottom:6px}.gv-faq b i{color:var(--teal)}
.gv-faq p{margin:0;color:var(--muted);font-size:14px;line-height:1.8}
@media(max-width:992px){.gv{grid-template-columns:1fr}.gv-art{min-height:0;order:2}.gv-faq{grid-template-columns:1fr}}
@media(max-width:560px){.gv-card{padding:26px 18px}.gv-steps{grid-template-columns:1fr}.gv-acts{grid-template-columns:1fr}.gv-art{padding:28px 18px}}
</style>

<div class="gv">
  <div class="gv-card">
    <?php if ($state === 'ok'): ?>
      <div class="gv-ok">
        <div class="ic"><i class="fas fa-check"></i></div>
        <h3>تم العثور على وثيقتك</h3>
        <p>الوثيقة صادرة من جامعة بنيان العالمية ومسجلة برقم المرجع <b dir="ltr"><?= $h($ref) ?></b>.</p>
        <div class="gv-acts">
          <a class="p" href="/pdf_grad/doc.php?t=<?= $token ?>" target="_blank" rel="noopener"><i class="fas fa-eye"></i> عرض الوثيقة</a>
          <a class="g" href="/pdf_grad/doc.php?t=<?= $token ?>&dl=1"><i class="fas fa-download"></i> تحميل PDF</a>
        </div>
        <a class="gv-again" href="/pdf_grad/index.php"><i class="fas fa-redo"></i> بحث عن وثيقة أخرى</a>
      </div>
    <?php else: ?>
      <span class="gv-kick"><i class="fas fa-shield-alt"></i> خدمة رسمية آمنة</span>
      <h2>البحث عن وثيقة التخرج</h2>
      <p>أدخل رقم المرجع ورقم الهوية كما هما مسجلان في الجامعة.</p>
      <?php if ($msg): ?><div class="gv-alert err"><i class="fas fa-exclamation-circle"></i><span><?= $h($msg) ?></span></div><?php endif; ?>
      <form method="post" id="gvf" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= $_SESSION['grad_csrf'] ?>">
        <label class="gv-f"><span>رقم المرجع <small>الرقم الذي سُجّلت به</small></span>
          <div class="gv-in"><i class="fas fa-hashtag"></i><input name="id_ref" value="<?= $h($ref) ?>" required dir="ltr" inputmode="text" placeholder="مثال: 34566"></div></label>
        <label class="gv-f"><span>رقم الهوية أو الجواز <small>يُستخدم كرمز تحقق</small></span>
          <div class="gv-in"><i class="fas fa-id-card"></i><input type="password" name="id_pass" id="gvp" required dir="ltr" placeholder="••••••"><button type="button" id="gvt" aria-label="إظهار"><i class="far fa-eye"></i></button></div></label>
        <button class="gv-btn" name="search" value="1" id="gvb"><i class="fas fa-search"></i> اعرض الوثيقة</button>
      </form>
      <div class="gv-trust"><span><i class="fas fa-lock"></i> بياناتك لا تُحفظ</span><span><i class="fas fa-certificate"></i> وثيقة رسمية</span><span><i class="far fa-clock"></i> متاحة على مدار الساعة</span></div>
    <?php endif; ?>
  </div>

  <div class="gv-art" aria-hidden="true">
    <div class="gv-doc">
      <div class="gv-doc-h"><img src="/assets/images/logo-light.png" alt=""><div><b>جامعة بنيان العالمية</b><small>BUNYAN INTERNATIONAL UNIVERSITY</small></div></div>
      <h4>وثيقة تخرج</h4>
      <div class="gv-line" style="width:80%"></div><div class="gv-line" style="width:62%"></div><div class="gv-line" style="width:72%"></div><div class="gv-line" style="width:48%"></div>
      <div class="gv-doc-f"><div class="gv-sign"><i></i><small>رئيس الجامعة</small></div><div class="gv-seal"><i class="fas fa-award"></i></div><div class="gv-sign"><i></i><small>عميد الكلية</small></div></div>
    </div>
    <ol class="gv-steps">
      <li><b>رقم المرجع</b>من خطاب التخرج أو القبول</li>
      <li><b>رقم الهوية</b>المسجل في ملفك</li>
      <li><b>اعرض وحمّل</b>وثيقتك بصيغة PDF</li>
    </ol>
  </div>
</div>

<div class="gv-faq">
  <div><b><i class="far fa-question-circle"></i> أين أجد رقم المرجع؟</b><p>تجده في خطاب التخرج أو رسالة القبول التي وصلتك من الجامعة.</p></div>
  <div><b><i class="fas fa-building"></i> للجهات والمؤسسات</b><p>يمكن لجهة العمل التحقق من الوثيقة بنفس البيانات بعد موافقة الخريج.</p></div>
  <div><b><i class="fas fa-headset"></i> لم تظهر وثيقتك؟</b><p>راسل <a href="/form2.php">الدعم الفني</a> أو واتساب الجامعة، ونصحح بياناتك.</p></div>
</div>

<script>
(function(){var p=document.getElementById('gvp'),t=document.getElementById('gvt'),f=document.getElementById('gvf');
if(t)t.onclick=function(){var s=p.type==='password';p.type=s?'text':'password';t.innerHTML=s?'<i class="far fa-eye-slash"></i>':'<i class="far fa-eye"></i>';};
if(f)f.addEventListener('submit',function(){var b=document.getElementById('gvb');setTimeout(function(){b.disabled=true;b.innerHTML='<i class="fas fa-spinner fa-spin"></i> جارٍ البحث…';},10);});})();
</script>
<?php uwciv_close(); ?>
