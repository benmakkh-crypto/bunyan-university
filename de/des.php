<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php'; list($servername, $username, $password, $dbname) = db_cfg('logweb');

// إنشاء الاتصال
$conn = db_mysqli_raw($servername, $username, $password, $dbname);

// التحقق من الاتصال
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// الحصول على البيانات من قاعدة البيانات
$sql = "SELECT * FROM certificates WHERE id = 1"; // استبدل بـ id الطالب المطلوب
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // استخراج البيانات
    $row = $result->fetch_assoc();
    $student_name = $row['student_name'];
    $instructor_name = $row['instructor_name'];
    $course_name = $row['course_name'];
    $hours = $row['hours'];
    $grade = $row['grade'];
    $issue_date = $row['issue_date'];
} else {
    echo "لم يتم العثور على الشهادة.";
}

$conn->close();
?>




<?php
// هنا يمكن إضافة اتصال لقاعدة البيانات إذا كان لديك
// على سبيل المثال، يمكنك استخدام MySQL لاسترجاع البيانات
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شهادة حضور الدورة التدريبية</title>
    <link rel="stylesheet" href="des.css">
</head>
<body>

<div class="certificate-container">
        <header class="header">
            <div class="logo">
                <img src="img/logo2.png" alt="شعار الجامعة" class="logo-img">
            </div>
            <div class="university-info">
                <h1> جامعة بنيان العالمية</h1>
                <h3> كلية التقنية والبرمجة</h3>
            </div>
        </header>
    <div class="certificate">
        <div class="header1">
            <h1 class="tit">شهادة حضور</h1>
        </div>
        <div class="content">
            <p><strong>اسم الطالب/أسم الطالبة:</strong> <?php echo $student_name; ?></p>
            <p><strong>اسم المدرب/أسم المدربة:</strong> <?php echo $instructor_name; ?></p>
            <p><strong>اسم المادة:</strong> <?php echo $course_name; ?></p>
            <p><strong>عدد الساعات:</strong> <?php echo $hours; ?> ساعة</p>
            <p><strong>الدرجة:</strong> <?php echo $grade; ?></p>
            <p><strong>تاريخ الإصدار:</strong> <?php echo $issue_date; ?></p>
        </div>
        <div class="footer">
            <p>تم إصدار هذه الشهادة بواسطة النظام في تاريخ: <?php echo date('Y-m-d'); ?></p>
        </div>
    </div>
    <div class="imag">
                <img src="img/st.jpeg"class="pic-img">
            </div>

            <div class="link">
                <a href="../index.php"> الصفحة الرئيسية</a>
            </div>


</body>
</html>
