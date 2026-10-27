<?php
/* إدارة الامتحانات الإلكترونية */
require_once dirname(__DIR__) . '/inc/admin_ui.php';
require_once dirname(__DIR__) . '/inc/exam.php';
uwciv_admin_gate(['title' => 'الامتحانات الإلكترونية']);
$tab = $_GET['tab'] ?? 'exams'; $msg = ''; $err = '';
try { $db = oe_db(); } catch (Throwable $e) { $db = null; $err = 'تعذّر الاتصال بقاعدة بيانات الامتحانات.'; }

if ($db && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!uwciv_csrf_ok()) $err = 'انتهت صلاحية الصفحة، أعد المحاولة.';
    else try {
        $act = $_POST['act'] ?? ''; $back = 'exams';
        if ($act === 'save') {
            $data = ['title' => trim(mb_substr((string) $_POST['title'], 0, 100)), 'intro' => trim((string) ($_POST['intro'] ?? '')), 'tag' => trim(mb_substr((string) ($_POST['tag'] ?? ''), 0, 100)),
                'time' => max(1, min(600, (int) $_POST['time'])), 'sahi' => max(1, min(100, (int) $_POST['sahi'])), 'wrong' => max(0, min(100, (int) $_POST['wrong'])), 'questions' => []];
            if ($data['title'] === '') throw new RuntimeException('اكتب عنوان الامتحان.');
            foreach ((array) ($_POST['q'] ?? []) as $q) {
                $text = trim((string) ($q['t'] ?? '')); $opts = array_values(array_filter(array_map('trim', (array) ($q['o'] ?? [])), 'strlen'));
                if ($text === '' && !$opts) continue;
                $raw = array_map('trim', (array) ($q['o'] ?? [])); $ci = (int) ($q['c'] ?? -1);
                $correct = isset($raw[$ci]) && $raw[$ci] !== '' ? array_search($raw[$ci], $opts, true) : false;
                if ($text === '' || count($opts) < 2) throw new RuntimeException('كل سؤال يحتاج نصًّا وخيارين على الأقل (السؤال ' . (count($data['questions']) + 1) . ').');
                if ($correct === false) throw new RuntimeException('حدّد الإجابة الصحيحة للسؤال ' . (count($data['questions']) + 1) . '.');
                $data['questions'][] = [$text, $opts, $correct];
            }
            if (!$data['questions']) throw new RuntimeException('أضف سؤالًا واحدًا على الأقل.');
            $eid = (string) ($_POST['eid'] ?? '');
            if ($eid && !oe_quiz($eid)) $eid = '';
            oe_save_quiz($eid, $data);
            $msg = $eid ? 'تم حفظ تعديلات الامتحان.' : 'تم نشر الامتحان — أصبح متاحًا للطلاب الآن.';
        } elseif ($act === 'delquiz') {
            $eid = (string) $_POST['eid']; oe_delete_questions($eid);
            $db->prepare('DELETE FROM quiz WHERE eid = ?')->execute([$eid]);
            $st = $db->prepare('SELECT DISTINCT email FROM history WHERE eid = ?'); $st->execute([$eid]); $em = $st->fetchAll(PDO::FETCH_COLUMN);
            $db->prepare('DELETE FROM history WHERE eid = ?')->execute([$eid]); foreach ($em as $e) oe_update_rank($e);
            $msg = 'تم حذف الامتحان ونتائجه.';
        } elseif ($act === 'reset') {
            $db->prepare('DELETE FROM history WHERE eid = ? AND email = ?')->execute([(string) $_POST['eid'], (string) $_POST['email']]); oe_update_rank((string) $_POST['email']);
            $msg = 'تم إلغاء المحاولة — يستطيع الطالب إعادة الامتحان.'; $back = 'results';
        } elseif ($act === 'deluser') {
            $e = (string) $_POST['email'];
            foreach (['history', 'rank', 'user'] as $t) $db->prepare("DELETE FROM `$t` WHERE email = ?")->execute([$e]);
            $msg = 'تم حذف الطالب ونتائجه.'; $back = 'students';
        }
        if ($msg) { header('Location: dash.php?tab=' . $back . '&ok=' . rawurlencode($msg)); exit; }
    } catch (Throwable $e) { $err = $e instanceof RuntimeException ? $e->getMessage() : 'تعذّر الحفظ.'; if (($_POST['act'] ?? '') === 'save') $tab = 'build'; }
}
if (isset($_GET['ok'])) $msg = (string) $_GET['ok'];

