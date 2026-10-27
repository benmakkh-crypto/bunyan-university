<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php'; list($dbHost, $dbUser, $dbPass, $dbName) = db_cfg('logweb');

$conn = db_mysqli_raw($dbHost, $dbUser, $dbPass, $dbName);
// إن تعذّر الاتصال تُعرض الصفحة بدون الأقسام المعتمدة على قاعدة البيانات
?>



<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>جامعة بنيان العالمية | Bunyan International University — تعليم جامعي عن بُعد</title>
    <link rel="canonical" href="https://your-domain.com/">
    <link rel="icon" type="image/png" sizes="48x48" href="/assets/app/favicon-48.png"><link rel="shortcut icon" href="/favicon.ico"><meta property="og:type" content="website">
    <meta property="og:site_name" content="جامعة بنيان العالمية">
    <meta property="og:title" content="جامعة بنيان العالمية | Bunyan International University">
    <meta property="og:description" content="جامعة بنيان العالمية — جامعة أهلية مرخّصة في إندونيسيا منذ 2019م: كلية الشريعة، وكلية الدعوة، وكلية التربية للبنات، ومعهد تعليم اللغة العربية لغير الناطقين بها.">
    <meta property="og:url" content="https://your-domain.com/">
    <meta property="og:image" content="https://your-domain.com/assets/app/icon-512.png">
    <?php require_once __DIR__ . '/inc/settings.php'; $__sc = uwciv_contact(); ?>
    <script type="application/ld+json"><?= json_encode(['@context' => 'https://schema.org', '@type' => 'CollegeOrUniversity', 'name' => 'جامعة بنيان العالمية', 'alternateName' => 'Bunyan International University', 'url' => 'https://your-domain.com/', 'logo' => 'https://your-domain.com/assets/app/icon-512.png', 'email' => 'info@biuedu.com', 'foundingDate' => '2019', 'telephone' => $__sc['phone'], 'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Sajira', 'addressLocality' => 'Lebak', 'addressRegion' => 'Banten', 'addressCountry' => 'ID'], 'hasMap' => $__sc['location']] + ($__sc['lat'] !== null ? ['geo' => ['@type' => 'GeoCoordinates', 'latitude' => $__sc['lat'], 'longitude' => $__sc['lng']]] : []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
    <meta name="robots" content="index, follow" />
    <meta name="description" content="جامعة بنيان العالمية (Bunyan International University) — جامعة أهلية في بانتن بإندونيسيا تمنح الدرجات العلمية في الشريعة والأحوال الشخصية والدعوة واللغة العربية: دبلوم، بكالوريوس، ماجستير، دكتوراه. سجّل الآن.">
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#0b3119">
    <link rel="apple-touch-icon" href="/assets/app/apple-touch-icon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Favicon -->
  
    <!-- CSS
	============================================ -->

    <!-- Icon Font CSS -->
    <link rel="stylesheet" href="assets/css/plugins/all.min.css">
    <link rel="stylesheet" href="assets/css/plugins/flaticon.css">

    <!-- Plugins CSS -->
    <link rel="stylesheet" href="assets/css/plugins/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/plugins/swiper-bundle.min.css">
    <link rel="stylesheet" href="assets/css/plugins/aos.css">
    <link rel="stylesheet" href="assets/css/plugins/nice-select.css">
    <link rel="stylesheet" href="assets/css/plugins/jquery.powertip.min.css">
    <link rel="stylesheet" href="assets/css/plugins/magnific-popup.css">

    <!-- Main Style CSS -->
    <link rel="stylesheet" href="assets/css/style.css">


    <!--====== Use the minified version files listed below for better performance and remove the files listed above ======-->
    <!-- 
    <link rel="stylesheet" href="assets/css/style.min.css"> -->

</head>

<body>
 <?php include "header1.php";?>

        <!-- Offcanvas Start -->
      


        <!-- Hero Start -->
<section class="u-hero notranslate">
  <video class="u-bgvid" autoplay muted loop playsinline preload="metadata" poster="/assets/video/hero.jpg" aria-hidden="true"><source src="/assets/video/hero.mp4" type="video/mp4"></video>
  <div class="u-bgshade"></div>
  <div class="u-wrap u-hero-grid">
    <a class="u-hero-logo" href="/about.php" aria-label="<?= htmlspecialchars(t('uni')) ?>">
      <span class="u-hl-mark"><img src="/assets/images/logo-light.png" alt=""></span>
      <span class="u-hl-word"><img src="/assets/images/na-light.png" alt="<?= htmlspecialchars(t('uni')) ?>"><small>Bunyan International University</small></span>
    </a>
    <div class="u-hero-text">
      <span class="u-kicker"><?= t('h_kicker') ?></span>
      <h1><?= t('h_title') ?></h1>
      <p class="u-lead"><?= t('h_text') ?></p>
      <div class="u-cta">
        <a class="u-btn" href="/elctro.php"><?= t('h_cta') ?> <i class="fas fa-chevron-left"></i></a>
        <a class="u-btn u-btn-ghost" href="/colleges.php"><i class="fas fa-arrow-down"></i> <?= t('h_cta2') ?></a>
      </div>
      <p class="u-note"><i class="fas fa-shield-alt"></i><?= t('h_note') ?></p>
    </div>

    <div class="u-portal">
      <div class="u-portal-media" style="background-image:url('/assets/images/bd-arches.jpg')">
        <div class="u-portal-inset" style="background-image:url('/assets/images/bd-student.jpg')"></div>
      </div>
      <div class="u-portal-head">
        <div><h3><?= t('portal') ?></h3><small><?= t('uni') ?></small></div>
        <div class="u-dots"><i></i><i></i><i></i></div>
      </div>
      <a class="u-svc" href="/student/index.php"><span class="n">01</span><span class="ic"><i class="fas fa-user-graduate"></i></span><span><b><?= t('s_students') ?></b><small><?= t('s_students_d') ?></small></span><i class="fas fa-chevron-left go"></i></a>
      <a class="u-svc" href="/teacher/index.php"><span class="n">02</span><span class="ic"><i class="fas fa-chalkboard-teacher"></i></span><span><b><?= t('s_faculty') ?></b><small><?= t('s_faculty_d') ?></small></span><i class="fas fa-chevron-left go"></i></a>
      <a class="u-svc" href="/syslog/index.php"><span class="n">03</span><span class="ic"><i class="fas fa-id-badge"></i></span><span><b><?= t('s_staff') ?></b><small><?= t('s_staff_d') ?></small></span><i class="fas fa-chevron-left go"></i></a>
      <div class="u-portal-foot"><span><?= t('p_paths') ?></span><span><?= t('p_unified') ?></span><span><?= t('p_roles') ?></span></div>
    </div>
  </div>
</section>
        <!-- Hero End -->

  <!-- أرقام الجامعة -->
<section class="h-stats">
  <div class="u-wrap h-stats-row">
    <div class="h-stat"><i class="fas fa-landmark"></i><b data-to="2019">0</b><span>سنة التأسيس</span></div>
    <div class="h-stat"><i class="fas fa-university"></i><b data-to="3">0</b><span>كليات ومعهد للعربية</span></div>
    <div class="h-stat"><i class="fas fa-handshake"></i><b data-to="50" data-plus="1">0</b><span>اتفاقية دولية</span></div>
    <div class="h-stat"><i class="fas fa-book"></i><b data-to="10000" data-plus="1">0</b><span>كتاب في المكتبة</span></div>
    <div class="h-stat"><i class="fas fa-chalkboard-teacher"></i><b data-to="25" data-plus="1">0</b><span>عضو هيئة تدريس</span></div>
  </div>
</section>

<!-- قصة الجامعة -->
<section class="h-story">
  <div class="u-wrap h-story-grid">
    <div class="h-story-media">
      <div class="h-frame h-frame-back"><img src="/assets/images/biu/front-about.jpg" onerror="this.onerror=null;this.src='/assets/images/bd-mosque.jpg'" alt="مبنى جامعة بنيان العالمية"></div>
      <div class="h-frame h-frame-front"><img src="/assets/images/biu/front-library.jpg" onerror="this.onerror=null;this.src='/assets/images/bd-graduates.jpg'" alt="مكتبة جامعة بنيان العالمية"></div>
      <div class="h-badge"><i class="fas fa-map-marker-alt"></i><div><b>بانتن، إندونيسيا</b><small>مقر على 150 ألف م²</small></div></div>
      <div class="h-badge h-badge-2"><i class="fas fa-mosque"></i><div><b>منهج الوسطية</b><small>والاعتدال</small></div></div>
    </div>
    <div class="h-story-text">
      <span class="u-kicker h-kicker">عن الجامعة</span>
      <h2 class="h-title">صرحٌ علمي يجمع <em>أصالة العلم</em> وحداثة الوسيلة</h2>
      <nav class="h-tabs" role="tablist">
        <button class="is-on" data-t="0"><i class="fas fa-seedling"></i> نشأة الجامعة</button>
        <button data-t="1"><i class="fas fa-eye"></i> الرؤية والرسالة</button>
        <button data-t="2"><i class="fas fa-bullseye"></i> أهداف الجامعة</button>
      </nav>
      <div class="h-pane is-on" data-t="0">
        <p class="h-lead">أُنشئت جامعة بنيان العالمية عام 1440هـ الموافق 2019م، وهي جامعة أهلية مرخّصة في جمهورية إندونيسيا.</p>
        <p>تمنح الجامعة الدرجات العلمية في تخصصات الشريعة والأحوال الشخصية والدعوة واللغة العربية والعلوم الإنسانية والتطبيقية، وتخدم الطلاب بعد المرحلة الثانوية وفق معايير الاعتماد الأكاديمي العالمي، في بيئة تعليمية تحفيزية تناسب المتعلمين من سائر أنحاء العالم.</p>
        <p>وتحرص الجامعة على تعليم اللغة العربية للناطقين بغيرها، لسد حاجتهم ونفع مجتمعاتهم.</p>
      </div>
      <div class="h-pane" data-t="1">
        <p><b>الرؤية:</b> صناعة طلبة علم متمكنين، وتأهيل دعاة متميزين، وفق معايير الاعتماد الأكاديمي العالمي، وتأهيل مختصين يمتلكون الكفاءة العالية في العلوم الشرعية والمهارات الدعوية، وإعداد منهجية علمية حديثة تجمع بين الأصالة والمعاصرة.</p>
        <p><b>الرسالة:</b> الريادة في إيصال العلوم الشرعية والمهارية بأيسر الوسائل والطرق، وفق معايير الجودة العالمية في التطوير العلمي والتبادل المعرفي، ومن خلال بيئة تعليمية تحفيزية تناسب المتعلمين من سائر أنحاء العالم.</p>
      </div>
      <div class="h-pane" data-t="2">
        <ul class="h-goals">
          <li><i class="fas fa-book-open"></i>تدريس المعارف وفق مناهج علمية دقيقة، مستفيدة من مستجدات العلم المعاصر.</li>
          <li><i class="fas fa-hands-helping"></i>تنمية الإحساس بالمسؤولية المجتمعية لدى الخريجين والارتقاء بهم.</li>
          <li><i class="fas fa-user-graduate"></i>رفد المجتمع بالخبرات العلمية والتدريبية اللازمة للقيام بالمهام والوظائف المطلوبة.</li>
          <li><i class="fas fa-microscope"></i>تقديم الدراسات والبحوث العلمية التي تلامس حاجات المجتمع وتسهم في حل مشكلاته.</li>
          <li><i class="fas fa-handshake"></i>تعزيز ربط التعليم العالي بالمجتمع بالمشاركة في الأعمال المجتمعية والعلمية والثقافية.</li>
        </ul>
      </div>
      <a class="u-btn h-more" href="/about.php">اكتشف الجامعة <i class="fas fa-arrow-left"></i></a>
    </div>
  </div>
</section>

<!-- المراحل الدراسية -->
<section class="h-stages">
  <div class="u-wrap">
    <div class="h-head"><span class="u-kicker h-kicker">ابدأ رحلتك</span><h2 class="h-title">أربع مراحل… <em>طريق واحد</em> نحو التميز</h2></div>
    <div class="h-stage-row">
      <a class="h-st" href="/depl.php" style="--sc:#0e9aa7;--sc2:#08707a"><span class="n">01</span><i class="fas fa-seedling"></i><b>الدبلوم</b><small>برامج تأسيسية قصيرة</small><em>300 $</em></a>
      <a class="h-st" href="/depm.php" style="--sc:#12935a;--sc2:#0b6a40"><span class="n">02</span><i class="fas fa-book-open"></i><b>البكالوريوس</b><small>الشريعة والدعوة والتربية</small><em>500 $</em></a>
      <a class="h-st" href="/depmst.php" style="--sc:#2c5fd0;--sc2:#1c3f93"><span class="n">03</span><i class="fas fa-feather-alt"></i><b>الماجستير</b><small>مقررات ورسالة علمية</small><em>700 $</em></a>
      <a class="h-st" href="/depdec.php" style="--sc:#9b1f45;--sc2:#6c1430"><span class="n">04</span><i class="fas fa-crown"></i><b>الدكتوراه</b><small>بحث متقدم بإشراف أكاديمي</small><em>1000 $</em></a>
    </div>
  </div>
</section>

<!-- أخبار الجامعة -->
<section class="h-news">
  <video class="u-bgvid u-bgvid-soft" autoplay muted loop playsinline preload="none" poster="/assets/video/pattern.jpg" aria-hidden="true"><source src="/assets/video/pattern.mp4" type="video/mp4"></video>
  <div class="u-bgshade"></div>
  <div class="u-wrap" style="position:relative;z-index:2">
    <div class="h-head h-head-light"><span class="u-kicker h-kicker">المركز الإعلامي</span><h2 class="h-title">أخبار <em>الجامعة</em></h2>
      <a class="h-all" href="/news.php">كل الأخبار <i class="fas fa-arrow-left"></i></a></div>
    <div class="h-news-grid">
<?php
        $result = $conn ? mysqli_query($conn, "SELECT id, date, title, summary, image FROM posts ORDER BY id DESC LIMIT 6") : false;
        $i = 0;
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) { $i++;
                $img = htmlspecialchars('/blog/img/' . $row['image']);
                $date = htmlspecialchars($row['date']);
                echo '<a class="h-card' . ($i === 1 ? ' h-card-lead' : '') . '" href="/blog/view.php?id=' . (int)$row['id'] . '">'
                   . '<span class="h-card-img" style="background-image:url(\'' . $img . '\')"></span>'
                   . '<span class="h-card-body">' . ($date ? '<span class="h-date"><i class="far fa-calendar"></i> ' . $date . '</span>' : '')
                   . '<b>' . htmlspecialchars($row['title']) . '</b>'
                   . '<small>' . htmlspecialchars(mb_strimwidth(strip_tags($row['summary']), 0, 150, '…')) . '</small>'
                   . '<span class="h-read">اقرأ الخبر <i class="fas fa-arrow-left"></i></span></span></a>';
            }
        }
        if (!$i) echo '<p class="h-empty">لا توجد أخبار منشورة حالياً.</p>';
