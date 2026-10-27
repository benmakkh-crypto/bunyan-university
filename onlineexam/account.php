<?php
/* بوابة الطالب — الامتحانات والنتائج والترتيب */
require_once dirname(__DIR__) . '/inc/exam.php';
require_once dirname(__DIR__) . '/inc/shell.php';
$me = oe_me();
if (!$me) { header('Location: index.php#auth'); exit; }
$h = 'uwciv_h'; $err = '';
$cols = ['#1f7a45', '#2c5fd0', '#9b1f45', '#c9962a', '#0e9aa7', '#5b4bb7'];
$letters = ['أ', 'ب', 'ج', 'د', 'هـ', 'و'];
try { $db = oe_db(); } catch (Throwable $e) { $db = null; }
$done = function ($eid) use ($db, $me) { $st = $db->prepare('SELECT * FROM history WHERE email = ? AND eid = ? LIMIT 1'); $st->execute([$me['email'], $eid]); return $st->fetch() ?: null; };

/* ===== تسليم الامتحان ===== */
if ($db && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eid'])) {
    $eid = (string) $_POST['eid'];
    if (!oe_csrf_ok()) { $err = 'انتهت صلاحية الصفحة.'; }
    elseif (($Z = oe_quiz($eid)) && !$done($eid)) {
        $QS = oe_questions($eid); $ok = 0; $bad = 0; $ans = []; $given = (array) ($_POST['a'] ?? []);
        foreach ($QS as $q) { $g = (string) ($given[$q['qid']] ?? ''); if ($g === '') continue; $ans[$q['qid']] = $g; if ($g === $q['ans']) $ok++; else $bad++; }
        $score = $ok * (int) $Z['sahi'] - $bad * (int) $Z['wrong'];
        $db->prepare('INSERT INTO history (email, eid, score, level, sahi, wrong, date) VALUES (?, ?, ?, ?, ?, ?, ?)')->execute([$me['email'], $eid, $score, $ok + $bad, $ok, $bad, oe_now()]);
        oe_update_rank($me['email']);
        $_SESSION['oe_answers'][$eid] = $ans; unset($_SESSION['oe_start'][$eid]);
    }
    header('Location: account.php?result=' . rawurlencode($eid)); exit;
}

$view = isset($_GET['take']) ? 'take' : (isset($_GET['result']) ? 'result' : ($_GET['v'] ?? 'home'));

/* ===== صفحة الامتحان ===== */
if ($db && $view === 'take') {
    $eid = (string) $_GET['take']; $Z = oe_quiz($eid);
    if (!$Z) { header('Location: account.php'); exit; }
    if ($done($eid)) { header('Location: account.php?result=' . rawurlencode($eid)); exit; }
    $QS = oe_questions($eid);
    if (empty($_SESSION['oe_start'][$eid])) $_SESSION['oe_start'][$eid] = time();
    $left = max(0, (int) $Z['time'] * 60 - (time() - $_SESSION['oe_start'][$eid]));
    uwciv_open($Z['title'], 'الامتحانات الإلكترونية', '');
    ?>
<link rel="stylesheet" href="/assets/css/uwciv-exam.css?v=1">
<form method="post" id="oxf"><input type="hidden" name="csrf" value="<?= oe_csrf() ?>"><input type="hidden" name="eid" value="<?= $h($eid) ?>">
  <div class="ox-bar"><h2><i class="fas fa-file-signature" style="color:var(--teal)"></i> <?= $h($Z['title']) ?></h2><span class="ox-ans"><b id="oxa">0</b> / <?= count($QS) ?> مُجاب</span><div class="ox-timer" id="oxt"><i class="far fa-clock"></i><span>--:--</span></div><div class="ox-prog"><i id="oxp"></i></div></div>
  <?php if (!$QS): ?><div class="ox-empty">لم تُضف أسئلة لهذا الامتحان بعد.</div><?php endif; ?>
  <?php foreach ($QS as $i => $q): ?>
  <div class="ox-q" id="q<?= $i + 1 ?>"><h3><span><?= $i + 1 ?></span><?= nl2br($h($q['qns'])) ?></h3>
    <div class="ox-opts"><?php foreach ($q['options'] as $k => $o): ?><label class="ox-opt"><input type="radio" name="a[<?= $h($q['qid']) ?>]" value="<?= $h($o['optionid']) ?>"><span data-l="<?= $letters[$k] ?? ($k + 1) ?>"><?= $h($o['option']) ?></span></label><?php endforeach; ?></div></div>
  <?php endforeach; ?>
  <div class="ox-submit"><span style="color:var(--muted)"><i class="fas fa-info-circle"></i> الإجابة الصحيحة: +<?= (int) $Z['sahi'] ?> · الخاطئة: −<?= (int) $Z['wrong'] ?> · المتروكة: 0 — <b>محاولة واحدة فقط</b></span><button class="ox-btn" id="oxs"><i class="fas fa-paper-plane"></i> تسليم الإجابات</button></div>
</form>
<script>
(function(){var left=<?= $left ?>,total=<?= count($QS) ?>,f=document.getElementById('oxf'),t=document.getElementById('oxt'),sent=false;
function fmt(s){var m=Math.floor(s/60),x=s%60;return (m<10?'0':'')+m+':'+(x<10?'0':'')+x;}
function tick(){t.querySelector('span').textContent=fmt(left);t.classList.toggle('low',left<=60);if(left<=0){if(!sent){sent=true;window.onbeforeunload=null;f.submit();}return;}left--;}
tick();setInterval(tick,1000);
function upd(){var n=f.querySelectorAll('input[type=radio]:checked').length;document.getElementById('oxa').textContent=n;document.getElementById('oxp').style.width=(total?n/total*100:0)+'%';}
f.addEventListener('change',upd);
window.onbeforeunload=function(){return 'سيُفقد تقدّمك إن غادرت الصفحة.';};
f.addEventListener('submit',function(e){if(sent)return;var n=f.querySelectorAll('input[type=radio]:checked').length;if(n<total&&!confirm('أجبت عن '+n+' من '+total+' سؤالًا. تسليم الآن؟')){e.preventDefault();return;}sent=true;window.onbeforeunload=null;document.getElementById('oxs').innerHTML='<i class="fas fa-spinner fa-spin"></i> جارٍ التصحيح…';});})();
</script>
<?php uwciv_close(); exit;
}

