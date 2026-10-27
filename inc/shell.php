<?php
/* غلاف موحّد للصفحات الجديدة: رأس الصفحة + الترويسة + التذييل */
require_once __DIR__ . '/i18n.php';

function uwciv_open($title, $crumb = null, $sub = '')
{
    global $UWCIV_LANG, $UWCIV_LANGS, $UWCIV_T;
    ?><!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($title) ?> | <?= htmlspecialchars(t('uni')) ?></title>
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#0b3119">
    <link rel="apple-touch-icon" href="/assets/app/apple-touch-icon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    $__desc = trim(strip_tags($sub)) ?: t('f_about');
    $__path = strtok($_SERVER['REQUEST_URI'] ?? '/', '#');
    $__canon = 'https://your-domain.com' . $__path;
    ?>
    <meta name="description" content="<?= htmlspecialchars(mb_substr($__desc, 0, 300)) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= htmlspecialchars($__canon) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= htmlspecialchars(t('uni')) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($title) ?> | <?= htmlspecialchars(t('uni')) ?>">
    <meta property="og:description" content="<?= htmlspecialchars(mb_substr($__desc, 0, 300)) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($__canon) ?>">
    <meta property="og:image" content="https://your-domain.com/assets/app/icon-512.png">
    <meta property="og:locale" content="ar_AR">
    <link rel="icon" type="image/png" sizes="48x48" href="/assets/app/favicon-48.png"><link rel="shortcut icon" href="/favicon.ico"><link rel="stylesheet" href="/assets/css/plugins/all.min.css">
    <link rel="stylesheet" href="/assets/css/plugins/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include dirname(__DIR__) . '/header1.php'; ?>
<section class="u-page-hero">
  <video class="u-bgvid u-bgvid-soft" autoplay muted loop playsinline preload="none" poster="/assets/video/pattern.jpg" aria-hidden="true"><source src="/assets/video/pattern.mp4" type="video/mp4"></video>
  <div class="u-bgshade"></div>
  <div class="u-wrap" style="position:relative;z-index:2">
    <div class="u-hero-top">
      <nav class="u-crumbs notranslate"><a href="/index.php"><?= t('home') ?></a><?php if ($crumb): ?> <i class="fas fa-chevron-left"></i> <span><?= htmlspecialchars($crumb) ?></span><?php endif; ?></nav>
      <a class="u-back notranslate" href="/index.php" onclick="if(history.length>1&&document.referrer.indexOf(location.host)>-1){history.back();return false;}"><i class="fas fa-arrow-right"></i> <?= t('back') ?></a>
    </div>
    <h1><?= htmlspecialchars($title) ?></h1>
    <?php if ($sub): ?><p style="color:#bfe0e4;margin:10px 0 0;font-size:17px;max-width:46em"><?= $sub ?></p><?php endif; ?>
  </div>
</section>
<section class="u-page"><div class="u-wrap">
<?php
}

function uwciv_close()
{
    ?>
</div></section>
<?php include dirname(__DIR__) . '/footer.php'; ?>
</div>
</body>
</html>
<?php
}
