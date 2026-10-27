<?php
/* طلبات الالتحاق — للمشرف العام فقط */
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/admin_ui.php';
uwciv_admin_gate(['title' => 'طلبات الالتحاق']);
$q = trim((string) ($_GET['q'] ?? ''));
$stage = trim((string) ($_GET['stage'] ?? ''));
$rows = []; $total = 0; $byStage = []; $byCountry = []; $dbErr = false;
try {
    $db = db_pdo('logweb'); $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = 'SELECT * FROM info WHERE 1=1'; $par = [];
    if ($q !== '') { $sql .= ' AND (fname LIKE ? OR lname LIKE ? OR email LIKE ? OR phone LIKE ? OR depart LIKE ? OR country LIKE ?)'; $par = array_fill(0, 6, '%' . $q . '%'); }
    if ($stage !== '') { $sql .= ' AND stage = ?'; $par[] = $stage; }
    $st = $db->prepare($sql . ' ORDER BY id DESC LIMIT 500'); $st->execute($par); $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    foreach ($db->query('SELECT stage, COUNT(*) n FROM info GROUP BY stage')->fetchAll(PDO::FETCH_ASSOC) as $x) { $byStage[(string) $x['stage']] = (int) $x['n']; $total += (int) $x['n']; }
    foreach ($db->query('SELECT country, COUNT(*) n FROM info GROUP BY country ORDER BY n DESC LIMIT 6')->fetchAll(PDO::FETCH_ASSOC) as $x) $byCountry[(string) $x['country']] = (int) $x['n'];
} catch (Throwable $e) { $dbErr = true; }

