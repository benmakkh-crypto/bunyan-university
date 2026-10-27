<?php
/* نظام الامتحانات الإلكترونية — دوال مشتركة (قاعدة oesm) */
require_once dirname(__DIR__) . '/db_config.php';
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
if (!function_exists('uwciv_h')) { function uwciv_h($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); } }

function oe_db()
{
    static $db = null; if ($db) return $db;
    $db = db_pdo('oesm'); $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $db;
}
function oe_csrf() { if (empty($_SESSION['oe_csrf'])) $_SESSION['oe_csrf'] = bin2hex(random_bytes(16)); return $_SESSION['oe_csrf']; }
function oe_csrf_ok() { return hash_equals(oe_csrf(), (string) ($_POST['csrf'] ?? '')); }
function oe_me() { return empty($_SESSION['oe_email']) ? null : ['email' => $_SESSION['oe_email'], 'name' => $_SESSION['oe_name'] ?? '']; }
function oe_now() { return date('Y-m-d H:i:s'); }

/* كل الامتحانات مع عدد الأسئلة وعدد المحاولات */
function oe_quizzes()
{
    $db = oe_db();
    $Q = $db->query('SELECT * FROM quiz ORDER BY date DESC')->fetchAll();
    $qc = []; foreach ($db->query('SELECT eid, COUNT(*) n FROM questions GROUP BY eid')->fetchAll() as $r) $qc[$r['eid']] = (int) $r['n'];
    $hc = []; foreach ($db->query('SELECT eid, COUNT(*) n, AVG(score) a FROM history GROUP BY eid')->fetchAll() as $r) $hc[$r['eid']] = [(int) $r['n'], (float) $r['a']];
    foreach ($Q as &$q) { $q['qcount'] = $qc[$q['eid']] ?? 0; $q['attempts'] = $hc[$q['eid']][0] ?? 0; $q['avg'] = $hc[$q['eid']][1] ?? 0; $q['max'] = $q['qcount'] * (int) $q['sahi']; }
    return $Q;
}

/* أسئلة امتحان مع خياراتها والإجابة الصحيحة */
function oe_questions($eid)
{
    $db = oe_db();
    $st = $db->prepare('SELECT * FROM questions WHERE eid = ? ORDER BY sn'); $st->execute([$eid]); $qs = $st->fetchAll();
    $o = $db->prepare('SELECT * FROM options WHERE qid = ?'); $a = $db->prepare('SELECT ansid FROM answer WHERE qid = ? LIMIT 1');
    foreach ($qs as &$q) { $o->execute([$q['qid']]); $q['options'] = $o->fetchAll(); $a->execute([$q['qid']]); $q['ans'] = (string) $a->fetchColumn(); }
    return $qs;
}

function oe_quiz($eid) { $st = oe_db()->prepare('SELECT * FROM quiz WHERE eid = ? LIMIT 1'); $st->execute([$eid]); return $st->fetch() ?: null; }

/* حفظ امتحان (جديد أو تعديل) — $data: title,intro,time,sahi,wrong,tag,questions[[text, options[], correctIndex]] */
function oe_save_quiz($eid, array $data)
{
    $db = oe_db(); $db->beginTransaction();
    try {
        $qs = $data['questions'];
        if ($eid) {
            $db->prepare('UPDATE quiz SET title = ?, sahi = ?, wrong = ?, total = ?, time = ?, intro = ?, tag = ? WHERE eid = ?')->execute([$data['title'], $data['sahi'], $data['wrong'], count($qs), $data['time'], $data['intro'], $data['tag'], $eid]);
            oe_delete_questions($eid);
        } else {
            $eid = uniqid();
            $db->prepare('INSERT INTO quiz (eid, title, sahi, wrong, total, time, intro, tag, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute([$eid, $data['title'], $data['sahi'], $data['wrong'], count($qs), $data['time'], $data['intro'], $data['tag'], oe_now()]);
        }
        $iq = $db->prepare('INSERT INTO questions (eid, qid, qns, choice, sn) VALUES (?, ?, ?, ?, ?)');
        $io = $db->prepare('INSERT INTO options (qid, `option`, optionid) VALUES (?, ?, ?)');
        $ia = $db->prepare('INSERT INTO answer (qid, ansid) VALUES (?, ?)');
        foreach ($qs as $i => $q) {
            $qid = uniqid('q'); $iq->execute([$eid, $qid, $q[0], count($q[1]), $i + 1]);
            foreach ($q[1] as $k => $opt) { $oid = uniqid('o') . $k; $io->execute([$qid, $opt, $oid]); if ($k === $q[2]) $ia->execute([$qid, $oid]); }
        }
        $db->commit(); return $eid;
    } catch (Throwable $e) { $db->rollBack(); throw $e; }
}
function oe_delete_questions($eid)
{
    $db = oe_db(); $st = $db->prepare('SELECT qid FROM questions WHERE eid = ?'); $st->execute([$eid]);
    $do = $db->prepare('DELETE FROM options WHERE qid = ?'); $da = $db->prepare('DELETE FROM answer WHERE qid = ?');
    foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $qid) { $do->execute([$qid]); $da->execute([$qid]); }
    $db->prepare('DELETE FROM questions WHERE eid = ?')->execute([$eid]);
}

/* إعادة حساب مجموع الطالب في جدول الترتيب */
function oe_update_rank($email)
{
    $db = oe_db(); $st = $db->prepare('SELECT COALESCE(SUM(score),0) FROM history WHERE email = ?'); $st->execute([$email]); $sum = (int) $st->fetchColumn();
    $ex = $db->prepare('SELECT COUNT(*) FROM rank WHERE email = ?'); $ex->execute([$email]);
    if ($ex->fetchColumn()) $db->prepare('UPDATE rank SET score = ?, time = ? WHERE email = ?')->execute([$sum, oe_now(), $email]);
    else $db->prepare('INSERT INTO rank (email, score, time) VALUES (?, ?, ?)')->execute([$email, $sum, oe_now()]);
}

function oe_grade_label($pct)
{
    if ($pct >= 90) return ['ممتاز', '#1f7a45', 'fa-trophy'];
    if ($pct >= 80) return ['جيد جدًا', '#2c5fd0', 'fa-medal'];
    if ($pct >= 65) return ['جيد', '#0e9aa7', 'fa-thumbs-up'];
    if ($pct >= 50) return ['مقبول', '#c9962a', 'fa-check'];
    return ['لم يجتز', '#9b1f45', 'fa-redo'];
}
