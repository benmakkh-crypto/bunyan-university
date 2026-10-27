<?php
/* مركز الدعم والتواصل — يخدم «الدعم الفني» و«طلب أو شكوى لرئيس الجامعة» */
require_once dirname(__DIR__) . '/db_config.php';
require_once __DIR__ . '/shell.php';
require_once __DIR__ . '/settings.php';

function uwciv_helpdesk($kind)
{
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    if (empty($_SESSION['hd_csrf'])) $_SESSION['hd_csrf'] = bin2hex(random_bytes(16));
    $K = [
        'support' => ['title' => 'الدعم الفني', 'sub' => 'فريق الدعم معك خطوة بخطوة — صف المشكلة ونرد عليك في أسرع وقت.', 'icon' => 'fa-headset', 'c' => '#0e9aa7', 'pref' => 'SUP', 'head' => 'كيف نقدر نساعدك؟',
            'cats' => [['login', 'الدخول وكلمة المرور', 'fa-key'], ['platform', 'المنصة والمحاضرات', 'fa-laptop'], ['exam', 'الامتحانات الإلكترونية', 'fa-file-signature'], ['cert', 'الشهادات والوثائق', 'fa-certificate'], ['register', 'التسجيل والقبول', 'fa-user-plus'], ['other', 'أخرى', 'fa-ellipsis-h']],
            'faq' => [['نسيت كلمة المرور', 'استخدم «نسيت كلمة المرور؟» في صفحة تسجيل الدخول، وسيصلك رابط على بريدك.', '/syslog/forgot-password.php'], ['لا تظهر المحاضرات', 'تأكد من تسجيل الدخول بحسابك، ثم افتح المنصة التعليمية من البوابة.', '/elctro.php'], ['أين وثيقة تخرجي؟', 'ابحث عنها برقم المرجع ورقم الهوية من صفحة وثيقة التخرج.', '/pdf_grad/index.php']]],
        'complaint' => ['title' => 'طلب أو شكوى لرئيس الجامعة', 'sub' => 'صوتك يصل مباشرة إلى إدارة الجامعة — تُعامل كل رسالة بسرية واهتمام.', 'icon' => 'fa-envelope-open-text', 'c' => '#9b1f45', 'pref' => 'REQ', 'head' => 'ما نوع رسالتك؟',
            'cats' => [['request', 'طلب', 'fa-hand-paper'], ['complaint', 'شكوى', 'fa-exclamation-triangle'], ['suggest', 'اقتراح', 'fa-lightbulb'], ['thanks', 'شكر وتقدير', 'fa-heart'], ['grievance', 'تظلم أكاديمي', 'fa-balance-scale'], ['other', 'أخرى', 'fa-ellipsis-h']],
            'faq' => [['متى يصلني الرد؟', 'تُدرس الرسائل خلال 15 يوم عمل وفق لائحة التظلمات والشكاوى.', '/regulations-view.php?d=grievances'], ['هل رسالتي سرية؟', 'نعم، لا يطّلع عليها إلا المختصون، ولا يتضرر مقدمها بأي حال.', '/regulations.php'], ['تظلم من نتيجة اختبار', 'اختر «تظلم أكاديمي» واذكر اسم المقرر والفصل الدراسي.', '/regulations-view.php?d=study']]],
    ][$kind];
    $C = uwciv_contact();
    $h = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
    $v = array_fill_keys(['name', 'email', 'phone', 'cat', 'subject', 'message', 'prio'], ''); $err = []; $done = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach ($v as $k => $_) $v[$k] = trim(mb_substr((string) ($_POST[$k] ?? ''), 0, $k === 'message' ? 4000 : 200));
        $now = time(); $_SESSION['hd_try'] = array_values(array_filter($_SESSION['hd_try'] ?? [], function ($t) use ($now) { return $t > $now - 3600; }));
        if (!hash_equals($_SESSION['hd_csrf'], (string) ($_POST['csrf'] ?? '')) || !empty($_POST['website'])) $err['form'] = 'انتهت صلاحية الصفحة، أعد المحاولة.';
        elseif (count($_SESSION['hd_try']) >= 6) $err['form'] = 'أرسلت رسائل كثيرة خلال ساعة، حاول لاحقًا.';
        if (mb_strlen($v['name']) < 3) $err['name'] = 'اكتب الاسم';
        if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) $err['email'] = 'بريد غير صحيح';
        if ($v['phone'] !== '' && !preg_match('/^\+?[0-9 ()-]{7,20}$/', $v['phone'])) $err['phone'] = 'رقم غير صحيح';
        $cats = array_column($K['cats'], 1, 0);
        if (!isset($cats[$v['cat']])) $err['cat'] = 'اختر نوع الرسالة';
        if (mb_strlen($v['subject']) < 3) $err['subject'] = 'اكتب الموضوع';
        if (mb_strlen($v['message']) < 10) $err['message'] = 'اكتب التفاصيل (10 أحرف على الأقل)';
        if (!in_array($v['prio'], ['normal', 'high'], true)) $v['prio'] = 'normal';
        if (!$err) {
            $ref = $K['pref'] . '-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $dir = dirname(__DIR__) . '/_private'; if (!is_dir($dir)) @mkdir($dir, 0750, true);
            $f = $dir . '/tickets.json'; $T = is_file($f) ? (json_decode((string) file_get_contents($f), true) ?: []) : [];
            $T[] = $v + ['ref' => $ref, 'kind' => $kind, 'created' => date('c'), 'status' => 'open'];
            @file_put_contents($f, json_encode($T, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
            require_once __DIR__ . '/mailer.php';
            $rows = ['رقم المتابعة' => $ref, 'النوع' => $cats[$v['cat']] . ($v['prio'] === 'high' ? ' — عاجل' : ''), 'الاسم' => $v['name'], 'البريد' => $v['email'], 'الجوال' => $v['phone'] ?: '—', 'الموضوع' => $v['subject']];
            $tbl = '<table cellpadding="6">'; foreach ($rows as $k => $x) $tbl .= '<tr><td style="color:#5b6f62">' . $k . '</td><td><b>' . $h($x) . '</b></td></tr>'; $tbl .= '</table>';
            $sent = uwciv_mail(UWCIV_MAIL_TO, ($v['prio'] === 'high' ? '[عاجل] ' : '') . $K['title'] . ': ' . $v['subject'] . ' (' . $ref . ')', '<h3 style="color:' . $K['c'] . '">' . $K['title'] . '</h3>' . $tbl . '<p style="background:#f3f7f4;padding:12px;border-radius:8px">' . nl2br($h($v['message'])) . '</p>', $v['email'], $v['name']);
            uwciv_mail($v['email'], 'استلمنا رسالتك ' . $ref . ' — جامعة بنيان العالمية', '<h3 style="color:#12935a">مرحبًا ' . $h($v['name']) . '،</h3><p>استلمنا رسالتك بخصوص «' . $h($v['subject']) . '»، ورقم المتابعة: <b>' . $ref . '</b>.</p><p>سنتواصل معك عبر البريد قريبًا بإذن الله.</p>');
            $_SESSION['hd_try'][] = $now; $_SESSION['hd_csrf'] = bin2hex(random_bytes(16));
            $done = ['ref' => $ref, 'name' => $v['name'], 'email' => $v['email'], 'sent' => $sent];
        }
    }
    $e = function ($k) use ($err) { return isset($err[$k]) ? '<small class="hd-err">' . htmlspecialchars($err[$k]) . '</small>' : ''; };
    uwciv_open($K['title'], t('contact'), $K['sub']);
    ?>
<style>
.hd{--c:<?= $K['c'] ?>;margin-top:58px;display:grid;grid-template-columns:.85fr 1.15fr;gap:26px;align-items:start}
.hd-side{position:sticky;top:84px;display:flex;flex-direction:column;gap:14px}
.hd-intro{border-radius:26px;padding:28px;color:#fff;background:radial-gradient(400px 220px at 100% 0%,color-mix(in srgb,var(--c) 60%,transparent),transparent 65%),linear-gradient(160deg,#11432a,#06210f);position:relative;overflow:hidden;box-shadow:var(--sh-lift)}
.hd-intro:after{content:"";position:absolute;width:220px;height:220px;border-radius:50%;border:1px dashed rgba(255,255,255,.18);left:-80px;bottom:-100px}
.hd-intro>i{width:64px;height:64px;border-radius:18px;display:grid;place-items:center;font-size:26px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);margin-bottom:16px}
.hd-intro h2{color:#fff;font-size:26px;font-weight:900;margin:0 0 8px}
.hd-intro p{color:#cfe6d7;margin:0;line-height:1.9}
.hd-ch{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:20px;position:relative;z-index:1}
.hd-ch a{display:flex;gap:10px;align-items:center;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.16);border-radius:14px;padding:10px 12px;color:#fff!important;text-decoration:none!important;font-size:13px;font-weight:700;transition:.2s}
.hd-ch a:hover{background:rgba(255,255,255,.18)}
.hd-ch a i{font-size:18px;color:#9fe0b8}
.hd-ch small{display:block;color:#bfe0c9;font-size:11.5px;font-weight:500}
.hd-faq{background:#fff;border-radius:22px;box-shadow:var(--sh-1);padding:18px}
.hd-faq h3{font-size:16px;font-weight:900;color:var(--ink);margin:0 0 8px;display:flex;gap:8px;align-items:center}
.hd-faq details{border-top:1px dashed #e2ebe5;padding:10px 0}
.hd-faq summary{cursor:pointer;font-weight:800;color:var(--ink-3);font-size:14.5px;list-style:none;display:flex;justify-content:space-between}
.hd-faq summary::-webkit-details-marker{display:none}
.hd-faq summary:after{content:"+";color:var(--c);font-size:18px}
.hd-faq details[open] summary:after{content:"−"}
.hd-faq p{margin:8px 0 0;color:var(--muted);font-size:14px;line-height:1.8}
.hd-faq a{font-weight:800}
.hd-card{background:#fff;border-radius:26px;box-shadow:var(--sh-3d);padding:28px;position:relative;overflow:hidden}
.hd-card:before{content:"";position:absolute;inset:0 0 auto;height:5px;background:linear-gradient(90deg,var(--c),#46c07a)}
.hd-card h3{font-size:22px;font-weight:900;color:var(--ink);margin:0 0 16px}
.hd-cats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px}
.hd-cats label{position:relative;cursor:pointer;margin:0}
.hd-cats input{position:absolute;inset:0;opacity:0;margin:0}
.hd-cats span{display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center;border:2px solid #e3ece6;border-radius:16px;padding:14px 8px;font-weight:800;font-size:13.5px;color:var(--ink-3);transition:.2s;height:100%;box-sizing:border-box}
.hd-cats span i{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;background:var(--mist);color:var(--c);font-size:17px;transition:.2s}
.hd-cats input:checked+span{border-color:var(--c);background:color-mix(in srgb,var(--c) 7%,#fff);box-shadow:0 12px 22px -16px var(--c)}
.hd-cats input:checked+span i{background:var(--c);color:#fff}
.hd-cats input:focus-visible+span{box-shadow:0 0 0 4px color-mix(in srgb,var(--c) 25%,transparent)}
.hd-g{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.hd-g .w{grid-column:1/-1}
.hd-g label{display:flex;flex-direction:column;gap:7px;font-weight:800;font-size:14px;color:var(--ink-3)}
.hd-in{display:flex;align-items:center;gap:10px;border:2px solid #e3ece6;border-radius:14px;padding:0 14px;background:var(--mist);transition:.2s}
.hd-in.top{align-items:flex-start;padding-top:12px}
.hd-in i{color:var(--muted);width:16px;text-align:center}
.hd-in input,.hd-in textarea{flex:1;border:0!important;outline:none;background:transparent!important;height:50px;font-family:inherit;font-size:15px;min-width:0;box-shadow:none!important;padding:0!important}
.hd-in textarea{height:auto;resize:vertical;padding-bottom:12px!important;line-height:1.9}
.hd-in:focus-within{border-color:var(--c);background:#fff;box-shadow:0 0 0 4px color-mix(in srgb,var(--c) 14%,transparent)}
.hd-in:focus-within i{color:var(--c)}
.hd-count{font-size:12px;color:var(--muted);font-weight:600;align-self:flex-end}
.hd-err{color:#c0392b;font-size:12.5px;font-weight:700}
.hd-alert{background:#fdecef;color:#9b1f45;border-radius:14px;padding:12px 16px;font-weight:800;margin-bottom:14px}
.hd-prio{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.hd-prio label{flex-direction:row!important;align-items:center;gap:8px;background:var(--mist);border-radius:30px;padding:8px 14px;cursor:pointer}
.hd-prio input{accent-color:var(--c)}
.hd-send{margin-top:18px;width:100%;height:58px;border:0;border-radius:16px;font-size:17px;font-weight:900;color:#fff;background:linear-gradient(135deg,var(--c),#0b3119);cursor:pointer;font-family:inherit;box-shadow:0 16px 30px -16px rgba(6,33,15,.8);display:flex;gap:10px;align-items:center;justify-content:center}
.hd-send:disabled{opacity:.75}
.hd-hp{position:absolute!important;width:1px!important;height:1px!important;opacity:0;overflow:hidden;clip-path:inset(50%)}
.hd-done{text-align:center;padding:16px 0}
.hd-done .b{width:84px;height:84px;margin:0 auto 14px;border-radius:50%;display:grid;place-items:center;font-size:36px;color:#fff;background:linear-gradient(145deg,#46c07a,#1f7a45);box-shadow:0 0 0 10px #e4f4ea;animation:hdP .6s cubic-bezier(.2,1.6,.4,1)}
@keyframes hdP{from{transform:scale(.4);opacity:0}to{transform:none;opacity:1}}
.hd-done h3{margin:0 0 6px}.hd-done p{color:var(--muted)}
.hd-ref{display:inline-flex;gap:12px;align-items:center;background:var(--ink-3);color:#fff;border-radius:16px;padding:10px 18px;margin:8px 0 16px}
.hd-ref b{font-size:20px;color:#9fe0b8;letter-spacing:1px}
.hd-acts{display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.hd-acts a{display:inline-flex;gap:8px;align-items:center;border-radius:14px;padding:12px 18px;font-weight:900;text-decoration:none!important;background:var(--mist);color:var(--ink-3)!important}
.hd-acts a.p{background:var(--c);color:#fff!important}
.hd-switch{display:flex;gap:10px;align-items:center;justify-content:center;margin-top:16px;font-size:14px;color:var(--muted)}
@media(max-width:992px){.hd{grid-template-columns:1fr}.hd-side{position:static;order:2}}
@media(max-width:560px){.hd-cats{grid-template-columns:repeat(2,1fr)}.hd-g{grid-template-columns:1fr}.hd-card{padding:22px 16px}.hd-ch{grid-template-columns:1fr}}
</style>
<div class="hd">
  <aside class="hd-side">
    <div class="hd-intro"><i class="fas <?= $K['icon'] ?>"></i><h2><?= $K['title'] ?></h2><p><?= $K['sub'] ?></p>
      <div class="hd-ch">
        <a href="https://wa.me/<?= $C['digits'] ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp"></i><span>واتساب<small dir="ltr"><?= $h($C['pretty']) ?></small></span></a>
        <a href="mailto:info@biuedu.com"><i class="far fa-envelope"></i><span>البريد<small>info@biuedu.com</small></span></a>
        <a href="tel:+<?= $C['digits'] ?>"><i class="fas fa-phone-alt"></i><span>اتصال<small>السبت–الخميس</small></span></a>
        <a href="/ask.php"><i class="far fa-question-circle"></i><span>الأسئلة الشائعة<small>إجابات فورية</small></span></a>
      </div>
    </div>
    <div class="hd-faq"><h3><i class="fas fa-bolt" style="color:var(--c)"></i> حلول سريعة</h3>
      <?php foreach ($K['faq'] as $f): ?><details><summary><?= $f[0] ?></summary><p><?= $f[1] ?> <a href="<?= $f[2] ?>">افتح الصفحة ←</a></p></details><?php endforeach; ?>
    </div>
  </aside>

  <div class="hd-card">
    <?php if ($done): ?>
      <div class="hd-done"><div class="b"><i class="fas fa-check"></i></div>
        <h3>وصلت رسالتك، <?= $h($done['name']) ?></h3>
        <p>أرسلنا نسخة إلى <b dir="ltr"><?= $h($done['email']) ?></b>، وسنرد عليك قريبًا بإذن الله.</p>
        <div class="hd-ref"><small>رقم المتابعة</small><b dir="ltr"><?= $done['ref'] ?></b></div>
        <div class="hd-acts"><a class="p" href="/index.php"><i class="fas fa-home"></i> الصفحة الرئيسية</a><a href="https://wa.me/<?= $C['digits'] ?>?text=<?= rawurlencode('رقم المتابعة ' . $done['ref']) ?>" target="_blank"><i class="fab fa-whatsapp"></i> تابع عبر واتساب</a></div>
      </div>
    <?php else: ?>
    <h3><?= $K['head'] ?></h3>
    <?php if ($err): ?><div class="hd-alert"><i class="fas fa-exclamation-circle"></i> <?= $h($err['form'] ?? 'يرجى إكمال الحقول المظللة.') ?></div><?php endif; ?>
    <form method="post" id="hdf" novalidate>
      <input type="hidden" name="csrf" value="<?= $_SESSION['hd_csrf'] ?>"><input type="text" name="website" class="hd-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
      <div class="hd-cats"><?php foreach ($K['cats'] as $c): ?><label><input type="radio" name="cat" value="<?= $c[0] ?>" <?= $v['cat'] === $c[0] ? 'checked' : '' ?> required><span><i class="fas <?= $c[2] ?>"></i><?= $c[1] ?></span></label><?php endforeach; ?></div><?= $e('cat') ?>
      <div class="hd-g">
        <label>الاسم الكامل *<div class="hd-in"><i class="far fa-user"></i><input name="name" value="<?= $h($v['name']) ?>" required autocomplete="name"></div><?= $e('name') ?></label>
        <label>البريد الإلكتروني *<div class="hd-in"><i class="far fa-envelope"></i><input type="email" name="email" value="<?= $h($v['email']) ?>" dir="ltr" required autocomplete="email"></div><?= $e('email') ?></label>
        <label>الجوال (اختياري)<div class="hd-in"><i class="fab fa-whatsapp"></i><input name="phone" value="<?= $h($v['phone']) ?>" dir="ltr" autocomplete="tel"></div><?= $e('phone') ?></label>
        <label>الموضوع *<div class="hd-in"><i class="fas fa-tag"></i><input name="subject" value="<?= $h($v['subject']) ?>" required maxlength="200"></div><?= $e('subject') ?></label>
        <label class="w">التفاصيل *<div class="hd-in top"><i class="far fa-comment-dots"></i><textarea name="message" id="hdm" rows="6" maxlength="4000" required placeholder="اكتب رسالتك بوضوح، واذكر أي تفاصيل تساعدنا…"><?= $h($v['message']) ?></textarea></div><span class="hd-count" id="hdc">0 / 4000</span><?= $e('message') ?></label>
        <div class="w hd-prio"><b style="font-size:14px;color:var(--ink-3)">الأولوية:</b><label><input type="radio" name="prio" value="normal" <?= $v['prio'] !== 'high' ? 'checked' : '' ?>> عادية</label><label><input type="radio" name="prio" value="high" <?= $v['prio'] === 'high' ? 'checked' : '' ?>> <span style="color:#c0392b">عاجلة</span></label></div>
      </div>
      <button class="hd-send" id="hds"><i class="fas fa-paper-plane"></i> إرسال الرسالة</button>
      <p class="hd-switch"><i class="fas fa-lock"></i> بياناتك سرية ولا تُستخدم إلا للرد عليك · <?= $kind === 'support' ? '<a href="/form1.php">رسالة لرئيس الجامعة</a>' : '<a href="/form2.php">الدعم الفني</a>' ?></p>
    </form>
    <?php endif; ?>
  </div>
</div>
<script>
(function(){var m=document.getElementById('hdm'),c=document.getElementById('hdc'),f=document.getElementById('hdf');
if(m){var u=function(){c.textContent=m.value.length+' / 4000'};m.addEventListener('input',u);u();}
if(f)f.addEventListener('submit',function(e){var bad=null;f.querySelectorAll('[required]').forEach(function(x){if(bad)return;if(x.type==='radio'?!f.querySelector('input[name='+x.name+']:checked'):!x.value.trim())bad=x;});if(bad){e.preventDefault();(bad.closest('.hd-in')||bad.closest('.hd-cats')).scrollIntoView({behavior:'smooth',block:'center'});if(bad.focus)bad.focus();return;}var b=document.getElementById('hds');setTimeout(function(){b.disabled=true;b.innerHTML='<i class="fas fa-spinner fa-spin"></i> جارٍ الإرسال…'},10);});})();
</script>
<?php
    uwciv_close();
}