/* تصدير CSV (يفتح في Excel) */
if (isset($_GET['csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="applications-' . date('Y-m-d') . '.csv"');
    $o = fopen('php://output', 'w'); fwrite($o, "\xEF\xBB\xBF");
    fputcsv($o, ['الرقم', 'الاسم الأول', 'الاسم الأخير', 'البريد', 'الجوال', 'تاريخ الميلاد', 'الجنس', 'الدولة', 'المرحلة', 'القسم', 'رقم الهوية', 'رقم الجواز', 'العنوان']);
    foreach ($rows as $x) fputcsv($o, [$x['id'], $x['fname'] ?? '', $x['lname'] ?? '', $x['email'] ?? '', $x['phone'] ?? '', $x['birth'] ?? '', $x['gender'] ?? '', $x['country'] ?? '', $x['stage'] ?? '', $x['depart'] ?? '', $x['id_iden'] ?? '', $x['id_pass'] ?? '', $x['address'] ?? '']);
    exit;
}

$h = 'uwciv_h';
$doc = function ($f, $label) { return $f ? '<a class="cp-b g sm" target="_blank" href="/student/admin/file.php?f=' . rawurlencode($f) . '"><i class="fas fa-paperclip"></i> ' . $label . '</a>' : ''; };
$colors = ['#1f7a45', '#2c5fd0', '#9b1f45', '#c9962a', '#0e9aa7', '#5b4bb7'];
$qs = function ($extra) use ($q, $stage) { return '?' . http_build_query(array_filter(array_merge(['q' => $q, 'stage' => $stage], $extra), 'strlen')); };
$acts = '<a class="ad-b g" href="' . $h($qs(['csv' => 1])) . '"><i class="fas fa-file-excel"></i> تصدير Excel</a><a class="ad-b p" href="/apply" target="_blank"><i class="fas fa-external-link-alt"></i> صفحة التسجيل</a>';
uwciv_admin_open('applications', 'طلبات الالتحاق', 'كل الطلبات المقدّمة عبر صفحة «سجّل معنا» مع مستنداتها — مرئية للمشرف العام فقط.', $acts);
?>
<style>
.ap-stage{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin-bottom:16px}
.ap-stage a{background:#fff;border-radius:18px;padding:14px 16px;box-shadow:var(--sh-1);text-decoration:none!important;color:var(--text)!important;border:2px solid transparent;transition:.2s;display:flex;gap:12px;align-items:center}
.ap-stage a:hover{transform:translateY(-3px)}
.ap-stage a.on{border-color:var(--c)}
.ap-stage i{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;color:#fff;background:var(--c);flex:none}
.ap-stage b{display:block;font-size:22px;font-weight:900;color:var(--ink);line-height:1.1}.ap-stage span{color:var(--muted);font-size:13px;font-weight:700}
.ap-c{background:#fff;border-radius:20px;box-shadow:var(--sh-1);margin-bottom:10px;overflow:hidden;border-inline-start:4px solid var(--c)}
.ap-c summary{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(0,1.6fr) minmax(0,1fr) auto;gap:14px;align-items:center;padding:14px 18px;cursor:pointer;list-style:none}
.ap-c summary::-webkit-details-marker{display:none}
.ap-c summary b{display:block;color:var(--ink);font-size:15px}.ap-c summary small{color:var(--muted);font-size:12.5px}
.ap-c summary .chev{width:32px;height:32px;border-radius:10px;display:grid;place-items:center;background:var(--mist);color:var(--ink-3);transition:.25s}
.ap-c[open] summary{background:#f8fbf9}.ap-c[open] .chev{transform:rotate(180deg)}
.ap-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;padding:16px 18px}
.ap-grid div{background:var(--mist);border-radius:12px;padding:10px 12px}
.ap-grid small{display:block;color:var(--muted);font-size:12px;font-weight:700}.ap-grid b{font-size:14.5px;word-break:break-word;color:var(--ink)}
.ap-acts{display:flex;gap:8px;flex-wrap:wrap;padding:0 18px 16px}
.ap-ctry{display:flex;gap:8px;flex-wrap:wrap;margin:-4px 0 16px}
@media(max-width:860px){.ap-c summary{grid-template-columns:minmax(0,1fr) auto}.ap-c summary>span:nth-child(2),.ap-c summary>span:nth-child(3){display:none}}
</style>
<?php if ($dbErr): ?><div class="cp-card"><div class="cp-empty"><i class="fas fa-database"></i>تعذّر الاتصال بقاعدة البيانات حاليًا.</div></div><?php endif; ?>
<div class="ap-stage">
  <a href="<?= $h($qs(['stage' => ''])) ?>" class="<?= $stage === '' ? 'on' : '' ?>" style="--c:#0b3119"><i class="fas fa-layer-group"></i><span><b><?= $total ?></b><span>كل الطلبات</span></span></a>
  <?php $i = 0; foreach ($byStage as $s => $n): $c = $colors[$i++ % 6]; ?><a href="<?= $h($qs(['stage' => $s])) ?>" class="<?= $stage === $s ? 'on' : '' ?>" style="--c:<?= $c ?>"><i class="fas fa-user-graduate"></i><span><b><?= $n ?></b><span><?= $h($s ?: 'غير محدد') ?></span></span></a><?php endforeach; ?>
</div>
<form class="ad-bar" method="get">
  <?php if ($stage !== ''): ?><input type="hidden" name="stage" value="<?= $h($stage) ?>"><?php endif; ?>
  <div class="ad-in"><i class="fas fa-search"></i><input name="q" value="<?= $h($q) ?>" placeholder="ابحث بالاسم أو البريد أو الجوال أو القسم أو الدولة…"></div>
  <button class="ad-b p"><i class="fas fa-search"></i> بحث</button><?php if ($q !== ''): ?><a class="ad-b g" href="<?= $h($qs(['q' => ''])) ?>">مسح</a><?php endif; ?>
</form>
<?php if ($byCountry): ?><div class="ap-ctry"><?php foreach ($byCountry as $c => $n): ?><span class="cp-pill k"><i class="fas fa-globe-africa"></i> <?= $h($c ?: '—') ?> · <?= $n ?></span><?php endforeach; ?></div><?php endif; ?>
<?php if (!$rows && !$dbErr): ?><div class="cp-card"><div class="cp-empty"><i class="fas fa-user-graduate"></i>لا توجد طلبات مطابقة<?= $q !== '' ? ' لبحثك' : '' ?>.</div></div><?php endif; ?>
<?php $si = array_flip(array_keys($byStage)); foreach ($rows as $x): $c = $colors[($si[(string) $x['stage']] ?? 0) % 6]; $nm = trim(($x['fname'] ?? '') . ' ' . ($x['lname'] ?? '')); ?>
<details class="ap-c" style="--c:<?= $c ?>">
  <summary>
    <span class="ad-who"><span class="ad-av" style="--c:<?= $c ?>"><?= $h(mb_substr($nm, 0, 1)) ?></span><span><b><?= $h($nm) ?></b><small dir="ltr">#<?= (int) $x['id'] ?> · <?= $h($x['email'] ?? '') ?></small></span></span>
    <span><b><?= $h($x['depart'] ?? '') ?></b><small><?= $h($x['stage'] ?? '') ?></small></span>
    <span><b><?= $h($x['country'] ?? '') ?></b><small dir="ltr"><?= $h($x['phone'] ?? '') ?></small></span>
    <span class="chev"><i class="fas fa-chevron-down"></i></span>
  </summary>
  <div class="ap-grid">
    <div><small>تاريخ الميلاد</small><b><?= $h(($x['birth'] ?? '') ?: '—') ?></b></div>
    <div><small>الجنس</small><b><?= $h(($x['gender'] ?? '') ?: '—') ?></b></div>
    <div><small>رقم الهوية</small><b dir="ltr"><?= $h(($x['id_iden'] ?? '') ?: '—') ?></b></div>
    <div><small>رقم الجواز</small><b dir="ltr"><?= $h(($x['id_pass'] ?? '') ?: '—') ?></b></div>
    <div style="grid-column:1/-1"><small>العنوان</small><b><?= $h(($x['address'] ?? '') ?: '—') ?></b></div>
  </div>
  <div class="ap-acts">
    <?= $doc($x['img1'] ?? '', 'الهوية / الجواز') . $doc($x['imgpass'] ?? '', 'المستند الثاني') ?>
    <a class="cp-b p sm" href="mailto:<?= $h($x['email'] ?? '') ?>?subject=<?= rawurlencode('طلب الالتحاق — جامعة بنيان العالمية') ?>"><i class="fas fa-envelope"></i> مراسلة</a>
    <?php if (!empty($x['phone'])): ?><a class="cp-b g sm" target="_blank" rel="noopener" href="https://wa.me/<?= preg_replace('/\D/', '', $x['phone']) ?>"><i class="fab fa-whatsapp"></i> واتساب</a><?php endif; ?>
  </div>
</details>
<?php endforeach; ?>
<?php uwciv_admin_close(); ?>
