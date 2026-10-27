<?php
/* عرض وثيقة التخرج بعد التحقق — بدون كشف اسم الملف */
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
$t = (string) ($_GET['t'] ?? '');
$f = $_SESSION['grad_docs'][$t] ?? '';
$path = __DIR__ . '/admin/files/' . basename($f);
if ($f === '' || !is_file($path)) { http_response_code(404); header('Location: /pdf_grad/index.php'); exit; }
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($path));
header('Content-Disposition: ' . (isset($_GET['dl']) ? 'attachment' : 'inline') . '; filename="uwciv-graduation-document.pdf"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($path);
