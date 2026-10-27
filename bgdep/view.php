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
        <h1><a href="index.php" class="text-light text-decoration-none">فعاليات ونشاطات الأقسام </a></h1>
    </header>
    <div class="post-list mt-5">
        <div class="container">
            <?php
              $id="";
              if(isset($_GET['id'])){
                  $id = intval($_GET['id'] ?? 0);
                  
              }

                if ($id) {
                    include("connect.php");
                    $sqlSelect = "SELECT * FROM postevent WHERE id = $id";
                    $result = mysqli_query($conn,$sqlSelect);
                    while ($data = mysqli_fetch_array($result)) {
                    ?>
                       <div class="post bg-light p-4 mt-5">
                        <h1><?php echo $data['title']; ?></h1>
                        <p><?php echo $data['date']; ?> </p>
                        <p><?php echo $data['content']; ?> </p>
                        <p><?php echo $data['class']; ?> </p>
                        <p><?php echo $data['image']; ?> </p>
                       </div>
                    <?php
                    }
                }else{
                    echo "No post found";
                }
            ?>
         </div>
    </div>
    <div class="footer bg-dark p-3 mt-5">
        <a href="index.php"class="btn btn-primary"> عودة للصفحة السابقة</a>
    </div>
  
</body>
</html>