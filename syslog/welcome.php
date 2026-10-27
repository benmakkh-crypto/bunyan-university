<?php
session_start();
if (!isset($_SESSION['SESSION_EMAIL'])) {
    header("Location: index.php");
    exit;
}
// بعد تسجيل الدخول: الانتقال إلى البوابة الإلكترونية
header("Location: /elctro.php");
exit;
