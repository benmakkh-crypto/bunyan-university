<?php
session_start();
if (!isset($_SESSION["user"]) && empty($_SESSION["uwciv_super"])) {
    header("Location:login.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="ar"dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
</head>
<body>
    <div class="dashboard d-flex justify-content-between">
        <div class="sidebar bg-dark vh-100">
            <h4 class="bg-secondary p-4"><a href="./index.php" class="text-light text-decoration-none">  محاضرات كلية<br>  القران الكريم الدكتوراه</a></h4>
            <div class="menues p-4 mt-5">
                <div class="menu">
                    <a href="create.php"class="btn btn-secondary">إضافة جديدة</a>
                </div>
                <div class="menu mt-5">
                    <a href="./index.php" class="btn btn-secondary">العرض</a>
                </div>
                <div class="menu mt-5">
                    <a href="logout.php" class="btn btn-secondary"> تسجيل خروج</a>
                </div>
                
            </div>
        </div>