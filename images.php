




<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>معرض الصور | جامعة بنيان العالمية</title>
    <meta name="robots" content="index, follow" />
    <meta name="description" content="جامعة بنيان العالمية — تعليم عن بُعد في العلوم الشرعية والإنسانية: دبلوم، بكالوريوس، ماجستير، دكتوراه.">
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

<?php include "header1.php";

?>
    
 

        <!-- Page Banner Start -->
        <div class="upstudy-hero-section-06">
        <div class="container my-5">

  <div class="row">
    <div id="homeSlider" class="carousel slide carousel-lazy image-slider" data-bs-ride="carousel">
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#homeSlider" data-bs-slide-to="0" class="active" aria-current="true"
          aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#homeSlider" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#homeSlider" data-bs-slide-to="2" aria-label="Slide 3"></button>
      </div>
      <div class="carousel-inner">
        <div class="carousel-item active" data-bs-interval="10000">
          <img class="lazyload d-block w-100" src="<?= is_file(__DIR__ . '/assets/images/biu/slider-1.jpg') ? '/assets/images/biu/slider-1.jpg' : 'assets/images/bd-graduates.jpg' ?>" alt="..." />
        </div>
        <div class="carousel-item" data-bs-interval="2000">
          <img class="d-block w-100"
            
            src="<?= is_file(__DIR__ . '/assets/images/biu/slider-2.jpg') ? '/assets/images/biu/slider-2.jpg' : 'assets/images/c2.jpg' ?>" alt="..." />
        </div>
        <div class="carousel-item">
          <img class="d-block w-100"
           src="<?= is_file(__DIR__ . '/assets/images/biu/slider-3.jpg') ? '/assets/images/biu/slider-3.jpg' : 'assets/images/m7.jpg' ?>" alt="..." />
        </div>
        
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#homeSlider" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">السابق</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#homeSlider" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">التالي</span>
      </button>
    </div>
  </div>
</div>
</div>
</div>
<!--end slider--> 

       





<div class="section upstudy-offer-section section-padding-03">
    <div class="container">
        <!-- معرض صور جامعة بنيان — الصور في assets/images/biu/gallery وتُجلب من لوحة التحكم ← استيراد محتوى بنيان -->
        <?php $__g = glob(__DIR__ . '/assets/images/biu/gallery/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG}', GLOB_BRACE) ?: []; sort($__g); ?>
        <h2 style="font-weight:900;text-align:center;margin:0 0 24px">معرض صور جامعة بنيان العالمية</h2>
        <?php if ($__g): ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px">
          <?php foreach ($__g as $__f): $__u = '/assets/images/biu/gallery/' . rawurlencode(basename($__f)); ?>
          <a href="<?= $__u ?>" class="popup-img" target="_blank" style="display:block;border-radius:16px;overflow:hidden;aspect-ratio:4/3;background:#e9f2ec"><img src="<?= $__u ?>" alt="من صور جامعة بنيان العالمية" loading="lazy" style="width:100%;height:100%;object-fit:cover;transition:.3s" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform=''"></a>
          <?php endforeach; ?>
        </div>
        <?php else: ?><p style="text-align:center;color:#6b7c72">تُضاف الصور قريبًا.</p><?php endif; ?>
    </div>
    </div>




       
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
    
        <!-- course hover End -->

 

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