/* ===== النتيجة ===== */
if ($db && $view === 'result') {
    $eid = (string) $_GET['result']; $Z = oe_quiz($eid); $H = $Z ? $done($eid) : null;
    if (!$Z || !$H) { header('Location: account.php'); exit; }
    $QS = oe_questions($eid); $max = count($QS) * (int) $Z['sahi'];
    $pct = $max ? max(0, min(100, round($H['score'] / $max * 100))) : 0; $G = oe_grade_label($pct);
    $mine = $_SESSION['oe_answers'][$eid] ?? null;
    $st = $db->prepare('SELECT COUNT(*) FROM history WHERE eid = ? AND score > ?'); $st->execute([$eid, $H['score']]); $pos = (int) $st->fetchColumn() + 1;
    $st = $db->prepare('SELECT COUNT(*) FROM history WHERE eid = ?'); $st->execute([$eid]); $of = (int) $st->fetchColumn();
    uwciv_open('نتيجة: ' . $Z['title'], 'الامتحانات الإلكترونية', '');
    ?>
<link rel="stylesheet" href="/assets/css/uwciv-exam.css?v=1">
<section class="ox-res" style="--c:<?= $G[1] ?>">
  <div class="ox-ring" style="--p:<?= $pct ?>"><div><span><b><?= $pct ?>%</b><br><?= (int) $H['score'] ?> من <?= $max ?></span></div></div>
  <div><small><i class="fas <?= $G[2] ?>"></i> <?= $G[0] ?></small><h2><?= $h($Z['title']) ?></h2>
    <div class="ox-pills"><span class="g"><i class="fas fa-check"></i> صحيحة: <?= (int) $H['sahi'] ?></span><span class="r"><i class="fas fa-times"></i> خاطئة: <?= (int) $H['wrong'] ?></span><span><i class="far fa-circle"></i> متروكة: <?= max(0, count($QS) - (int) $H['level']) ?></span><span><i class="fas fa-trophy" style="color:#c9962a"></i> ترتيبك: <?= $pos ?> من <?= $of ?></span></div>
    <a class="ox-btn" style="width:auto;display:inline-flex;padding:0 22px" href="account.php"><i class="fas fa-th-large"></i> العودة لامتحاناتي</a></div>
</section>
<h3 class="ox-h"><i class="fas fa-clipboard-check"></i> مراجعة الإجابات</h3>
<?php if ($mine === null): ?><div class="ox-empty"><i class="fas fa-eye-slash"></i> تظهر مراجعة كل سؤال مرة واحدة بعد التسليم مباشرة. هذه نتيجتك المحفوظة.</div><?php else: ?>
<div class="ox-rv"><?php foreach ($QS as $i => $q): $g = $mine[$q['qid']] ?? ''; $tag = $g === '' ? ['n', 'متروك'] : ($g === $q['ans'] ? ['g', '+' . (int) $Z['sahi']] : ['r', (int) $Z['wrong'] ? '−' . (int) $Z['wrong'] : 'خطأ']); ?>
  <div class="ox-q"><h3><span><?= $i + 1 ?></span><?= nl2br($h($q['qns'])) ?><em class="ox-tag <?= $tag[0] ?>"><?= $tag[1] ?></em></h3>
    <div class="ox-opts"><?php foreach ($q['options'] as $k => $o): $c = $o['optionid'] === $q['ans'] ? ' ok' : ($o['optionid'] === $g ? ' bad' : ''); ?><div class="ox-opt<?= $c ?>"><span data-l="<?= $letters[$k] ?? ($k + 1) ?>"><?= $h($o['option']) ?></span></div><?php endforeach; ?></div></div>
<?php endforeach; ?></div><?php endif; ?>
<?php uwciv_close(); exit;
}

