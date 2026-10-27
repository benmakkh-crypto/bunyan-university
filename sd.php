<?php require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: /form2.php'); exit; }

$f = function ($k, $max = 300) { return trim(mb_substr((string) ($_POST[$k] ?? ''), 0, $max)); };
$name    = $f('name', 120);
$email   = $f('email', 160);
$phone   = $f('phone', 40);
$subject = $f('subject', 200);
$message = $f('message', 5000);
$back    = (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'form1') !== false) ? '/form1.php' : '/form2.php';

$ok = false; $err = '';
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $err = 'يرجى كتابة الاسم وبريد إلكتروني صحيح.';
} elseif ($message === '' && $subject === '') {
    $err = 'يرجى كتابة موضوع الرسالة أو نصها.';
} else {
    $h = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
    $body = '<div dir="rtl" style="font-family:Tahoma,Arial;font-size:15px;line-height:1.8">'
          . '<h3 style="color:#12935a;margin:0 0 10px">رسالة جديدة من موقع جامعة بنيان العالمية</h3>'
          . '<p><b>الاسم:</b> ' . $h($name) . '<br><b>البريد:</b> ' . $h($email) . '<br><b>الجوال:</b> ' . $h($phone) . '<br><b>الموضوع:</b> ' . $h($subject) . '</p>'
          . '<p><b>الرسالة:</b><br>' . ($message !== '' ? nl2br($h($message)) : '— (بدون نص) —') . '</p></div>';
    $alt = "الاسم: $name\nالبريد: $email\nالجوال: $phone\nالموضوع: $subject\n\n" . ($message !== '' ? $message : '(بدون نص)');

    $mail = new PHPMailer(true);
    try {
        $mail->CharSet = 'UTF-8';
        if (UWCIV_SMTP_PASS === '') {
            // بدون كلمة مرور تطبيق: الإرسال عبر بريد الاستضافة نفسها
            $mail->isMail();
            $mail->setFrom('noreply@your-domain.com', 'موقع جامعة بنيان العالمية');
        } else {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = UWCIV_SMTP_USER;
            $mail->Password   = UWCIV_SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->setFrom(UWCIV_SMTP_USER, 'موقع جامعة بنيان العالمية');
        }
        $mail->addReplyTo($email, $name);
        $mail->addAddress(UWCIV_MAIL_TO);
        $mail->isHTML(true);
        $mail->Subject = 'رسالة من الموقع: ' . ($subject !== '' ? $subject : $name);
        $mail->Body    = $body;
        $mail->AltBody = $alt;
        $mail->send();
        $ok = true;
    } catch (Exception $e) {
        $err = 'تعذّر إرسال الرسالة حالياً، حاول لاحقاً أو راسلنا مباشرة على info@biuedu.com';
        @error_log('sd.php mail: ' . $mail->ErrorInfo);
    }
}
?><!DOCTYPE html>
<html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $ok ? 'تم الإرسال' : 'لم تُرسل الرسالة' ?> | جامعة بنيان العالمية</title>
<style>
body{margin:0;min-height:100vh;display:grid;place-items:center;background:linear-gradient(135deg,#0b3d2a,#12935a);font-family:Tahoma,Arial,sans-serif;padding:16px}
.c{background:#fff;border-radius:18px;max-width:440px;width:100%;padding:34px 26px;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,.25)}
.i{width:70px;height:70px;border-radius:50%;display:grid;place-items:center;margin:0 auto 14px;font-size:36px;color:#fff;background:<?= $ok ? '#12935a' : '#9b1f45' ?>}
h1{font-size:22px;margin:0 0 8px;color:#123}p{color:#456;line-height:1.8;margin:0 0 20px}
a{display:inline-block;margin:4px;padding:10px 20px;border-radius:30px;text-decoration:none;background:#12935a;color:#fff}
a.o{background:#fff;color:#12935a;border:2px solid #12935a}
</style></head><body><div class="c">
<div class="i"><?= $ok ? '✓' : '!' ?></div>
<h1><?= $ok ? 'تم إرسال رسالتك بنجاح' : 'لم تُرسل الرسالة' ?></h1>
<p><?= $ok ? 'شكراً لتواصلك مع جامعة بنيان العالمية، سنرد عليك على بريدك قريباً.' : htmlspecialchars($err) ?></p>
<?php if (!$ok): ?><a href="javascript:history.back()">رجوع وتعديل</a><?php endif; ?>
<a href="/index.php" class="<?= $ok ? '' : 'o' ?>">الصفحة الرئيسية</a>
</div></body></html>
