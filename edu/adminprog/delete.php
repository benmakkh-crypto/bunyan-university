<?php if (session_status() !== PHP_SESSION_ACTIVE) @session_start(); if (empty($_SESSION['user']) && empty($_SESSION['uwciv_super'])) { header('Location: login.php'); exit; } ?><?php
$id = intval($_GET['id'] ?? 0);
if($id){
include("../connect.php");
$sqlDelete = "DELETE FROM prog WHERE id = $id";
if(mysqli_query($conn, $sqlDelete)){
    session_start();
    $_SESSION["delete"] = "تم الحذف بنجاح";
    header("Location:index.php");
}else{
    die("لم يتم الحذف");
}
}else{
    echo " لا توجد محاضرة ";
}
?>