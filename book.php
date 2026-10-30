<?php
/*
 * بوابة التحميل الآمن للكتب
 * - الملفات المرفوعة: محفوظة في _private/books (محمية من الوصول المباشر) وتُقدَّم من هنا فقط بعد التحقق من أنها PDF.
 * - الروابط الخارجية: تُحوَّل فقط إن كانت https ومن نطاق موثوق في قائمة الجامعة.
 */
require_once __DIR__ . '/inc/curriculum.php';

$code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) ($_GET['c'] ?? '')));
$id = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['b'] ?? ''));
$D = cur_doc();
$book = null; $bi = null;
foreach ($D['courses'][$code]['books'] ?? [] as $i => $b) if ($b['id'] === $id) { $book = $b; $bi = $i; break; }

function book_fail($msg) { http_response_code(404); header('Content-Type: text/html; charset=utf-8'); echo '<!doctype html><meta charset="utf-8"><body style="font-family:Tahoma;text-align:center;padding:60px" dir="rtl"><h2>' . htmlspecialchars($msg) . '</h2><p><a href="/plans.php">العودة إلى الخطط الدراسية</a></p></body>'; exit; }
if (!$book) book_fail('الكتاب غير موجود.');

/* عدّاد التحميل — مرة لكل زائر في الجلسة */
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
if (empty($_SESSION['cur_dl'][$code . $id])) {
    $_SESSION['cur_dl'][$code . $id] = 1;
    $fp = @fopen(cur_live_file() . '.lock', 'c');
    if ($fp && flock($fp, LOCK_EX)) {
        $doc = cur_doc(true);
        if (isset($doc['courses'][$code]['books'][$bi]) && $doc['courses'][$code]['books'][$bi]['id'] === $id) { $doc['courses'][$code]['books'][$bi]['dl'] = (int) ($doc['courses'][$code]['books'][$bi]['dl'] ?? 0) + 1; cur_save_quiet($doc); }
        flock($fp, LOCK_UN);
    }
    if ($fp) fclose($fp);
}
function cur_save_quiet(array $doc) { $f = cur_live_file(); if (!is_dir(dirname($f))) @mkdir(dirname($f), 0750, true); @file_put_contents($f, json_encode($doc, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX); }

if (!empty($book['file'])) {
    $path = cur_books_dir() . '/' . basename($book['file']);
    if (!is_file($path)) book_fail('الملف غير متوفر حاليًا.');
    $fh = fopen($path, 'rb'); $magic = fread($fh, 5); fclose($fh);
    if ($magic !== '%PDF-') book_fail('الملف غير صالح.');
    $name = preg_replace('/[^\p{Arabic}\p{L}\p{N} ._-]+/u', '', $code . ' - ' . $book['title']) ?: $code;
    header('Content-Type: application/pdf');
    header('X-Content-Type-Options: nosniff');
    header("Content-Security-Policy: default-src 'none'; sandbox");
    header('Content-Disposition: ' . (isset($_GET['view']) ? 'inline' : 'attachment') . "; filename=\"" . $code . ".pdf\"; filename*=UTF-8''" . rawurlencode($name . '.pdf'));
    header('Content-Length: ' . filesize($path));
    header('Cache-Control: private, max-age=3600');
    readfile($path);
    exit;
}
if (!empty($book['url']) && cur_url_ok($book['url'])) {
    header('Referrer-Policy: no-referrer');
    header('Location: ' . $book['url'], true, 302);
    exit;
}
book_fail('لا يوجد ملف أو رابط موثوق لهذا الكتاب بعد.');
