<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php'; list($dbHost, $dbUser, $dbPass, $dbName) = db_cfg('courses_std');

$conn = db_mysqli_raw($dbHost, $dbUser, $dbPass, $dbName);
if (!$conn) {
    die("Something went wrong. Database is not connected;");
}



// استعلام البيانات

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
    <h1> دورة بايثون  </h1د>
</div>

<div class="navbar">
   <a href="#home">الرئيسية</a>
    <a href="register.php">تسجيل</a>
    <a href="login.php">تسجيل الدخول</a>
    <a href="courses.php">الكورسات</a>
    <a href="book.php">حجز </a>
    <a href="#contact">الشهادة الالكترونية </a>
</div>

    <main style="margin-top:3rem;">
    <?php
           
           $sql = "SELECT * FROM  python" ;
           $result = mysqli_query($conn, $sql);
           if (mysqli_num_rows($result) > 0) {
               // output data of each row
               while($row = mysqli_fetch_assoc($result)) {
                 echo '
      
                            <div class="single-testimonial-02">
   
                                   <div class="testimonial-thumb">
                                       <img width="170px" height="160px" src="adnpython/img/'.$row['image'].'" alt="">
                                   </div>
                                   <div class="testimonial-content">
                               <p style="font-size:20px;color:#568203;">'.$row["title"].'</P>
                                         <p style="font-size:20px;color:#568203;">'.$row["teacher"].'</P>
                                      <p style="font-size:16px;color:#568203"></P>';
                                 echo" <h5 style=font-size:16px;color:#568203;><a style=font-size:16px;color:#568203 href='" . htmlspecialchars($row["link"]) . "' target='_blank'>" . htmlspecialchars($row["title"]) . "</a></h5><br>
   
                                   </div>
                               </div>
                               <!--  Single Testimonial End  -->
                           </div>
   
                           
        
        ";
        
               }
             }
           ?>
        
    </main>
   
    <script src="script.js"></script>
</body>
</html>