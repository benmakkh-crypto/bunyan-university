<?php 

if(isset($_POST['fname']) && 
   isset($_POST['uname']) && 
   isset($_POST['pass'])){

    include "../db_conn.php";

    $fname = $_POST['fname'];
    $uname = $_POST['uname'];
    $pass = $_POST['pass'];

    $data = "fname=".$fname."&uname=".$uname;
    
    if (empty($fname)) {
    	$em = "أدخل الاسم الكامل المطلوب";
    	header("Location: ../reg.php?error=$em&$data");
	    exit;
    }else if(empty($uname)){
    	$em = "  أدخل أسم المستخدم المطلوب ";
    	header("Location: ../reg.php?error=$em&$data");
	    exit;
    }else if(empty($pass)){
    	$em = " أدخل كلمة المرور المطلوبة ";
    	header("Location: ../reg.php?error=$em&$data");
	    exit;
    }else {

    	// hashing the password
    	$pass = password_hash($pass, PASSWORD_DEFAULT);

    	$sql = "INSERT INTO users(fname, username, password) 
    	        VALUES(?,?,?)";
    	$stmt = $conn->prepare($sql);
    	$stmt->execute([$fname, $uname, $pass]);

    	header("Location: ../reg.php?success=لقد تم إنشاء حسابك بنجاح");
	    exit;
    }


}else {
	header("Location: ../reg.php?error=error");
	exit;
}
