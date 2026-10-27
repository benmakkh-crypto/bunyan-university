<?php require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php';
session_start();

try {
    // الاتصال بقاعدة البيانات
    $con = db_pdo('courses_std');
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // إدخال البيانات في قاعدة البيانات باستخدام جملة محضرة
    $sql = "INSERT INTO students (name, email, password) VALUES (:name, :email, :password)";
    
    try {
        $stmt = $con->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        
        echo "<div class='success'>تم التسجيل بنجاح!</div>";
    } catch (PDOException $e) {
        echo "<div class='error'>خطأ في تسجيل البيانات: " . $e->getMessage() . "</div>";
    }
}
?>



<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الطلبة</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            text-align: center;
            padding: 50px;
            margin: 0;
        }
        .header {
            background-color: rgb(5, 96, 5);
            color: white;
            padding: 20px 0;
            position: relative;
        }
        .header h1 {
            margin: 0;
        }
        .navbar {
            background-color: #333;
            overflow: hidden;
        }
        .navbar a {
            float: right;
            display: block;
            color: white;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
        .form-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.22);
            padding: 20px;
            max-width: 400px;
            margin: auto;
            margin-top: 20px;
            position: relative;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: rgb(5, 96, 5);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #4cae4c;
        }
        .success {
            color: green;
            margin-top: 20px;
        }
        .error {
            color: red;
            margin-top: 20px;
        }
        .header-image {
            width: 200px;
            height: auto;
            border-radius: 5px;
            transition: transform 0.3s;
        }
        .header-image:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>

<div class="header">
    <h1>تسجيل الطلبة</h1>
</div>

<div class="navbar">
    <a href="../index.php">الرئيسية</a>
    <a href="register.php">تسجيل</a>
    <a href="login.php">تسجيل الدخول</a>
    <a href="courses.php">الدورات التدريبية</a>
    <a href="booking.php">رابط الدورة </a>
    <a href="../de/des.php">الشهادة الالكترونية </a>
</div>

<div class="form-container">
    <img src="img/reg.png" alt="صورة توضيحية" class="header-image">
    <form method="POST" action="">
        <input type="text" name="name" placeholder="الاسم" required>
        <input type="email" name="email" placeholder="البريد الإلكتروني" required>
        <input type="password" name="password" placeholder="كلمة المرور" required>
        <button type="submit">تسجيل</button>
    </form>
</div>

</body>
</html>