$Q = []; $users = []; $hist = []; $fb = [];
if ($db) try {
    $Q = oe_quizzes();
    $users = $db->query('SELECT u.*, (SELECT COUNT(*) FROM history h WHERE h.email = u.email) AS n, (SELECT score FROM rank r WHERE r.email = u.email) AS total FROM user u ORDER BY name')->fetchAll();
    $hist = $db->query('SELECT h.*, u.name FROM history h LEFT JOIN user u ON u.email = h.email ORDER BY h.date DESC LIMIT 500')->fetchAll();
    try { $fb = $db->query('SELECT * FROM feedback ORDER BY date DESC LIMIT 100')->fetchAll(); } catch (Throwable $e) {}
} catch (Throwable $e) { $err = 'تعذّر قراءة البيانات.'; }
$titles = []; $maxes = []; foreach ($Q as $q) { $titles[$q['eid']] = $q['title']; $maxes[$q['eid']] = $q['max']; }

if ($tab === 'results' && isset($_GET['csv'])) {
    header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="exam-results-' . date('Y-m-d') . '.csv"');
    $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF"); fputcsv($o, ['الطالب', 'البريد', 'الامتحان', 'الدرجة', 'العظمى', 'صحيحة', 'خاطئة', 'التاريخ']);
    foreach ($hist as $x) if (empty($_GET['eid']) || $_GET['eid'] === $x['eid']) fputcsv($o, [$x['name'], $x['email'], $titles[$x['eid']] ?? '', $x['score'], $maxes[$x['eid']] ?? '', $x['sahi'], $x['wrong'], $x['date']]);
    exit;
}

