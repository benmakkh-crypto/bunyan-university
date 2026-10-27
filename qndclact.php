<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php'; list($dbHost, $dbUser, $dbPass, $dbName) = db_cfg('logweb');

$conn = db_mysqli_raw($dbHost, $dbUser, $dbPass, $dbName);
if (!$conn) {
    die("Something went wrong. Database is not connected;");
}
?>



<!doctype html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>محاضرات كلية القران الكريم-الدكتوراه    </title>
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
 <?php include "header1.php";?>

        <!-- Offcanvas Start -->
      


        <!-- Hero Start -->
      













        <!-- Offer Start -->
    
       

        <!-- Testimonial Start -->
      
               



        <div class="section upstudy-offer-section section-padding-03">
    <div class="container">
        <!-- Offer Wrap Start -->
        
    </div>
    </div>





 <!-- Testimonial Start  -->
 <div style="background-color:#568203;margin-top:8rem;"class="section upstudy-testimonial-section-03 upstudy-testimonial section-padding-0">
  
    <div class="container1">
        <!-- Testimonial Wrap Start  -->
        

        <div class="testimonial-wrap-02">
            <div class="section-title text-center">
            <h5 class="title"style="color:#fff;font-size:26px;"> محاضرات كلية القران الكريم-الدكتوراه  </h5>
            </div>
         
        
            <div class="testimonial-content-wrap-02">
                <div class="swiper-container testimonial-02-active">
                    <div class="swiper-wrapper">
     
     
                    <?php
           
           $sql = "SELECT * FROM qurandec" ;
           $result = mysqli_query($conn, $sql);
           if (mysqli_num_rows($result) > 0) {
               // output data of each row
               while($row = mysqli_fetch_assoc($result)) {
                 echo '
         <div class="swiper-slide">
                               <!--  Single Testimonial Start  -->
                               <div class="single-testimonial-02">
   
                                   <div class="testimonial-thumb">
                                       <img width="170px" height="160px" src="./edu/img/'.$row['image'].'" alt="">
                                   </div>
                                   <div class="testimonial-content">
                              
                                         <p style="font-size:20px;color:#568203;">'.$row["teacher"].'</P>
                                      <p style="font-size:16px;color:#568203">'.$row["date"].'</P>';
                                 echo" <h5 style=font-size:16px;color:#000;><a href='" . htmlspecialchars($row["link"]) . "' target='_blank'>" . htmlspecialchars($row["title"]) . "</a></h5><br>
   
                                   </div>
                               </div>
                               <!--  Single Testimonial End  -->
                           </div>
   
                           
        
        ";
        
               }
             }
           ?>
        
     
                       
        
        
                         
                    </div>

                    <!-- Add Pagination -->
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </div>
    </div>
</div>




        
        <!-- Testimonial Wrap End  -->
    
<!-- Testimonial End  -->




<div class="section upstudy-offer-section section-padding-03">
    <div class="container">
        <!-- Offer Wrap Start -->
        
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