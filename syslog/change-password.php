<?php

$msg = "";

include 'config.php';

if (isset($_GET['reset'])) {
    $resetCode = mysqli_real_escape_string($conn, $_GET['reset']);
    if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM user WHERE code='{$resetCode}'")) > 0) {
        if (isset($_POST['submit'])) {
            $password = mysqli_real_escape_string($conn, md5($_POST['password']));
            $confirm_password = mysqli_real_escape_string($conn, md5($_POST['confirm-password']));

            if ($password === $confirm_password) {
                $query = mysqli_query($conn, "UPDATE user SET password='{$password}', code='' WHERE code='{$resetCode}'");

                if ($query) {
                    header("Location: index.php");
                }
            } else {
                $msg = "<div class='alert alert-danger'>Password and Confirm Password do not match.</div>";
            }
        }
    } else {
        $msg = "<div class='alert alert-danger'>Reset Link do not match.</div>";
    }
} else {
    header("Location: forgot-password.php");
}

?>

<!DOCTYPE html>
<html lang="zxx">

<head>
    <title>تغير الباسورد</title>
    <!-- Meta tag Keywords -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8" />
    <meta name="keywords"
        content="Login Form" />
    <!-- //Meta tag Keywords -->

    <link href="//fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!--/Style-CSS -->
    <link rel="stylesheet" href="css/style.css" type="text/css" media="all" />
    <!--//Style-CSS -->

    <script src="https://kit.fontawesome.com/af562a2a63.js" crossorigin="anonymous"></script>

</head>

<body>

    <!-- form section start -->
    <section style="background-color:#ccd7b9;" class="w3l-mockup-form">
        <div class="container">
            <!-- /form -->
            <div class="workinghny-form-grid">
                <div class="main-mockup">
                    <div class="alert-close">
                        <span class="fa fa-close"></span>
                    </div>
                    <div class="w3l_form align-self">
                        <div class="left_grid_info">
                            <img src="images/im.png" alt="">
                        </div>
                    </div>
                    <div class="content-wthree">
                        <h2 style="color:#568203;">تغير كلمة المرور</h2>
                        
                        <?php echo $msg; ?>
                        <form action="" method="post">
                            <input style="background-color:#ccd7b9;" type="password" class="password" name="password" placeholder="أدخل كلمة المرور" required>
                            <input style="background-color:#ccd7b9;" type="password" class="confirm-password" name="confirm-password" placeholder="أدخل تأكيد كلمة المرور" required>
                            <button style="background-color:#568203;" name="submit" class="btn" type="submit"> تغير كلمة المرور</button>
                        </form>
                        <div class="social-icons">
                            <p style="color:#000;">العودة الى <a style="color:#568203;"href="index.php">تسجيل الدخول</a>.</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- //form -->
        </div>
    </section>
    <!-- //form section start -->

    <script src="js/jquery.min.js"></script>
    <script>
        $(document).ready(function (c) {
            $('.alert-close').on('click', function (c) {
                $('.main-mockup').fadeOut('slow', function (c) {
                    $('.main-mockup').remove();
                });
            });
        });
    </script>

</body>

</html>