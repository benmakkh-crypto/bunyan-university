<!doctype html>
<html lang="ar"dir="rtl">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>  البحث</title>
    <meta name="robots" content="index, follow" />
    <meta name="description" content="جامعة بنيان العالمية — تعليم عن بُعد في العلوم الشرعية والإنسانية: دبلوم، بكالوريوس، ماجستير، دكتوراه.">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

   

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


</head>



<body>

  
    
<?php include "header1.php";
?>
        

      <!-- Call To Action Start -->
      <div style="padding:12rem;" class="upstudy-hero-section-06">

        
      <div class="container">
<div class="cta-wrap">
        <div class="row align-items-center">
            
        <?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php'; list($dbHost, $dbUser, $dbPass, $dbName) = db_cfg('logweb');

// Connect to MySQL database
$conn = db_mysqli_raw($dbHost, $dbUser, $dbPass, $dbName);

// Check if connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


// Check if form was submitted with a search query
if (isset($_GET['search'])) {
    // Sanitize user input to prevent SQL injection
    $search = mysqli_real_escape_string($conn, $_GET['search']);

    // Construct SQL query to search for records containing search term
    $sql = "SELECT * FROM search 
            WHERE name LIKE '%$search%' OR Hyperlink LIKE '%$search%'";

    // Execute SQL query
    $result = mysqli_query($conn, $sql);

    // Check if any results were returned
    if (mysqli_num_rows($result) > 0) {
        echo "<h3>نتيجة البحث:</h3>";
        // Loop through results and display them
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<p>";
            echo "<strong>الاسم:</strong> " . htmlspecialchars($row['name']) . "<br>";
            echo "<strong>الرابط:</strong> <a href='" . htmlspecialchars($row['Hyperlink']) . "' target='_blank'>" . htmlspecialchars($row['Hyperlink']) . "</a><br>";
            echo "</p>";
        }
    } else {
        echo "غير موجود";
    }

    // Free result set
    mysqli_free_result($result);
}

// Close database connection
mysqli_close($conn);
?>

        </div>
        </div>
        </div>
        </div>
        <!-- Class End -->

        <?php include "footer.php";
?>
        

        <!-- back to top start -->
        <div class="progress-wrap progress-wrap-02">
            <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
                <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
            </svg>
        </div>
        <!-- back to top end -->

    </div>

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