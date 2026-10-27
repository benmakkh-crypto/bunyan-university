<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>البحث في الموقع </title>
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
<style>

/* ===== Google Font Import - Poppins ===== */


.srchweb {
    width: 500px;
    margin: 140px auto;
}




    
</style>

    <!--====== Use the minified version files listed below for better performance and remove the files listed above ======-->
    <!-- 
    <link rel="stylesheet" href="assets/css/style.min.css"> -->

</head>

<body>

<?php include "header1.php";
?>






        <div class="section upstudy-about-section-06 section-padding"style="background:color-mix(in srgb,rgb(233, 255, 210) 31%, #f9f9f9 69%);">
            <div class="container"dir="rtl">
                <div class="about-wrap">
             
                <div class="srchweb">

                <form class="search-form" action="search.php" method="GET">
    <input type="text" name="search" placeholder="بحث...">
    <button type="submit"style="background-color:#568203">بحث</button>
</form>
      </div>

     
    </div>

    </div>




    





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


        <script>
       
       const optionMenu = document.querySelector(".select-menu"),
  selectBtn = optionMenu.querySelector(".select-btn"),
  options = optionMenu.querySelectorAll(".option"),
  sBtn_text = optionMenu.querySelector(".sBtn-text");

selectBtn.addEventListener("click", () =>
  optionMenu.classList.toggle("active")
);

options.forEach((option) => {
  option.addEventListener("click", () => {
    let selectedOption = option.querySelector(".option-text").innerText;
    sBtn_text.innerText = selectedOption;

    optionMenu.classList.remove("active");
  });
});

</script>

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