?>
    </div>
  </div>
</section>

<script>
(function(){
  // عدّاد الأرقام
  var nums = document.querySelectorAll('.h-stat b');
  function run(el){ var to = +el.dataset.to, t0 = null; function f(ts){ if (!t0) t0 = ts; var k = Math.min(1, (ts - t0) / 1600); el.textContent = Math.round(to * (1 - Math.pow(1 - k, 3))) + (k === 1 && el.dataset.plus ? '+' : ''); if (k < 1) requestAnimationFrame(f); } requestAnimationFrame(f); }
  if ('IntersectionObserver' in window) { var io = new IntersectionObserver(function(es){ es.forEach(function(e){ if (e.isIntersecting) { run(e.target); io.unobserve(e.target); } }); }); nums.forEach(function(n){ io.observe(n); }); } else nums.forEach(run);
  // تبويبات قصة الجامعة
  var tabs = document.querySelectorAll('.h-tabs button'), panes = document.querySelectorAll('.h-pane');
  tabs.forEach(function(b){ b.addEventListener('click', function(){ tabs.forEach(function(x){ x.classList.toggle('is-on', x === b); }); panes.forEach(function(p){ p.classList.toggle('is-on', p.dataset.t === b.dataset.t); }); }); });
  // إيقاف الفيديو لمن يفضّل تقليل الحركة
  if (matchMedia('(prefers-reduced-motion: reduce)').matches) document.querySelectorAll('.u-bgvid').forEach(function(v){ v.removeAttribute('autoplay'); v.pause(); });
})();
</script>

    <?php include "footer.php";
?>



        <!-- back to top start -->
        <div class="progress-wrap">
            <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
                <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
            </svg>
        </div>
        <!-- back to top end -->


        <!-- course hover Start -->
      

    <!-- JS
    ============================================ -->
    <script src="assets/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="assets/js/vendor/modernizr-3.11.2.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="assets/js/plugins/popper.min.js"></script>
    <script src="assets/js/plugins/bootstrap.min.js"></script>

    <!-- Plugins JS -->
    <script src="assets/js/plugins/swiper-bundle.min.js"></script>
    <script src="assets/js/plugins/aos.js"></script>
    <script src="assets/js/plugins/waypoints.min.js"></script>
    <script src="assets/js/plugins/jquery.counterup.min.js"></script>
    <script src="assets/js/plugins/jquery.nice-select.min.js"></script>
    <script src="assets/js/plugins/back-to-top.js"></script>
    <script src="assets/js/plugins/jquery.powertip.min.js"></script>
    <script src="assets/js/plugins/jquery.magnific-popup.min.js"></script>

    <!--====== Use the minified version files listed below for better performance and remove the files listed above ======-->


    <!-- Main JS -->
    <script src="assets/js/main.js"></script>

</body>

</html>