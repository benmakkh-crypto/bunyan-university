<?php
require_once __DIR__ . '/inc/shell.php';
require_once __DIR__ . '/inc/links.php';

$groups = [];
foreach ($UWCIV_LINKS as $l) $groups[$l[4]][] = $l;

uwciv_open(t('links'), null, 'روابط قصيرة سهلة الحفظ والمشاركة لكل أقسام الجامعة وخدماتها.');
foreach ($groups as $name => $items): ?>
<div class="u-tiles-group">
  <h2><?= htmlspecialchars($name) ?></h2>
  <div class="u-links">
    <?php foreach ($items as $l): $url = 'your-domain.com/' . $l[0]; ?>
    <a class="u-link" href="/<?= $l[0] ?>">
      <i class="fas <?= $l[3] ?>"></i>
      <span><b><?= htmlspecialchars($l[2]) ?></b><code class="notranslate"><?= $url ?></code></span>
      <button type="button" title="نسخ" data-copy="https://<?= $url ?>"><i class="far fa-copy"></i></button>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>
<script>
document.querySelectorAll('[data-copy]').forEach(function(b){
  b.addEventListener('click', function(e){
    e.preventDefault(); e.stopPropagation();
    var t = b.getAttribute('data-copy');
    (navigator.clipboard ? navigator.clipboard.writeText(t) : Promise.reject()).then(function(){
      b.innerHTML = '<i class="fas fa-check"></i>'; setTimeout(function(){ b.innerHTML = '<i class="far fa-copy"></i>'; }, 1500);
    }).catch(function(){});
  });
});
</script>
<?php uwciv_close();
