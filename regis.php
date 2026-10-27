<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>تسجيل الدخول </title>
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


.select-menu{
    width: 380px;
    margin: 140px auto;
}
.select-menu .select-btn{
    display: flex;
    height: 55px;
    background: #fff;
    padding: 20px;
    font-size: 18px;
    font-weight: 400;
    border-radius: 8px;
    align-items: center;
    cursor: pointer;
    justify-content: space-between;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
}
.select-btn i{
    font-size: 25px;
    transition: 0.3s;
}
.select-menu.active .select-btn i{
    transform: rotate(-180deg);
}
.select-menu .options{
    position: relative;
    padding: 20px;
    margin-top: 10px;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 0 3px rgba(0,0,0,0.1);
    display: none;
}
.select-menu.active .options{
    display: block;
}
.options .option{
    display: flex;
    height: 55px;
    cursor: pointer;
    padding: 0 16px;
    border-radius: 8px;
    align-items: center;
    background: #fff;
}
.options .option:hover{
    background: #F2F2F2;
}
.option i{
    font-size: 25px;
    margin-right: 12px;
}
.option .option-text{
    font-size: 18px;
    color: #333;
}






    
</style>

    <!--====== Use the minified version files listed below for better performance and remove the files listed above ======-->
    <!-- 
    <link rel="stylesheet" href="assets/css/style.min.css"> -->

</head>

<body>

<?php include "header1.php";
?>






        <div class="section upstudy-about-section-06 section-padding"style="background:color-mix(in srgb, #b9dcff 31%, #f9f9f9 69%);">
            <div class="container"dir="rtl">
                <div class="about-wrap">
             
                <div class="select-menu">
      <div class="select-btn">
        <span class="sBtn-text">تسجيل الدخول</span>
        <i class="bx bx-chevron-down"></i>
      </div>

      <ul class="options">
        <li class="option">
          <span class="option-text">الموظفين</span>
          <i class="fas fa-users" style="color: #171515"></i>
        </li>
        <li class="option">
          <span class="option-text">الاكادمين والمدربين</span>
          <i class="fas fa-user-tie" style="color:#171515"></i>
        </li>
        <li class="option">
          <span class="option-text">الطلاب</span>
          <i class="fas fa-book-reader" style="color: #0e76a8"></i>
        </li>
        <li class="option">
          <span class="option-text">تقديم وظيفة</span>
          <i class="fa fa-address-card" style="color: #4267b2"></i>
        </li>
      
      </ul>
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