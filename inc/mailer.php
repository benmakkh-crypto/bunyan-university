<?php
/* إرسال بريد موحّد: Gmail إن وُضعت كلمة مرور التطبيق، وإلا بريد الاستضافة */
require_once dirname(__DIR__) . '/db_config.php';
require_once dirname(__DIR__) . '/PHPMailer/src/Exception.php';
require_once dirname(__DIR__) . '/PHPMailer/src/PHPMailer.php';
require_once dirname(__DIR__) . '/PHPMailer/src/SMTP.php';

function uwciv_mail($to, $subject, $html, $replyTo = '', $replyName = '')
{
    $m = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $m->CharSet = 'UTF-8';
        if (UWCIV_SMTP_PASS === '') {
            $m->isMail();
            $m->setFrom('noreply@your-domain.com', 'جامعة بنيان العالمية');
        } else {
            $m->isSMTP();
            $m->Host = 'smtp.gmail.com'; $m->SMTPAuth = true;
            $m->Username = UWCIV_SMTP_USER; $m->Password = UWCIV_SMTP_PASS;
            $m->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS; $m->Port = 465;
            $m->setFrom(UWCIV_SMTP_USER, 'جامعة بنيان العالمية');
        }
        if ($replyTo) $m->addReplyTo($replyTo, $replyName);
        $m->addAddress($to);
        $m->isHTML(true);
        $m->Subject = $subject;
        $m->Body = '<div dir="rtl" style="font-family:Tahoma,Arial;font-size:15px;line-height:1.9;color:#13261b">' . $html . '</div>';
        $m->AltBody = trim(strip_tags(str_replace(['<br>', '</p>', '</tr>'], "\n", $html)));
        $m->send();
        return true;
    } catch (Exception $e) {
        @error_log('uwciv_mail: ' . $m->ErrorInfo);
        return false;
    }
}
