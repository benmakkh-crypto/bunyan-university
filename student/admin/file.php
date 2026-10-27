<?php
/* عرض مستندات المتقدمين — للمشرف العام فقط */
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/admin_ui.php';
uwciv_admin_gate(['title' => 'مستندات المتقدمين', 'perm' => 'applications']);
$f = (string) ($_GET['f'] ?? '');
if (strpos($f, 'app:') === 0) { $dir = $_SERVER['DOCUMENT_ROOT'] . '/_private/applications'; $f = substr($f, 4); }
else $dir = __DIR__ . '/img';
$f = basename($f);
$path = $dir . '/' . $f;
if ($f === '' || !is_file($path)) { http_response_code(404); exit('الملف غير موجود'); }
$ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
$types = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif', 'pdf' => 'application/pdf'];
if (!isset($types[$ext])) { http_response_code(403); exit; }
header('Content-Type: ' . $types[$ext]);
header('Content-Length: ' . filesize($path));
header('Content-Disposition: inline; filename="' . $f . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($path);
