<?php
require_once __DIR__ . '/inc/i18n.php';
require_once __DIR__ . '/inc/pages.php';
$p = isset($_GET['p']) ? preg_replace('/[^a-z_]/', '', $_GET['p']) : '';
if ($p === 'journal') { header('Location: /journal', true, 301); exit; }
if ($p === 'jobs') { header('Location: /jobs', true, 301); exit; }
if ($p === 'regs') { header('Location: /regulations.php', true, 301); exit; }
if ($p === '' || !isset($UWCIV_T[$p])) { header('Location: /index.php'); exit; }

// إيجاد القسم الذي تنتمي له الصفحة لعرض روابطه الجانبية
$parent = null; $siblings = [];
foreach (uwciv_menu() as $m) {
    if (empty($m[2])) continue;
    foreach ($m[2] as $s) {
        if ($s[1] === '/info.php?p=' . $p || $s[0] === $p) { $parent = $m[0]; $siblings = $m[2]; break 2; }
    }
}
$title = t($p);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title) ?> | <?= htmlspecialchars(t('uni')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/plugins/all.min.css">
    <link rel="stylesheet" href="/assets/css/plugins/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/header1.php'; ?>

<section class="u-page-hero">
  <video class="u-bgvid u-bgvid-soft" autoplay muted loop playsinline preload="none" poster="/assets/video/pattern.jpg" aria-hidden="true"><source src="/assets/video/pattern.mp4" type="video/mp4"></video>
  <div class="u-bgshade"></div>
  <div class="u-wrap" style="position:relative;z-index:2">
    <div class="u-hero-top">
      <nav class="u-crumbs"><a href="/index.php"><?= t('home') ?></a><?php if ($parent): ?> <i class="fas fa-chevron-left"></i> <span><?= t($parent) ?></span><?php endif; ?></nav>
      <a class="u-back notranslate" href="/index.php" onclick="if(history.length>1&&document.referrer.indexOf(location.host)>-1){history.back();return false;}"><i class="fas fa-arrow-right"></i> <?= t('back') ?></a>
    </div>
    <h1><?= htmlspecialchars($title) ?></h1>
  </div>
</section>

<section class="u-page">
  <div class="u-wrap u-page-grid">
    <?php if (isset($UWCIV_PAGES[$p])): $pg = $UWCIV_PAGES[$p]; ?>
    <article class="u-panel u-doc">
      <p class="u-doc-lead"><?= $pg['lead'] ?></p>
      <?php foreach ($pg['sections'] as $sec): ?>
        <section class="u-doc-sec"><h2><?= $sec[0] ?></h2><?= $sec[1] ?></section>
      <?php endforeach; ?>
      <div class="u-doc-foot"><a class="u-btn" href="/form2.php"><i class="fas fa-headset"></i> <?= t('support') ?></a></div>
    </article>
    <?php else: ?>
    <article class="u-panel u-soon">
      <div class="u-soon-ico"><i class="fas fa-feather-alt"></i></div>
      <h2><?= t('soon_t') ?></h2>
      <p><?= t('soon_d') ?></p>
      <a class="u-btn" href="/form2.php"><i class="fas fa-headset"></i> <?= t('support') ?></a>
    </article>
    <?php endif; ?>
    <?php if ($siblings): ?>
    <aside class="u-panel u-side notranslate">
      <h3><?= t('in_section') ?></h3>
      <ul>
        <?php foreach ($siblings as $s): ?>
          <li><a class="<?= ($s[1] === '/info.php?p=' . $p) ? 'is-on' : '' ?>" href="<?= $s[1] ?>"><?= t($s[0]) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </aside>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
</div>
</body>
</html>