/* ===== الصفحة الرئيسية للطالب ===== */
$Q = []; $hist = []; $rank = []; $myRank = 0; $total = 0;
if ($db) {
    try {
        $Q = oe_quizzes();
        $st = $db->prepare('SELECT * FROM history WHERE email = ? ORDER BY date DESC'); $st->execute([$me['email']]); foreach ($st->fetchAll() as $r) $hist[$r['eid']] = $r;
        $rank = $db->query('SELECT r.email, r.score, u.name, u.department FROM rank r LEFT JOIN user u ON u.email = r.email ORDER BY r.score DESC LIMIT 50')->fetchAll();
        foreach ($rank as $i => $r) if ($r['email'] === $me['email']) $myRank = $i + 1;
        foreach ($hist as $x) $total += (int) $x['score'];
    } catch (Throwable $e) { $err = 'تعذّر تحميل البيانات.'; }
}
$titles = []; foreach ($Q as $q) $titles[$q['eid']] = $q['title'];
$pending = count(array_filter($Q, function ($q) use ($hist) { return !isset($hist[$q['eid']]) && $q['qcount'] > 0; }));
uwciv_open('بوابة الامتحانات', 'الامتحانات الإلكترونية', '');
?>
<link rel="stylesheet" href="/assets/css/uwciv-exam.css?v=1">
<section class="ox-wel"><div><small><i class="fas fa-user-graduate"></i> <?= isset($_GET['welcome']) ? 'تم إنشاء حسابك بنجاح' : 'بوابة الامتحانات' ?></small><h2>أهلًا <?= $h($me['name']) ?> 👋</h2><p><?= $pending ? "لديك $pending امتحان بانتظارك — بالتوفيق!" : 'أنهيت كل الامتحانات المتاحة. أحسنت!' ?></p></div><a href="logout.php"><i class="fas fa-sign-out-alt"></i> خروج</a></section>
<div class="ox-k">
  <div style="--c:#1f7a45"><i class="fas fa-list-ol"></i><span><b><?= $pending ?></b><span>بانتظارك</span></span></div>
  <div style="--c:#2c5fd0"><i class="fas fa-check-double"></i><span><b><?= count($hist) ?></b><span>امتحان مكتمل</span></span></div>
  <div style="--c:#c9962a"><i class="fas fa-star"></i><span><b><?= $total ?></b><span>مجموع درجاتك</span></span></div>
  <div style="--c:#9b1f45"><i class="fas fa-trophy"></i><span><b><?= $myRank ?: '—' ?></b><span>ترتيبك العام</span></span></div>
</div>
<nav class="ox-nav"><a href="account.php" class="<?= $view === 'home' ? 'on' : '' ?>"><i class="fas fa-th-large"></i> الامتحانات</a><a href="?v=hist" class="<?= $view === 'hist' ? 'on' : '' ?>"><i class="fas fa-history"></i> سجلّي</a><a href="?v=rank" class="<?= $view === 'rank' ? 'on' : '' ?>"><i class="fas fa-trophy"></i> لوحة الشرف</a></nav>
<?php if ($err): ?><div class="ox-empty"><?= $h($err) ?></div><?php endif; ?>

