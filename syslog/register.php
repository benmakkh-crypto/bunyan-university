<!-- Code by Brave Coder - https://youtube.com/BraveCoder -->

<?php require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php';
    //Import PHPMailer classes into the global namespace
    //These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    session_start();
    if (isset($_SESSION['SESSION_EMAIL'])) {
        header("Location: welcome.php");
        die();
    }

    //Load Composer's autoloader
    require 'vendor/autoload.php';

    include 'config.php';
    $msg = "";

    if (isset($_POST['submit'])) {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass = mysqli_real_escape_string($conn, md5($_POST['pass']));
        $confirm_pass = mysqli_real_escape_string($conn, md5($_POST['confirm-password']));
        // $pass = mysqli_real_escape_string($conn,$_POST['pass']);
        // $confirm_pass = mysqli_real_escape_string($conn, $_POST['confirm-password']);
        $code = mysqli_real_escape_string($conn, md5(rand()));
        header("Location: index.php");

        if (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM user WHERE email='{$email}'")) > 0) {
            $msg = "<div class='alert alert-danger'>{$email} - عنوان البريد الإلكتروني هذا موجود بالفعل.</div>";
        } else {
            if ($password === $confirm_password) {
                $sql = "INSERT INTO user (name, email, pass, code) VALUES ('{$name}', '{$email}', '{$pass}', '{$code}')";
                $result = mysqli_query($conn, $sql);

                if ($result) {
                    echo "<div style='display: none;'>";
                    //Create an instance; passing `true` enables exceptions
                    $mail = new PHPMailer(true);

                    try {
                        //Server settings
                        $mail->SMTPDebug = 0;                      //Enable verbose debug output
                        $mail->isSMTP();                                            //Send using SMTP
                        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
                        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                        $mail->Username   = UWCIV_SMTP_USER;                     //SMTP username
                        $mail->Password   = UWCIV_SMTP_PASS;                               //SMTP password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                        //Recipients
                        $mail->setFrom(UWCIV_SMTP_USER, 'جامعة بنيان العالمية');
                        $mail->CharSet = 'UTF-8';
                        // بدون كلمة مرور تطبيق: الإرسال عبر بريد الاستضافة نفسها
                        if (UWCIV_SMTP_PASS === '') { $mail->isMail(); $mail->setFrom('noreply@your-domain.com', 'جامعة بنيان العالمية'); $mail->addReplyTo(UWCIV_MAIL_TO); }
                        $mail->addAddress($email);

                        //Content
                        $mail->isHTML(true);                                  //Set email format to HTML
                        $mail->Subject = 'no reply';
                        $mail->Body    = 'Here is the verification link <b><a href="https://your-domain.com/syslog/?verification='.$code.'">https://your-domain.com/syslog/?verification='.$code.'</a></b>';

                        $mail->send();
                        echo 'Message has been sent';
                    } catch (Exception $e) {
                        echo "تعذر إرسال الرسالة. Mailer Error: {$mail->ErrorInfo}";
                    }
                    echo "</div>";
                    $msg = "<div class='alert alert-info'لقد أرسلنا رابط التحقق على بريدك الإلكتروني.</div>";
                } else {
                    $msg = "<div class='alert alert-danger'>حدث خطأ ما.</div>";
                }
            } else {
                $msg = "<div class='alert alert-danger'>كلمة المرور وتأكيد كلمة المرور غير متطابقين</div>";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="ar"dir="rtl">

<head>
    <title>تسجيل</title>
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
    <section  style="background-color:#ccd7b9;" class="w3l-mockup-form">
        <div class="container">
            <!-- /form -->
            <div class="workinghny-form-grid">
                <div class="main-mockup">
                    <div style="background-color:#568203;" class="w3l_form align-self">
                        <div class="left_grid_info">
                            <img src="images/im.png" alt="">
                        </div>
                    </div>
                    <div class="content-wthree">
                        <h2 style="color:#568203;">تسجيل الأن</h2>
                        <p>أذا لم يكن لديك حساب . </p>
                        <?php echo $msg; ?>
                        <form action="" method="post">
                            <input style="background-color:#ccd7b9;" type="text" class="name" name="name" placeholder=" أدخل أسمك:" value="<?php if (isset($_POST['submit'])) { echo $name; } ?>" required>
                            <input style="background-color:#ccd7b9;" type="email" class="email" name="email" placeholder="أدخل بريدك الالكتروني:" value="<?php if (isset($_POST['submit'])) { echo $email; } ?>" required>
                            <input style="background-color:#ccd7b9;" type="password" class="password" name="pass" placeholder="أدخل كلمة السر:" required>
                            <input style="background-color:#ccd7b9;" type="password" class="confirm-password" name="confirm-password" placeholder="تأكيد كلمة السر:" required>
                            <button style="background-color:#568203;" name="submit" class="btn" type="submit">تسجيل</button>
                        </form>
                        <div class="social-icons">
                            <p style="color:#000;">تمتلك حساب؟ <a style="color:#568203;" href="index.php">تسجيل دخول</a>.</p>
                        </div>
                    
                    </div>
                 
                </div>
              
            </div>
         
            <!-- //form -->
            <div class="social-icons">
                            <p style="color:#000;">عودة لصفحة الرئيسية  <a style="color:#568203;" href="../index.php">أضغط هنا</a>.</p>
                        </div>
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