/* بيانات المحرر */
$edit = null;
if ($tab === 'build') {
    if (!empty($_GET['eid']) && ($z = oe_quiz((string) $_GET['eid']))) {
        $qs = oe_questions($z['eid']); $edit = $z + ['questions' => []];
        foreach ($qs as $q) { $c = 0; foreach ($q['options'] as $k => $o) if ($o['optionid'] === $q['ans']) $c = $k; $edit['questions'][] = ['t' => $q['qns'], 'o' => array_column($q['options'], 'option'), 'c' => $c]; }
    }
    if ($err && ($_POST['act'] ?? '') === 'save') {
        $edit = ['eid' => $_POST['eid'] ?? '', 'title' => $_POST['title'] ?? '', 'intro' => $_POST['intro'] ?? '', 'tag' => $_POST['tag'] ?? '', 'time' => $_POST['time'] ?? 10, 'sahi' => $_POST['sahi'] ?? 1, 'wrong' => $_POST['wrong'] ?? 0, 'questions' => []];
        foreach ((array) ($_POST['q'] ?? []) as $q) $edit['questions'][] = ['t' => $q['t'] ?? '', 'o' => array_values((array) ($q['o'] ?? [])), 'c' => (int) ($q['c'] ?? 0)];
    }
}
$h = 'uwciv_h';
$tabs = ['exams' => ['fas fa-th-large', 'الامتحانات', count($Q)], 'build' => ['fas fa-plus-circle', $edit && !empty($edit['eid']) ? 'تعديل امتحان' : 'امتحان جديد', null], 'results' => ['fas fa-chart-bar', 'النتائج', count($hist)], 'students' => ['fas fa-user-graduate', 'الطلاب', count($users)]];
if ($fb) $tabs['feedback'] = ['far fa-comment-dots', 'الملاحظات', count($fb)];
$avg = $hist ? round(array_sum(array_map(function ($x) use ($maxes) { $m = $maxes[$x['eid']] ?? 0; return $m ? max(0, $x['score']) / $m * 100 : 0; }, $hist)) / count($hist)) : 0;
uwciv_admin_open('exams', 'الامتحانات الإلكترونية', 'أنشئ امتحانات مؤقّتة بأسئلة اختيار من متعدد، وتابع نتائج الطلاب وترتيبهم لحظة بلحظة.', '<a class="ad-b g" href="/onlineexam/index.php" target="_blank"><i class="fas fa-eye"></i> بوابة الطلاب</a><a class="ad-b p" href="?tab=build"><i class="fas fa-plus"></i> امتحان جديد</a>');
uwciv_admin_toast($err ?: $msg, (bool) $err);
?>
<style>
.ex-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px}
.ex-tabs a{display:inline-flex;gap:8px;align-items:center;border-radius:14px;padding:10px 16px;font-weight:800;background:#fff;color:var(--ink-3)!important;box-shadow:var(--sh-1);text-decoration:none!important}
.ex-tabs a em{font-style:normal;background:var(--mist);border-radius:20px;padding:0 8px;font-size:12px}
.ex-tabs a.on{background:var(--ink-3);color:#fff!important}.ex-tabs a.on em{background:rgba(255,255,255,.18)}
.ex-g{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px}
.ex-c{background:#fff;border-radius:22px;box-shadow:var(--sh-1);padding:20px;border-top:5px solid var(--c);display:flex;flex-direction:column;gap:10px}
.ex-c h3{margin:0;font-size:17px;font-weight:900;color:var(--ink)}
.ex-c p{margin:0;color:var(--muted);font-size:13.5px;line-height:1.8}
.ex-m{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.ex-m div{background:var(--mist);border-radius:12px;padding:8px;text-align:center}.ex-m b{display:block;font-size:18px;color:var(--ink)}.ex-m span{font-size:11.5px;color:var(--muted);font-weight:700}
.ex-bar{height:8px;background:#e6efe9;border-radius:6px;overflow:hidden}.ex-bar i{display:block;height:100%;background:linear-gradient(90deg,var(--c),#7fe0a6)}
.ex-f{display:flex;gap:6px;margin-top:auto;flex-wrap:wrap}.ex-f form{margin:0;margin-inline-start:auto}
.qb{background:#fff;border-radius:20px;box-shadow:var(--sh-1);padding:18px 20px;margin-bottom:12px;border-inline-start:5px solid var(--teal);animation:cpIn .3s ease}
.qb-h{display:flex;gap:10px;align-items:center;margin-bottom:10px}
.qb-h b{width:34px;height:34px;border-radius:10px;display:grid;place-items:center;background:var(--ink-3);color:#fff;flex:none}
.qb-h .sp{flex:1}
.qb-h button{border:0;background:var(--mist);width:34px;height:34px;border-radius:10px;cursor:pointer;color:var(--ink-3)}
.qb-h button.rm{background:#fdecef;color:#c0392b}
.qb textarea{width:100%;border:2px solid var(--ad-line);border-radius:14px;padding:10px 14px;font-family:inherit;font-size:15px;min-height:60px;resize:vertical;background:var(--mist);box-sizing:border-box}
.qb textarea:focus{outline:none;border-color:var(--teal);background:#fff}
.qb-o{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-top:10px}
.qb-o label{display:flex;gap:8px;align-items:center;border:2px solid var(--ad-line);border-radius:12px;padding:0 10px;background:#fff;transition:.2s}
.qb-o label:has(input[type=radio]:checked){border-color:#1f7a45;background:#e4f4ea}
.qb-o input[type=radio]{width:18px;height:18px;accent-color:#1f7a45;flex:none}
.qb-o input[type=text]{flex:1;border:0;outline:none;background:transparent;height:42px;font-family:inherit;font-size:14.5px;min-width:0}
.qb-o .x{border:0;background:none;color:#c0392b;cursor:pointer;opacity:.6}
.qb-add{border:1.5px dashed #bcd8c6;background:#fff;color:var(--teal);border-radius:10px;padding:6px 12px;font-weight:800;cursor:pointer;font-family:inherit;margin-top:8px;font-size:13px}
.qb-new{width:100%;border:2px dashed #bcd8c6;background:#f8fcf9;color:var(--teal);border-radius:18px;padding:16px;font-weight:900;font-family:inherit;font-size:15px;cursor:pointer;margin-bottom:14px}
.qb-new:hover{background:var(--cyan-soft)}
.qb-sticky{position:sticky;bottom:12px;z-index:5;background:rgba(255,255,255,.95);backdrop-filter:blur(8px);border-radius:18px;box-shadow:0 10px 30px -12px rgba(0,0,0,.3);padding:12px 16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.qb-sticky .sp{flex:1;color:var(--muted);font-weight:700;font-size:13.5px}
.ex-sum{display:flex;gap:6px;flex-wrap:wrap}
@media(max-width:700px){.qb-o{grid-template-columns:minmax(0,1fr)}.ad-table thead{display:none}.ad-table tr{display:grid;padding:8px 0;border-bottom:1px solid #eef3ef}.ad-table td{border:0;padding:5px 14px}}
</style>
<div class="cp-kpis">
  <div class="cp-kpi" style="--c:#1f7a45"><i class="fas fa-laptop-code"></i><span><b><?= count($Q) ?></b><span>امتحان منشور</span></span></div>
  <div class="cp-kpi" style="--c:#2c5fd0"><i class="fas fa-user-graduate"></i><span><b><?= count($users) ?></b><span>طالب مسجّل</span></span></div>
  <div class="cp-kpi" style="--c:#c9962a"><i class="fas fa-check-double"></i><span><b><?= count($hist) ?></b><span>محاولة مكتملة</span></span></div>
  <div class="cp-kpi" style="--c:#9b1f45"><i class="fas fa-percentage"></i><span><b><?= $avg ?>%</b><span>متوسط الأداء</span></span></div>
</div>
<nav class="ex-tabs"><?php foreach ($tabs as $k => $t): ?><a href="?tab=<?= $k ?>" class="<?= $tab === $k ? 'on' : '' ?>"><i class="<?= $t[0] ?>"></i> <?= $t[1] ?><?= $t[2] !== null ? ' <em>' . $t[2] . '</em>' : '' ?></a><?php endforeach; ?></nav>

<?php if ($tab === 'build'): $E = $edit ?: ['eid' => '', 'title' => '', 'intro' => '', 'tag' => '', 'time' => 15, 'sahi' => 1, 'wrong' => 0, 'questions' => [['t' => '', 'o' => ['', '', '', ''], 'c' => 0]]]; ?>
<form method="post" id="qbf"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="save"><input type="hidden" name="eid" value="<?= $h($E['eid']) ?>">
  <div class="cp-card"><h2><i class="fas fa-cog"></i> بيانات الامتحان</h2><p><?= $E['eid'] ? 'تعديل الأسئلة لا يحذف نتائج من أدّوا الامتحان سابقًا.' : 'املأ البيانات ثم أضف الأسئلة بالأسفل.' ?></p>
    <div class="cp-f three">
      <label class="w">عنوان الامتحان *<div class="cp-in"><i class="fas fa-heading"></i><input name="title" required maxlength="100" value="<?= $h($E['title']) ?>" placeholder="مثال: اختبار منتصف الفصل — أصول الفقه"></div></label>
      <label>المدة (دقيقة)<div class="cp-in"><i class="far fa-clock"></i><input type="number" name="time" min="1" max="600" value="<?= (int) $E['time'] ?>"></div></label>
      <label>درجة الإجابة الصحيحة<div class="cp-in"><i class="fas fa-plus"></i><input type="number" name="sahi" min="1" max="100" value="<?= (int) $E['sahi'] ?>"></div></label>
      <label>خصم الإجابة الخاطئة<div class="cp-in"><i class="fas fa-minus"></i><input type="number" name="wrong" min="0" max="100" value="<?= (int) $E['wrong'] ?>"></div></label>
      <label class="w">تعليمات / وصف<div class="cp-in ta"><i class="fas fa-align-right"></i><textarea name="intro" style="min-height:70px" placeholder="يظهر للطالب في بطاقة الامتحان"><?= $h($E['intro']) ?></textarea></div></label>
    </div></div>
  <div id="qbl"></div>
  <button type="button" class="qb-new" id="qbadd"><i class="fas fa-plus-circle"></i> إضافة سؤال</button>
  <div class="qb-sticky"><span class="sp" id="qbsum"></span><a class="cp-b g" href="?tab=exams">إلغاء</a><button class="cp-b p"><i class="fas fa-paper-plane"></i> <?= $E['eid'] ? 'حفظ التعديلات' : 'نشر الامتحان' ?></button></div>
</form>
<template id="qbt"><div class="qb"><div class="qb-h"><b class="n"></b><span class="sp" style="color:var(--muted);font-size:13px;font-weight:700">حدّد الإجابة الصحيحة بالدائرة</span><button type="button" class="up" title="أعلى"><i class="fas fa-arrow-up"></i></button><button type="button" class="dn" title="أسفل"><i class="fas fa-arrow-down"></i></button><button type="button" class="cp" title="نسخ"><i class="far fa-clone"></i></button><button type="button" class="rm" title="حذف"><i class="fas fa-trash"></i></button></div>
  <textarea class="t" placeholder="اكتب نص السؤال…"></textarea><div class="qb-o"></div><button type="button" class="qb-add">＋ خيار</button></div></template>
<script>
(function(){var L=document.getElementById('qbl'),T=document.getElementById('qbt'),init=<?= json_encode($E['questions'], JSON_UNESCAPED_UNICODE) ?>,letters=['أ','ب','ج','د','هـ','و'];
function opt(box,val,checked){if(box.children.length>=6)return;var l=document.createElement('label');l.innerHTML='<input type="radio"><input type="text" placeholder="الخيار"><button type="button" class="x" title="حذف الخيار">✕</button>';l.querySelector('[type=text]').value=val||'';l.querySelector('[type=radio]').checked=!!checked;box.appendChild(l);}
function add(q,after){q=q||{t:'',o:['','','',''],c:0};var n=T.content.firstElementChild.cloneNode(true);n.querySelector('.t').value=q.t||'';var box=n.querySelector('.qb-o');(q.o&&q.o.length?q.o:['','']).forEach(function(v,i){opt(box,v,i===q.c)});if(after)after.after(n);else L.appendChild(n);ren();return n;}
function ren(){[].forEach.call(L.children,function(b,i){b.querySelector('.n').textContent=i+1;b.querySelector('.t').name='q['+i+'][t]';[].forEach.call(b.querySelectorAll('.qb-o label'),function(l,k){l.querySelector('[type=radio]').name='q['+i+'][c]';l.querySelector('[type=radio]').value=k;var x=l.querySelector('[type=text]');x.name='q['+i+'][o]['+k+']';x.placeholder='الخيار '+(letters[k]||k+1);});});
  var s=document.getElementById('qbsum'),t=+document.querySelector('[name=time]').value||0,m=+document.querySelector('[name=sahi]').value||0;s.innerHTML='<i class="fas fa-list-ol"></i> '+L.children.length+' سؤال · <i class="fas fa-star"></i> الدرجة العظمى '+(L.children.length*m)+' · <i class="far fa-clock"></i> '+t+' دقيقة';}
init.forEach(function(q){add(q)});if(!L.children.length)add();
document.getElementById('qbadd').onclick=function(){add().querySelector('.t').focus();window.scrollBy(0,200);};
L.addEventListener('click',function(e){var b=e.target.closest('.qb');if(!b)return;
  if(e.target.closest('.rm')){if(L.children.length>1){b.remove();ren();}}
  else if(e.target.closest('.up')){if(b.previousElementSibling){b.previousElementSibling.before(b);ren();}}
  else if(e.target.closest('.dn')){if(b.nextElementSibling){b.nextElementSibling.after(b);ren();}}
  else if(e.target.closest('.cp')){var o=[].map.call(b.querySelectorAll('.qb-o [type=text]'),function(x){return x.value}),c=[].findIndex.call(b.querySelectorAll('.qb-o [type=radio]'),function(x){return x.checked});add({t:b.querySelector('.t').value,o:o,c:c},b);}
  else if(e.target.closest('.qb-add')){opt(b.querySelector('.qb-o'),'',false);ren();}
  else if(e.target.closest('.x')){var box=b.querySelector('.qb-o');if(box.children.length>2){e.target.closest('label').remove();ren();}}});
document.querySelectorAll('[name=time],[name=sahi]').forEach(function(i){i.addEventListener('input',ren)});
document.getElementById('qbf').addEventListener('submit',function(e){var bad=[].findIndex.call(L.children,function(b){return !b.querySelector('[type=radio]:checked')});if(bad>-1){e.preventDefault();uwcToast('حدّد الإجابة الصحيحة للسؤال '+(bad+1),true);L.children[bad].scrollIntoView({behavior:'smooth',block:'center'});}});})();
</script>

<?php elseif ($tab === 'results'): $fe = (string) ($_GET['eid'] ?? ''); ?>
<div class="ad-bar"><div class="ad-in"><i class="fas fa-search"></i><input placeholder="ابحث باسم الطالب أو بريده…" data-filter="#rsl"></div>
  <div class="ad-chips"><button class="ad-chip on" type="button" data-chip="all">كل الامتحانات</button><?php foreach ($Q as $q): ?><button class="ad-chip" type="button" data-chip="e<?= $h($q['eid']) ?>"><?= $h(mb_strimwidth($q['title'], 0, 30, '…')) ?></button><?php endforeach; ?></div>
  <a class="ad-b g" href="?tab=results&csv=1"><i class="fas fa-file-excel"></i> تصدير Excel</a></div>
<div class="ad-tw"><table class="ad-table"><thead><tr><th>الطالب</th><th>الامتحان</th><th>الدرجة</th><th>صحيحة / خاطئة</th><th>التاريخ</th><th></th></tr></thead><tbody id="rsl">
<?php foreach ($hist as $x): $m = $maxes[$x['eid']] ?? 0; $p = $m ? max(0, min(100, round($x['score'] / $m * 100))) : 0; $G = oe_grade_label($p); ?>
<tr class="fi" data-s="<?= $h($x['name'] . ' ' . $x['email']) ?>" data-g="e<?= $h($x['eid']) ?>">
  <td><div class="ad-who"><span class="ad-av" style="--c:<?= $G[1] ?>"><?= $h(mb_substr((string) $x['name'], 0, 1) ?: '?') ?></span><span><b><?= $h($x['name'] ?: '—') ?></b><small dir="ltr"><?= $h($x['email']) ?></small></span></div></td>
  <td><?= $h($titles[$x['eid']] ?? 'امتحان محذوف') ?></td>
  <td><b><?= (int) $x['score'] ?></b> / <?= $m ?> <span class="cp-pill" style="background:color-mix(in srgb,<?= $G[1] ?> 12%,#fff);color:<?= $G[1] ?>"><?= $G[0] ?> <?= $p ?>%</span></td>
  <td><span style="color:#1f7a45;font-weight:800"><?= (int) $x['sahi'] ?></span> / <span style="color:#9b1f45;font-weight:800"><?= (int) $x['wrong'] ?></span></td>
  <td dir="ltr" style="text-align:right;color:var(--muted)"><?= $h(substr((string) $x['date'], 0, 16)) ?></td>
  <td><form method="post" data-confirm="إلغاء محاولة <?= $h($x['name']) ?> والسماح له بإعادة الامتحان؟"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="reset"><input type="hidden" name="eid" value="<?= $h($x['eid']) ?>"><input type="hidden" name="email" value="<?= $h($x['email']) ?>"><button class="cp-b g sm" title="السماح بإعادة الامتحان"><i class="fas fa-redo"></i> إعادة</button></form></td>
</tr>
<?php endforeach; ?></tbody></table></div>
<div class="cp-card fi-empty" <?= $hist ? 'hidden' : '' ?>><div class="cp-empty"><i class="fas fa-chart-bar"></i><?= $hist ? 'لا نتائج مطابقة.' : 'لا توجد نتائج بعد.' ?></div></div>
<?php if ($fe): ?><script>setTimeout(function(){var c=document.querySelector('[data-chip="e<?= $h($fe) ?>"]');if(c)c.click();},50);</script><?php endif; ?>

<?php elseif ($tab === 'students'): ?>
<div class="ad-bar"><div class="ad-in"><i class="fas fa-search"></i><input placeholder="ابحث بالاسم أو البريد أو الكلية…" data-filter="#stl"></div></div>
<div class="ad-tw"><table class="ad-table"><thead><tr><th>الطالب</th><th>الكلية</th><th>الجوال</th><th>امتحانات</th><th>المجموع</th><th></th></tr></thead><tbody id="stl">
<?php foreach ($users as $i => $u): ?>
<tr class="fi" data-s="<?= $h($u['name'] . ' ' . $u['email'] . ' ' . $u['department']) ?>">
  <td><div class="ad-who"><span class="ad-av" style="--c:<?= ['#1f7a45', '#2c5fd0', '#9b1f45', '#c9962a', '#0e9aa7', '#5b4bb7'][$i % 6] ?>"><?= $h(mb_substr((string) $u['name'], 0, 1)) ?></span><span><b><?= $h($u['name']) ?> <small><?= $u['gender'] === 'F' ? '♀' : '♂' ?></small></b><small dir="ltr"><?= $h($u['email']) ?></small></span></div></td>
  <td><?= $h($u['department']) ?></td><td dir="ltr" style="text-align:right"><?= $h($u['mob']) ?></td><td><b><?= (int) $u['n'] ?></b></td><td><b><?= (int) $u['total'] ?></b></td>
  <td><form method="post" data-confirm="حذف الطالب <?= $h($u['name']) ?> وكل نتائجه؟"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="deluser"><input type="hidden" name="email" value="<?= $h($u['email']) ?>"><button class="cp-b r sm" title="حذف"><i class="fas fa-trash"></i></button></form></td>
</tr>
<?php endforeach; ?></tbody></table></div>
<div class="cp-card fi-empty" <?= $users ? 'hidden' : '' ?>><div class="cp-empty"><i class="fas fa-user-graduate"></i><?= $users ? 'لا نتائج مطابقة.' : 'لم يسجّل أي طالب بعد.' ?></div></div>

<?php elseif ($tab === 'feedback'): ?>
<?php foreach ($fb as $f): ?><div class="cp-card" style="padding:18px 22px"><h2 style="font-size:16px"><i class="far fa-comment-dots"></i> <?= $h($f['subject']) ?> <small style="color:var(--muted);font-weight:700;font-size:12.5px;margin-inline-start:auto"><?= $h($f['name']) ?> · <span dir="ltr"><?= $h($f['email']) ?></span> · <?= $h($f['date']) ?></small></h2><p style="margin:8px 0 0;color:var(--text)"><?= nl2br($h($f['feedback'])) ?></p></div><?php endforeach; ?>

<?php else: ?>
<?php if (!$Q): ?><div class="cp-card"><div class="cp-empty"><i class="fas fa-laptop-code"></i>لا توجد امتحانات بعد.<br><a class="cp-b p" style="margin-top:12px" href="?tab=build"><i class="fas fa-plus"></i> أنشئ أول امتحان</a></div></div><?php endif; ?>
<div class="ex-g">
<?php foreach ($Q as $i => $q): $c = ['#1f7a45', '#2c5fd0', '#9b1f45', '#c9962a', '#0e9aa7', '#5b4bb7'][$i % 6]; $ap = $q['max'] ? max(0, min(100, round($q['avg'] / $q['max'] * 100))) : 0; ?>
  <article class="ex-c" style="--c:<?= $c ?>">
    <h3><?= $h($q['title']) ?></h3><?php if ($q['intro']): ?><p><?= $h(mb_strimwidth($q['intro'], 0, 120, '…')) ?></p><?php endif; ?>
    <div class="ex-sum"><span class="cp-pill k"><i class="far fa-clock"></i> <?= (int) $q['time'] ?> دقيقة</span><span class="cp-pill y">+<?= (int) $q['sahi'] ?> صحيحة</span><?php if ($q['wrong']): ?><span class="cp-pill r">−<?= (int) $q['wrong'] ?> خاطئة</span><?php endif; ?></div>
    <div class="ex-m"><div><b><?= $q['qcount'] ?></b><span>سؤال</span></div><div><b><?= $q['attempts'] ?></b><span>محاولة</span></div><div><b><?= $ap ?>%</b><span>متوسط</span></div></div>
    <div class="ex-bar" title="متوسط الأداء"><i style="width:<?= $ap ?>%"></i></div>
    <div class="ex-f"><a class="cp-b p sm" href="?tab=build&eid=<?= rawurlencode($q['eid']) ?>"><i class="fas fa-pen"></i> تعديل الأسئلة</a><a class="cp-b g sm" href="?tab=results&eid=<?= rawurlencode($q['eid']) ?>"><i class="fas fa-chart-bar"></i> النتائج</a>
      <form method="post" data-confirm="حذف «<?= $h($q['title']) ?>» مع أسئلته ونتائجه نهائيًا؟"><?= uwciv_csrf_field() ?><input type="hidden" name="act" value="delquiz"><input type="hidden" name="eid" value="<?= $h($q['eid']) ?>"><button class="cp-b r sm" title="حذف"><i class="fas fa-trash"></i></button></form></div>
  </article>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php uwciv_admin_close(); ?>
