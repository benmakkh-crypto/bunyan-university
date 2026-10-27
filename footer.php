<?php require_once __DIR__ . '/inc/i18n.php'; require_once __DIR__ . '/inc/settings.php'; ?>
<footer class="u-footer notranslate">
  <div class="u-wrap">
    <div class="u-foot-grid">
      <div class="u-foot-brand">
        <a href="/index.php" class="u-foot-logo"><img src="/assets/images/logo-light.png" alt="<?= htmlspecialchars(t('uni')) ?>"></a>
        <p><?= t('f_about') ?></p>
      </div>

      <div class="u-foot-col">
        <h4><?= t('f_links') ?></h4>
        <ul>
          <li><a href="/edusys.php"><?= t('edusys') ?></a></li>
          <li><a href="/journal"><?= t('journal') ?></a></li>
          <li><a href="/librarysystem/index.php"><?= t('library') ?></a></li>
          <li><a href="/news.php"><?= t('news') ?></a></li>
          <li><a href="/teacher/index.php"><?= t('faculty') ?></a></li>
          <li><a href="/ask.php"><?= t('faq') ?></a></li>
        </ul>
      </div>

      <div class="u-foot-col">
        <h4><?= t('f_quick') ?></h4>
        <ul>
          <li><a href="/colleges.php"><?= t('colleges') ?></a></li>
          <li><a href="/education.php"><?= t('platform') ?></a></li>
          <li><a href="/onlineexam/index.php"><?= t('exam') ?></a></li>
          <li><a href="/de/des.php"><?= t('cert') ?></a></li>
          <li><a href="/grad.php"><?= t('alumni') ?></a></li>
          <li><a href="/calendar.php"><?= t('calendar_m') ?></a></li>
          <li><a href="/syslog/index.php"><?= t('login') ?></a></li>
          <li><a href="/links"><?= t('links') ?></a></li>
          <li><a href="/app">تطبيق الجوال</a></li>
        </ul>
      </div>

      <div class="u-foot-card">
        <h4><span class="u-dash"></span><?= t('f_follow') ?></h4>
        <div class="u-social">
          <?php require_once __DIR__ . '/inc/settings.php'; $__soc = uwciv_settings()['social'] ?? []; $__any = false;
          foreach (uwciv_social_defs() as $k => $d): if (empty($__soc[$k])) continue; $__any = true; ?>
            <a href="<?= htmlspecialchars($__soc[$k]) ?>" target="_blank" rel="noopener" aria-label="<?= $d[0] ?>"><i class="<?= $d[1] ?>"></i></a>
          <?php endforeach; if (!$__any): ?>
            <a href="mailto:info@biuedu.com" aria-label="Email"><i class="fas fa-envelope"></i></a>
          <?php endif; ?>
        </div>
        <hr>
        <h4><span class="u-dash"></span><?= t('f_reach') ?></h4>
        <div class="u-reach">
          <a href="/form2.php"><i class="fas fa-headset"></i><?= t('support') ?></a>
          <a href="/contact.php"><i class="fas fa-envelope"></i><?= t('contact') ?></a>
          <a href="mailto:info@biuedu.com" class="notranslate" dir="ltr"><i class="fas fa-at"></i>info@biuedu.com</a>
          <?php $__fc = uwciv_contact(); ?><a href="https://wa.me/<?= $__fc['digits'] ?>" target="_blank" rel="noopener" class="notranslate" dir="ltr"><i class="fab fa-whatsapp"></i><?= $__fc['pretty'] ?></a>
          <a href="tel:+<?= $__fc['digits'] ?>" class="notranslate" dir="ltr"><i class="fas fa-phone-alt"></i><?= $__fc['pretty'] ?></a>
        </div>
      </div>

      <a class="u-foot-map" href="<?= htmlspecialchars($__fc['location']) ?>" target="_blank" rel="noopener" aria-label="موقع الجامعة على خرائط Google">
        <span class="u-foot-map-h"><span class="u-dash"></span>موقع الجامعة</span>
        <span class="u-foot-map-box">
          <iframe src="<?= htmlspecialchars($__fc['embed']) ?>" loading="lazy" tabindex="-1" aria-hidden="true" title="خريطة موقع الجامعة"></iframe>
          <span class="u-foot-map-pin"><i class="fas fa-map-marker-alt"></i><span><b><?= htmlspecialchars($__fc['address']) ?></b><small>اضغط لفتح الموقع في خرائط Google</small></span><i class="fas fa-external-link-alt"></i></span>
        </span>
      </a>
    </div>
  </div>

  <div class="u-foot-bottom">
    <div class="u-wrap u-foot-bottom-row">
      <img src="/assets/images/na-light.png" alt="" class="u-foot-cal">
      <nav>
        <a href="/info.php?p=privacy"><?= t('privacy') ?></a>
        <a href="/info.php?p=terms"><?= t('terms') ?></a>
        <a href="/info.php?p=credits"><?= t('credits') ?></a>
      </nav>
      <p>© <?= date('Y') ?> <?= t('rights') ?></p>
    </div>
  </div>
</footer>
