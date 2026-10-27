<?php if (session_status() !== PHP_SESSION_ACTIVE) @session_start(); if (empty($_SESSION['user']) && empty($_SESSION['uwciv_super'])) { header('Location: login.php'); exit; } ?><?php
if (isset($_POST["create"])) {
    include("../connect.php");
    

     $title = $_POST['title'];
     $teacher = $_POST['teacher'];
     $date = $_POST['date'];
     $link = $_POST['link'];
     $image = uploads('../img','image');
     
     $sql = "INSERT INTO rsch (`date`,title,teacher,link,image)

     VALUES ('$date','$title','$teacher','$link','$image')";
    
     if ($conn->query($sql) === TRUE) {
      echo "تمت الاضافة بنجاح";
    } else {
      echo "لم تتم الاضافة " . $sql . "<br>" . $conn->error;
    }
    
    $conn->close();
  }

  function uploads($nameFolder,$namePostFile,$nameFile=""){

    if(isset($_FILES[$namePostFile])){
      $temp = explode(".", $_FILES[$namePostFile]["name"]);
      $newfilename = round(microtime(true)) . '.' . end($temp);
      $target_dir = $nameFolder."/";
      $target_file = $target_dir . basename($newfilename );
        if (move_uploaded_file($_FILES[$namePostFile]["tmp_name"], $target_file)) {
          return $newfilename;
        } else {
          return $nameFile;
        }
    }else{
      return $nameFile;
    }
     
  }

?>



   
<?php

if (isset($_POST["update"])) {
    include("../connect.php");
    $date = $_POST['date'];
    $title = $_POST['title'];
     $teacher = $_POST['teacher'];
     $link = $_POST['link'];


     $image=uploads('../img','image1',$_POST['image2']);
    
 
    $id = mysqli_real_escape_string($conn, $_POST["id"]);
    $sqlUpdate = "UPDATE quran SET  title = '$title', teacher = '$teacher',
 date = '$date',link='$link',image='$image' WHERE id = $id";
        
    if(mysqli_query($conn, $sqlUpdate)){
        session_start();
        
        $_SESSION["update"] = "تم التعديل بنجاح";
        header("Location:index.php");
    }else{
        die("البيانات لم تعدل!"); 
    }

    function uploads($nameFolder,$namePostFile,$nameFile=""){

      if(isset($_FILES[$namePostFile])){
        $temp = explode(".", $_FILES[$namePostFile]["name"]);
        $newfilename = round(microtime(true)) . '.' . end($temp);
        $target_dir = $nameFolder."/";
        $target_file = $target_dir . basename($newfilename );
          if (move_uploaded_file($_FILES[$namePostFile]["tmp_name"], $target_file)) {
            return $newfilename;
          } else {
            return $nameFile;
          }
      }else{
        return $nameFile;
      }
       
    }
}
?>