<?php if ($view === 'hist'): ?>
  <h3 class="ox-h"><i class="fas fa-history"></i> سجل امتحاناتي</h3>
  <?php if (!$hist): ?><div class="ox-empty">لم تؤدِّ أي امتحان بعد.</div><?php else: ?>
  <table class="ox-tbl"><thead><tr><th>الامتحان</th><th>الدرجة</th><th>صحيحة</th><th>خاطئة</th><th>التاريخ</th><th></th></tr></thead><tbody>
  <?php foreach ($hist as $eid => $x): ?><tr><td><b><?= $h($titles[$eid] ?? 'امتحان محذوف') ?></b></td><td><b><?= (int) $x['score'] ?></b></td><td style="color:#1f7a45"><?= (int) $x['sahi'] ?></td><td style="color:#9b1f45"><?= (int) $x['wrong'] ?></td><td dir="ltr" style="text-align:right"><?= $h(substr((string) $x['date'], 0, 16)) ?></td><td><?php if (isset($titles[$eid])): ?><a href="?result=<?= rawurlencode($eid) ?>">التفاصيل ←</a><?php endif; ?></td></tr><?php endforeach; ?>
  </tbody></table><?php endif; ?>
<?php elseif ($view === 'rank'): ?>
  <h3 class="ox-h"><i class="fas fa-trophy"></i> لوحة الشرف</h3>
  <?php if (!$rank): ?><div class="ox-empty">لا توجد نتائج بعد.</div><?php else: ?>
  <table class="ox-tbl"><thead><tr><th>#</th><th>الطالب</th><th>الكلية</th><th>المجموع</th></tr></thead><tbody>
  <?php foreach ($rank as $i => $r): ?><tr class="<?= $r['email'] === $me['email'] ? 'me' : '' ?>"><td><span class="ox-medal <?= $i < 3 ? 'm' . ($i + 1) : '' ?>"><?= $i + 1 ?></span></td><td><?= $h($r['name'] ?: '—') ?><?= $r['email'] === $me['email'] ? ' <small>(أنت)</small>' : '' ?></td><td><?= $h($r['department'] ?? '') ?></td><td><b><?= (int) $r['score'] ?></b></td></tr><?php endforeach; ?>
  </tbody></table><?php endif; ?>
<?php else: ?>
  <h3 class="ox-h"><i class="fas fa-th-large"></i> الامتحانات المتاحة</h3>
  <?php if (!$Q): ?><div class="ox-empty"><i class="fas fa-inbox"></i> لا توجد امتحانات منشورة حاليًا.</div><?php endif; ?>
  <div class="ox-grid">
  <?php foreach ($Q as $i => $q): $x = $hist[$q['eid']] ?? null; $c = $cols[$i % 6];
    $pct = $x && $q['max'] ? max(0, min(100, round($x['score'] / $q['max'] * 100))) : 0; $G = oe_grade_label($pct); ?>
    <article class="ox-ex" style="--c:<?= $c ?>">
      <h3><?= $h($q['title']) ?></h3><?php if ($q['intro']): ?><p><?= $h(mb_strimwidth($q['intro'], 0, 140, '…')) ?></p><?php endif; ?>
      <div class="ox-meta"><span><i class="fas fa-list-ol"></i><?= $q['qcount'] ?> سؤال</span><span><i class="far fa-clock"></i><?= (int) $q['time'] ?> دقيقة</span><span><i class="fas fa-star"></i><?= $q['max'] ?> درجة</span></div>
      <?php if ($x): ?><div class="ox-done" style="--c2:<?= $G[1] ?>"><i class="fas <?= $G[2] ?>" style="color:<?= $G[1] ?>;font-size:20px"></i><span><b><?= (int) $x['score'] ?></b> / <?= $q['max'] ?><br><small><?= $G[0] ?> · <?= $pct ?>%</small></span></div>
        <a class="go done" href="?result=<?= rawurlencode($q['eid']) ?>"><i class="fas fa-chart-pie"></i> عرض النتيجة</a>
      <?php elseif (!$q['qcount']): ?><a class="go done" style="pointer-events:none;opacity:.6">قيد الإعداد</a>
      <?php else: ?><a class="go" href="?take=<?= rawurlencode($q['eid']) ?>" onclick="return confirm('سيبدأ المؤقّت (<?= (int) $q['time'] ?> دقيقة) فور الدخول، ولديك محاولة واحدة فقط. ابدأ الآن؟')"><i class="fas fa-play"></i> ابدأ الامتحان</a><?php endif; ?>
    </article>
  <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php uwciv_close(); ?>
