<?php require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php';
session_start();

try {
    // الاتصال بقاعدة البيانات
    $con = db_pdo('courses_std');
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// دالة لجلب البيانات
function getData($con, $sql) {
    $stmt = $con->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// استعلام البيانات
$sql = "SELECT * FROM courses";
$result = getData($con, $sql);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>صفحة الكورسات</title>
    <link rel="stylesheet" href="styles.css">
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
        .course {
    background-color: #fff;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 15px;
    margin: 15px 0;
    transition: box-shadow 0.3s;
}

.course:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

h2 {
    color: #333;
}

.price {
    font-weight: bold;
    color:rgb(3, 164, 95);
}

.Booklink {
            background-color: rgb(5, 96, 5);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            text-decoration: none;
        }
    .Booklink:hover {
            background-color: #4cae4c;
    
        }

    </style>
</head>
<body>
   
<div class="header">
    <h1>  الدورات المتاحة</h1>
</div>

<div class="navbar">
    <a href="../index.php">الرئيسية</a>
    <a href="register.php">تسجيل</a>
    <a href="login.php">تسجيل الدخول</a>
    <a href="courses.php">الدورات التدريبية</a>
    <a href="booking.php">رابط الدورة </a>
    <a href="../de/des.php">الشهادة الالكترونية </a>
</div>

    <main>
        <?php foreach ($result as $row): ?>
            <section class="course" id="course-<?php echo htmlspecialchars($row['id']); ?>">
                <h2><?php echo htmlspecialchars($row['title']); ?></h2>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <a href="booking.php"class="Booklink">رابط الدورة </a>
            </section>
        <?php endforeach; ?>
    </main>
    <script src="script.js"></script>
</body>
</html>