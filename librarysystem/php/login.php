<?php 
session_start();

if(isset($_POST['uname']) && 
   isset($_POST['pass'])){

    include "../db_conn.php";

    $uname = $_POST['uname'];
    $pass = $_POST['pass'];

    $data = "uname=".$uname;
    
    if(empty($uname)){
    	$em = "ادخل اسم المستخدم المطلوب";
    	header("Location: ../login.php?error=$em&$data");
	    exit;
    }else if(empty($pass)){
    	$em = " أدخل كلمة المرور المطلوبة";
    	header("Location: ../login.php?error=$em&$data");
	    exit;
    }else {

    	$sql = "SELECT * FROM users WHERE username = ?";
    	$stmt = $conn->prepare($sql);
    	$stmt->execute([$uname]);

      if($stmt->rowCount() == 1){
          $user = $stmt->fetch();

          $username =  $user['username'];
          $password =  $user['password'];
          $fname =  $user['fname'];
          $id =  $user['id'];
          if($username === $uname){
             if(password_verify($pass, $password)){
                 $_SESSION['id'] = $id;
                 $_SESSION['fname'] = $fname;

                 header("Location:../index1.php");
                 exit; 
             }else {
               $em = "قد يكون أسم المستخدم أو كلمة المرور خطأ";
               header("Location: ../login.php?error=$em&$data");
               exit;
            }

          }else {
            $em = "قد يكون أسم المستخدم أو كلمة المرور خطأ";

            header("Location: ../login.php?error=$em&$data");
            exit;
         }

      }else {
                      $em = "قد يكون أسم المستخدم أو كلمة المرور خطأ";

         header("Location: ../login.php?error=$em&$data");
         exit;
      }
    }


}else {
	header("Location: ../login.php?error=error");
	exit;
}
