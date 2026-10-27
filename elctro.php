<?php
require_once __DIR__ . '/inc/shell.php';

/* ✏️ خدمات البوابة: كل صف = مجموعة [عنوان, وصف, لون, أيقونة, [خدمات]] — الخدمة: [عنوان, وصف, رابط, أيقونة] */
$ROWS = [
 ['خدمات الطلاب', 'من التقديم حتى التخرج', '#12935a', 'fa-user-graduate', [
   ['طلب التحاق', 'سجّل في أي مرحلة دراسية', '/student/index.php', 'fa-user-plus'],
   ['تسجيل الدخول', 'حسابك في الجامعة', '/syslog/index.php', 'fa-sign-in-alt'],
   ['المحاضرات', 'كل المراحل في مكان واحد', '/depmlact.php', 'fa-play-circle'],
   ['الامتحان الإلكتروني', 'الاختبارات والنتائج', '/onlineexam/index.php', 'fa-file-signature'],
   ['الشهادة الإلكترونية', 'إصدار وتحقق فوري', '/de/des.php', 'fa-certificate'],
   ['الدورات التدريبية', 'برامج قصيرة معتمدة', '/courses/courses.php', 'fa-chalkboard'],
 ]],
 ['المكتبة والبحث العلمي', 'مصادر المعرفة بين يديك', '#2c5fd0', 'fa-book-reader', [
   ['المكتبة الرقمية', 'كتب ومراجع لكل التخصصات', '/librarysystem/index.php', 'fa-book'],
   ['رسائل الخريجين', 'بحوث ورسائل منشورة', '/pdf_grad/index.php', 'fa-scroll'],
   ['معهد البحوث', 'بحث واستشارات علمية', '/research.php', 'fa-microscope'],
   ['مجلة الجامعة', 'نشر علمي محكّم', '/info.php?p=journal', 'fa-newspaper'],
 ]],
 ['التواصل والدعم', 'نحن هنا لمساعدتك', '#9b1f45', 'fa-hands-helping', [
   ['طلب أو شكوى', 'مباشرة إلى رئاسة الجامعة', '/form1.php', 'fa-envelope-open-text'],
   ['الدعم الفني', 'حل مشكلات الدخول والمنصة', '/form2.php', 'fa-headset'],
   ['الأسئلة الشائعة', 'إجابات سريعة وواضحة', '/ask.php', 'fa-question-circle'],
   ['التقويم الدراسي', 'المواعيد والإجازات', '/calendar.php', 'fa-calendar-alt'],
   ['منصة الخريجين', 'شبكة وفرص الخريجين', '/grad.php', 'fa-user-graduate'],
 ]],
];
$total = 0; foreach ($ROWS as $r) $total += count($r[4]);

uwciv_open(t('portal'), null, 'كل خدمات الجامعة في ' . $total . ' بوابة — ابحث، اختر، وانطلق.');
?>
<div class="p-tools">
  <div class="p-search">
    <i class="fas fa-search"></i>
    <input type="search" id="p-q" placeholder="ابحث عن خدمة... مثلاً: الامتحان، المكتبة، الشهادة" autocomplete="off">
    <kbd>/</kbd>
  </div>
  <nav class="p-chips" aria-label="تصفية">
    <button class="is-on" data-f="all"><i class="fas fa-th-large"></i> الكل <b><?= $total ?></b></button>
    <?php foreach ($ROWS as $i => $r): ?>
      <button data-f="<?= $i ?>" style="--rc:<?= $r[2] ?>"><i class="fas <?= $r[3] ?>"></i> <?= $r[0] ?> <b><?= count($r[4]) ?></b></button>
    <?php endforeach; ?>
  </nav>
</div>

<?php foreach ($ROWS as $i => $r): ?>
<section class="p-row" data-row="<?= $i ?>" style="--rc:<?= $r[2] ?>">
  <header class="p-row-head">
    <span class="p-num"><?= sprintf('%02d', $i + 1) ?></span>
    <span class="p-row-ic"><i class="fas <?= $r[3] ?>"></i></span>
    <div><h2><?= $r[0] ?></h2><p><?= $r[1] ?></p></div>
    <span class="p-count"><?= count($r[4]) ?> خدمات</span>
  </header>
  <div class="p-track" style="--n:<?= count($r[4]) ?>">
    <?php foreach ($r[4] as $j => $s): ?>
    <a class="p-card" href="<?= $s[2] ?>" style="--d:<?= $j * 60 ?>ms">
      <span class="p-ic"><i class="fas <?= $s[3] ?>"></i></span>
      <b><?= $s[0] ?></b>
      <small><?= $s[1] ?></small>
      <span class="p-go"><i class="fas fa-arrow-left"></i></span>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endforeach; ?>

<div class="p-empty" id="p-empty"><i class="far fa-compass"></i> لم نجد خدمة بهذا الاسم. <a href="/ask.php">تصفّح الأسئلة الشائعة</a> أو <a href="/form2.php">اسأل الدعم الفني</a>.</div>

<aside class="p-help">
  <div><b>تحتاج مساعدة؟</b><span>فريق الدعم الفني يرد على استفساراتك في أقرب وقت.</span></div>
  <a class="u-btn" href="/form2.php"><i class="fas fa-headset"></i> تواصل مع الدعم</a>
</aside>

<script>
(function(){
  var q = document.getElementById('p-q'), chips = document.querySelectorAll('.p-chips button'), rows = document.querySelectorAll('.p-row'), f = 'all';
  function apply(){
    var v = q.value.trim(), any = false;
    rows.forEach(function(row){
      var okRow = f === 'all' || row.dataset.row === f, shown = 0;
      row.querySelectorAll('.p-card').forEach(function(c){
        var hit = okRow && (!v || c.textContent.indexOf(v) > -1);
        c.classList.toggle('is-hidden', !hit); if (hit) shown++;
      });
      row.classList.toggle('is-hidden', !shown); if (shown) any = true;
    });
    document.getElementById('p-empty').style.display = any ? 'none' : 'block';
  }
  q.addEventListener('input', apply);
  chips.forEach(function(b){ b.addEventListener('click', function(){
    chips.forEach(function(x){ x.classList.remove('is-on'); }); b.classList.add('is-on'); f = b.dataset.f; apply();
    if (f !== 'all') { var r = document.querySelector('.p-row[data-row="' + f + '"]'); r && window.scrollTo({top: r.getBoundingClientRect().top + scrollY - 150, behavior: 'smooth'}); }
  }); });
  document.addEventListener('keydown', function(e){ if (e.key === '/' && document.activeElement !== q) { e.preventDefault(); q.focus(); } });
  // ظهور تدريجي للبطاقات عند التمرير
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function(es){ es.forEach(function(en){ if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); } }); }, {threshold: .15});
    rows.forEach(function(r){ io.observe(r); });
  } else rows.forEach(function(r){ r.classList.add('is-in'); });
})();
</script>
<?php uwciv_close();
