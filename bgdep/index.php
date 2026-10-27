<!DOCTYPE html>
<html lang="ar"dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> فعاليات ونشاطات الأقسام</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
</head>
<body>
    <header class="p-4 bg-dark text-center">
    <h1><a href="index.php" class="text-light text-decoration-none">فعاليات ونشاطات الأقسام</a></h1>
      
    
    </header>
    <div class="post-list mt-5">
        <div class="container">
            <?php
                include("connect.php");
                $sqlSelect = "SELECT * FROM postevent";
                $result = mysqli_query($conn,$sqlSelect);
                while ($data = mysqli_fetch_array($result)) {
                ?>
                 <table class="table table-striped">
        <thead>
            <tr>
                <th style="width:15%;">تاريخ النشر:</th>
                <th style="width:15%;">العنوان:</th>
                <th style="width:15%;">الملخص:</th>
                <th style="width:45%;">المقال:</th>
                <th style="width:15%;">التصنيف:</th>
                <th style="width:30%;">الصورة:</th>
              
            

            </tr>
        </thead>
        <tbody>
            
            <tr>
            <td><?php echo $data["date"]?></td>
            <td><?php echo $data["title"]?></td>
            <td><?php echo $data["summary"]?></td>
            <td><?php echo $data["content"]?></td>
            <td><?php echo $data["class"]?></td>
            <td><?php echo $data["image"]?></td>

            <td><a href="view.php?id=<?php echo $data['id']; ?>" class="btn btn-primary">المزيد</a></td>
           
            </tr>
            <?php
            }
            ?>
            
        </tbody>
    </table>
 
</div>
                <?php
                
            ?>


            
                       
         </div>

        
    </div>
    <div class="footer bg-dark p-3 mt-5">
        <a href="../events.php"class="btn btn-primary"> عودة لصفحة الفعاليات</a>
    </div>
  
</body>
</html>